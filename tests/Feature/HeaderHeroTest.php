<?php

function lotThreeConfiguration(): array
{
    $configuration = config('portfolio');
    $configuration['components']['services']['active'] = false;
    $configuration['components']['projects']['active'] = false;

    return $configuration;
}

function lotThreeValidContactForm(): array
{
    return [
        'active' => true,
        'order' => 80,
        'title' => 'Example contact form',
        'fields' => [
            [
                'key' => 'email',
                'type' => 'email',
                'label' => 'Example email',
                'required' => true,
            ],
            [
                'key' => 'message',
                'type' => 'textarea',
                'label' => 'Example message',
                'required' => true,
            ],
        ],
        'submit_label' => 'Send example',
        'success_message' => 'Example sent.',
        'consent_required' => true,
    ];
}

test('it resolves the validated Header and Hero content without inactive links or empty media', function () {
    $configuration = lotThreeConfiguration();
    $sourceHeroCta = $configuration['components']['hero']['primary_cta'];
    config()->set('portfolio', $configuration);

    $portfolio = $this->get('/')->assertOk()->inertiaProps('portfolio');
    $components = collect($portfolio['components'])->keyBy('type');

    expect($components->keys()->all())->toBe(['header', 'hero'])
        ->and($components['header']['brand_label'])->toBe('Mohamed M’Kirchel')
        ->and($components['header']['show_logo'])->toBeFalse()
        ->and($components['header'])->not->toHaveKeys(['navigation', 'primary_cta'])
        ->and($components['hero']['eyebrow'])
        ->toBe('PHP/Laravel/Vue.js — SQL Server, API, automatisation')
        ->and($components['hero']['headline'])
        ->toBe('Transformer l’expertise et le réseau d’un professionnel en opportunités commerciales qualifiées.')
        ->and($components['hero'])->not->toHaveKeys([
            'description',
            'primary_cta',
            'secondary_cta',
            'photo_override_url',
            'photo_alt',
        ])
        ->and($configuration['components']['hero']['primary_cta'])->toBe($sourceHeroCta);
});

test('it keeps only Header links and optional Hero actions targeting active valid sections', function () {
    $configuration = lotThreeConfiguration();
    $configuration['components']['projects'] = [
        'active' => true,
        'order' => 40,
        'title' => 'Example projects',
        'items' => [[
            'title' => 'Example project',
            'summary' => 'Example project summary.',
        ]],
    ];
    $configuration['components']['about'] = [
        'active' => true,
        'order' => 50,
        'title' => 'Example about title',
        'summary' => 'Example about summary.',
    ];
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components['header']['navigation'])->toBe([
        ['label' => 'Projets', 'target' => '#projects'],
        ['label' => 'À propos', 'target' => '#about'],
    ])->and($components['header'])->not->toHaveKey('primary_cta')
        ->and($components['hero'])->not->toHaveKey('primary_cta')
        ->and($components['hero']['secondary_cta'])->toBe([
            'label' => 'Voir mes projets',
            'target' => '#projects',
        ]);
});

test('it keeps the Hero and removes its valid Contact Form CTA during the transitional state', function () {
    $configuration = lotThreeConfiguration();
    $configuration['components']['contact_form']['active'] = false;
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components->has('hero'))->toBeTrue()
        ->and($components['hero'])->not->toHaveKey('primary_cta')
        ->and($configuration['components']['hero']['primary_cta'])->toBe([
            'label' => 'Me contacter',
            'target' => '#contact-form',
        ]);
});

test('it keeps the transitional Hero stable when another component is removed', function () {
    $configuration = lotThreeConfiguration();
    $configuration['components']['footer'] = [
        'active' => true,
        'order' => 90,
        'show_identity' => false,
        'show_navigation' => false,
        'show_socials' => false,
        'show_contact' => false,
        'show_legal_links' => false,
    ];
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components->keys()->all())->toBe(['header', 'hero'])
        ->and($components['hero'])->not->toHaveKey('primary_cta');
});

test('it keeps the Hero CTA when the Contact Form source is active and valid', function () {
    $configuration = lotThreeConfiguration();
    $configuration['global']['legal']['privacy_policy_url'] = 'https://example.test/privacy';
    $configuration['global']['legal']['form_consent_text'] = 'Example consent text.';
    $configuration['components']['contact_form'] = lotThreeValidContactForm();
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components->has('hero'))->toBeTrue()
        ->and($components->has('contact_form'))->toBeTrue()
        ->and($components['hero']['primary_cta'])->toBe([
            'label' => 'Me contacter',
            'target' => '#contact-form',
        ]);
});

test('it removes the Hero when the active Contact Form source is invalid', function () {
    $configuration = lotThreeConfiguration();
    $configuration['components']['contact_form']['active'] = true;
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components->has('hero'))->toBeFalse()
        ->and($components->has('contact_form'))->toBeFalse();
});

test('it removes the Hero when its required CTA is absent incomplete or invalid', function (mixed $cta) {
    $configuration = lotThreeConfiguration();
    $configuration['components']['hero']['primary_cta'] = $cta;
    config()->set('portfolio', $configuration);

    $components = collect(
        $this->get('/')->assertOk()->inertiaProps('portfolio.components'),
    )->keyBy('type');

    expect($components->has('hero'))->toBeFalse();
})->with([
    'absent' => [null],
    'incomplete' => [['label' => 'Me contacter']],
    'invalid' => [[
        'label' => 'Me contacter',
        'target' => 'javascript:alert(1)',
    ]],
]);

test('it does not expose private values in the Lot 3 browser response', function () {
    $privateRecipient = 'private-recipient@example.test';
    $privateSecret = 'private-lot-three-secret';

    config()->set('portfolio', lotThreeConfiguration());
    config()->set('portfolio-private.contact.recipient_email', $privateRecipient);
    config()->set('portfolio-private.operations.secret', $privateSecret);
    config()->set('mail.mailers.smtp.password', $privateSecret);

    $browserPayload = $this->get('/')->assertOk()->getContent();

    expect($browserPayload)
        ->not->toContain('recipient_email')
        ->not->toContain($privateRecipient)
        ->not->toContain($privateSecret)
        ->not->toContain('smtp.password');
});
