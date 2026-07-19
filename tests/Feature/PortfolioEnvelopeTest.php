<?php

use Inertia\Testing\AssertableInertia as Assert;

function lotTwoConfiguration(array $components = []): array
{
    return [
        'schema_version' => '1.0',
        'global' => [
            'identity' => [
                'display_name' => 'Example Profile',
                'professional_title' => 'Example Professional Title',
                'tagline' => 'Example tagline used only by tests.',
                'logo_url' => 'https://example.test/logo.svg',
                'profile_photo_url' => 'https://example.test/profile.webp',
                'default_language' => 'en',
            ],
            'appearance' => [
                'primary_color' => '#112233',
                'accent_color' => null,
                'background_color' => '#FFFFFF',
                'text_color' => '#111827',
                'font_family' => null,
                'color_mode' => 'light',
            ],
            'seo' => [
                'site_url' => 'https://example.test',
                'meta_title' => 'Example Portfolio Title',
                'meta_description' => 'A neutral description used exclusively by the automated tests for Portfolio OS.',
                'canonical_url' => 'https://example.test/profile',
                'social_image_url' => 'https://example.test/social.png',
                'indexing_enabled' => true,
            ],
            'socials' => [
                [
                    'platform' => 'github',
                    'url' => 'https://example.test/source',
                ],
                [
                    'platform' => 'other',
                    'url' => 'https://example.test/network',
                    'label' => 'Example network',
                ],
            ],
            'contact' => [
                'email' => 'public@example.test',
                'phone' => null,
                'whatsapp_url' => null,
                'booking_url' => null,
                'location_text' => 'Example location',
            ],
            'legal' => [
                'publisher_name' => 'Example Publisher',
                'registration_number' => 'EXAMPLE-123',
                'legal_notice_url' => 'https://example.test/legal',
                'privacy_policy_url' => 'https://example.test/privacy',
                'form_consent_text' => 'Example consent text used only by tests.',
                'copyright_year' => 2026,
                'copyright_owner' => 'Example Owner',
            ],
        ],
        'components' => $components,
    ];
}

test('it exposes the configured public identity SEO socials and legal information', function () {
    config()->set('portfolio', lotTwoConfiguration());

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Portfolio')
            ->where('portfolio.global.identity.display_name', 'Example Profile')
            ->where('portfolio.global.identity.professional_title', 'Example Professional Title')
            ->where('portfolio.global.identity.tagline', 'Example tagline used only by tests.')
            ->where('portfolio.global.identity.logo_url', 'https://example.test/logo.svg')
            ->where('portfolio.global.seo.meta_title', 'Example Portfolio Title')
            ->where(
                'portfolio.global.seo.meta_description',
                'A neutral description used exclusively by the automated tests for Portfolio OS.',
            )
            ->where('portfolio.global.seo.canonical_url', 'https://example.test/profile')
            ->where('portfolio.global.seo.social_image_url', 'https://example.test/social.png')
            ->where('portfolio.global.socials.0.platform', 'github')
            ->where('portfolio.global.socials.1.label', 'Example network')
            ->where('portfolio.global.legal.publisher_name', 'Example Publisher')
            ->where('portfolio.global.legal.registration_number', 'EXAMPLE-123')
            ->where('portfolio.global.legal.legal_notice_url', 'https://example.test/legal')
            ->where('portfolio.global.legal.privacy_policy_url', 'https://example.test/privacy'));
});

test('it applies the configured language to the initial document', function () {
    config()->set('portfolio', lotTwoConfiguration());

    $response = $this->get('/')->assertOk();

    $response->assertSee('<html lang="en">', false)
        ->assertInertia(fn (Assert $page) => $page
            ->where('portfolio.global.identity.default_language', 'en'));
});

test('it permits local and preproduction URLs only with indexing disabled', function (string $siteUrl) {
    $configuration = lotTwoConfiguration();
    $configuration['global']['seo']['site_url'] = $siteUrl;
    $configuration['global']['seo']['canonical_url'] = null;
    $configuration['global']['seo']['indexing_enabled'] = false;
    config()->set('portfolio', $configuration);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('portfolio.global.seo.site_url', $siteUrl)
            ->where('portfolio.global.seo.indexing_enabled', false));

    $configuration['global']['seo']['indexing_enabled'] = true;
    config()->set('portfolio', $configuration);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('portfolio.global.seo'));
})->with([
    'local URL' => 'http://localhost:8000',
    'preproduction URL' => 'http://preview.example.test:8080',
]);

test('it requires a public HTTPS URL when indexing is enabled', function () {
    $configuration = lotTwoConfiguration();
    config()->set('portfolio', $configuration);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('portfolio.global.seo.site_url', 'https://example.test')
            ->where('portfolio.global.seo.indexing_enabled', true));

    $configuration['global']['seo']['site_url'] = 'http://example.test';
    config()->set('portfolio', $configuration);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->missing('portfolio.global.seo'));
});

test('it exposes the global photo and only valid optional component replacements', function () {
    $configuration = lotTwoConfiguration([
        'hero' => [
            'active' => true,
            'order' => 10,
            'headline' => 'Example headline for the test portfolio',
            'primary_cta' => [
                'label' => 'Example action',
                'target' => '/contact',
            ],
            'photo_override_url' => 'https://example.test/hero.jpg',
            'photo_alt' => 'Example hero photo',
        ],
        'about' => [
            'active' => true,
            'order' => 20,
            'title' => 'Example about title',
            'summary' => 'Example about summary.',
            'photo_override_url' => 'https://example.test/about.gif',
            'photo_alt' => 'Rejected example photo',
        ],
    ]);
    config()->set('portfolio', $configuration);

    $this->get('/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where(
                'portfolio.global.identity.profile_photo_url',
                'https://example.test/profile.webp',
            )
            ->where('portfolio.components.0.type', 'hero')
            ->where('portfolio.components.0.photo_override_url', 'https://example.test/hero.jpg')
            ->where('portfolio.components.0.photo_alt', 'Example hero photo')
            ->where('portfolio.components.1.type', 'about')
            ->missing('portfolio.components.1.photo_override_url')
            ->missing('portfolio.components.1.photo_alt'));
});

test('it never exposes private or operational configuration in the browser response', function () {
    $privateRecipient = 'private-recipient@example.test';
    $privateSecret = 'private-operational-example-secret';
    $configuration = lotTwoConfiguration();
    $configuration['global']['private_token'] = $privateSecret;
    $configuration['private'] = ['secret' => $privateSecret];

    config()->set('portfolio', $configuration);
    config()->set('portfolio-private.contact.recipient_email', $privateRecipient);
    config()->set('portfolio-private.operations.secret', $privateSecret);
    config()->set('mail.mailers.smtp.password', $privateSecret);

    $response = $this->get('/')->assertOk();
    $browserPayload = $response->getContent();

    expect($browserPayload)
        ->not->toContain('recipient_email')
        ->not->toContain($privateRecipient)
        ->not->toContain('private_token')
        ->not->toContain($privateSecret)
        ->not->toContain('smtp.password');
});
