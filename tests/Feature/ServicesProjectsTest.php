<?php

use App\Portfolio\PortfolioComposer;

function lotFourConfiguration(): array
{
    return config('portfolio');
}

test('it exposes the exact validated Services and Projects payload in configured order', function () {
    $configuration = lotFourConfiguration();
    config()->set('portfolio', $configuration);

    $portfolio = $this->get('/')->assertOk()->inertiaProps('portfolio');
    $components = collect($portfolio['components'])->keyBy('type');

    expect(array_column($portfolio['components'], 'type'))->toBe([
        'header',
        'hero',
        'services',
        'projects',
    ])->and(array_column($portfolio['components'], 'order'))->toBe([10, 20, 30, 40])
        ->and($components['services'])->toBe([
            'type' => 'services',
            'active' => true,
            'order' => 30,
            'title' => 'Services',
            'items' => [[
                'title' => 'Site de crédibilité B2B',
                'description' => 'Création d’un site professionnel d’une page, adapté aux mobiles, présentant clairement votre promesse, vos services, vos preuves, votre présentation et un moyen de contact.',
                'benefit' => 'Aider vos prospects LinkedIn et vos recommandations à comprendre rapidement votre expertise et à passer plus facilement à la prise de contact.',
            ]],
        ])->and($components['projects'])->toBe([
            'type' => 'projects',
            'active' => true,
            'order' => 40,
            'title' => 'Projets',
            'items' => [[
                'title' => 'Portfolio OS',
                'summary' => 'Projet de moteur configurable et réutilisable pour créer des portfolios professionnels d’une page, dont le portfolio de Mohamed constitue la première instance.',
                'tags' => ['Laravel', 'Vue.js', 'Inertia', 'Tailwind CSS'],
            ]],
        ])->and($components['header']['navigation'])->toBe([
            ['label' => 'Services', 'target' => '#services'],
            ['label' => 'Projets', 'target' => '#projects'],
        ])->and($components['hero']['secondary_cta'])->toBe([
            'label' => 'Voir mes projets',
            'target' => '#projects',
        ]);
});

test('it excludes Services or Projects when their mandatory content is invalid', function () {
    $validConfiguration = lotFourConfiguration();
    $invalidComponents = [];

    $servicesWithoutTitle = $validConfiguration['components']['services'];
    unset($servicesWithoutTitle['title']);
    $invalidComponents['Services without title'] = ['services', $servicesWithoutTitle];

    $servicesWithoutBenefit = $validConfiguration['components']['services'];
    unset($servicesWithoutBenefit['items'][0]['benefit']);
    $invalidComponents['Services without a valid item'] = ['services', $servicesWithoutBenefit];

    $projectsWithoutTitle = $validConfiguration['components']['projects'];
    unset($projectsWithoutTitle['title']);
    $invalidComponents['Projects without title'] = ['projects', $projectsWithoutTitle];

    $projectsWithoutSummary = $validConfiguration['components']['projects'];
    unset($projectsWithoutSummary['items'][0]['summary']);
    $invalidComponents['Projects without a valid item'] = ['projects', $projectsWithoutSummary];

    foreach ($invalidComponents as $case => [$type, $component]) {
        $configuration = $validConfiguration;
        $configuration['components'][$type] = $component;
        $types = array_column(
            (new PortfolioComposer)->compose($configuration)['components'],
            'type',
        );

        expect($types)->not->toContain($type, $case);
    }
});

test('it does not expose absent optional fields or private values in the Lot 4 response', function () {
    $privateRecipient = 'private-lot-four-recipient@example.test';
    $privateSecret = 'private-lot-four-secret';
    $configuration = lotFourConfiguration();
    $configuration['global']['private_token'] = $privateSecret;
    $configuration['private'] = ['secret' => $privateSecret];

    config()->set('portfolio', $configuration);
    config()->set('portfolio-private.contact.recipient_email', $privateRecipient);
    config()->set('portfolio-private.operations.secret', $privateSecret);
    config()->set('mail.mailers.smtp.password', $privateSecret);

    $response = $this->get('/')->assertOk();
    $portfolio = $response->inertiaProps('portfolio');
    $components = collect($portfolio['components'])->keyBy('type');
    $browserPayload = $response->getContent();

    expect($components['services'])->not->toHaveKey('intro')
        ->and($components['services']['items'][0])->not->toHaveKey('cta')
        ->and($components['projects'])->not->toHaveKey('intro')
        ->and($components['projects']['items'][0])->not->toHaveKeys([
            'challenge',
            'solution',
            'result',
            'client_name',
            'client_logo_url',
            'image_url',
            'image_alt',
            'date',
            'project_url',
        ])->and($browserPayload)
        ->not->toContain('recipient_email')
        ->not->toContain($privateRecipient)
        ->not->toContain('private_token')
        ->not->toContain($privateSecret)
        ->not->toContain('smtp.password');
});
