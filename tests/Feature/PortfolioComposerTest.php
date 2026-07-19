<?php

use App\Portfolio\InvalidPortfolioConfiguration;
use App\Portfolio\PortfolioComposer;

function publicPortfolioConfiguration(array $components = []): array
{
    return [
        'schema_version' => '1.0',
        'global' => [
            'identity' => [
                'display_name' => 'Example Profile',
                'professional_title' => 'Example Professional Title',
                'tagline' => null,
                'logo_url' => null,
                'profile_photo_url' => null,
                'default_language' => 'fr',
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
                'canonical_url' => null,
                'social_image_url' => null,
                'indexing_enabled' => false,
            ],
            'socials' => [],
            'contact' => [
                'email' => 'public@example.test',
                'phone' => null,
                'whatsapp_url' => null,
                'booking_url' => null,
                'location_text' => null,
            ],
            'legal' => [
                'publisher_name' => null,
                'registration_number' => null,
                'legal_notice_url' => null,
                'privacy_policy_url' => 'https://example.test/privacy',
                'form_consent_text' => 'Example consent text used only by tests.',
                'copyright_year' => 2026,
                'copyright_owner' => null,
            ],
        ],
        'components' => $components,
    ];
}

function validHeader(int $order = 10, bool $active = true): array
{
    return [
        'active' => $active,
        'order' => $order,
        'brand_label' => 'Example brand',
        'show_logo' => false,
    ];
}

function validHero(int $order = 20, bool $active = true): array
{
    return [
        'active' => $active,
        'order' => $order,
        'headline' => 'Example headline for the test portfolio',
        'primary_cta' => [
            'label' => 'Example action',
            'target' => '/contact',
        ],
    ];
}

function validServices(int $order = 30): array
{
    return [
        'active' => true,
        'order' => $order,
        'title' => 'Example services',
        'items' => [[
            'title' => 'Example service',
            'description' => 'Example service description.',
            'benefit' => 'Example service benefit.',
        ]],
    ];
}

function validProjects(int $order = 40): array
{
    return [
        'active' => true,
        'order' => $order,
        'title' => 'Example projects',
        'items' => [[
            'title' => 'Example project',
            'summary' => 'Example project summary.',
        ]],
    ];
}

function validAbout(int $order = 50, bool $active = true): array
{
    return [
        'active' => $active,
        'order' => $order,
        'title' => 'Example about title',
        'summary' => 'Example about summary.',
    ];
}

function validTestimonials(int $order = 60): array
{
    return [
        'active' => true,
        'order' => $order,
        'items' => [[
            'quote' => 'Example testimonial quote.',
            'author_name' => 'Example Author',
        ]],
    ];
}

function validContactCta(int $order = 70): array
{
    return [
        'active' => true,
        'order' => $order,
        'title' => 'Example contact action',
        'primary_action' => [
            'label' => 'Example contact',
            'target' => '/contact',
        ],
    ];
}

function validContactForm(int $order = 80): array
{
    return [
        'active' => true,
        'order' => $order,
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
        'submitting_message' => 'Sending example',
        'success_message' => 'Example sent.',
        'consent_required' => true,
    ];
}

function validFooter(int $order = 90): array
{
    return [
        'active' => true,
        'order' => $order,
        'text' => 'Example footer text.',
    ];
}

function allValidComponents(): array
{
    return [
        'header' => validHeader(),
        'hero' => validHero(),
        'services' => validServices(),
        'projects' => validProjects(),
        'about' => validAbout(),
        'testimonials' => validTestimonials(),
        'contact_cta' => validContactCta(),
        'contact_form' => validContactForm(),
        'footer' => validFooter(),
    ];
}

function composePortfolio(array $configuration): array
{
    return (new PortfolioComposer)->compose($configuration);
}

test('it activates enabled components and treats disabled absent and unknown components as inactive', function () {
    $configuration = publicPortfolioConfiguration([
        'hero' => validHero(),
        'about' => validAbout(active: false),
        'unknown_component' => [
            'active' => true,
            'order' => 10,
            'content' => 'Unknown test content',
        ],
    ]);

    $types = array_column(composePortfolio($configuration)['components'], 'type');

    expect($types)->toBe(['hero']);
});

test('it orders valid active components by their configured order', function () {
    $configuration = publicPortfolioConfiguration([
        'hero' => validHero(order: 30),
        'services' => validServices(order: 10),
        'about' => validAbout(order: 20),
    ]);

    $components = composePortfolio($configuration)['components'];

    expect(array_column($components, 'type'))->toBe(['services', 'about', 'hero'])
        ->and(array_column($components, 'order'))->toBe([10, 20, 30]);
});

test('it safely excludes string null zero negative and duplicate orders', function () {
    $stringOrder = validHero();
    $stringOrder['order'] = '10';
    $nullOrder = validServices();
    $nullOrder['order'] = null;
    $zeroOrder = validProjects();
    $zeroOrder['order'] = 0;
    $negativeOrder = validAbout();
    $negativeOrder['order'] = -1;

    $configuration = publicPortfolioConfiguration([
        'header' => validHeader(order: 40),
        'hero' => $stringOrder,
        'services' => $nullOrder,
        'projects' => $zeroOrder,
        'about' => $negativeOrder,
        'testimonials' => validTestimonials(order: 50),
        'contact_cta' => validContactCta(order: 50),
        'footer' => validFooter(order: 60),
    ]);

    $components = composePortfolio($configuration)['components'];

    expect(array_column($components, 'type'))->toBe(['header', 'footer'])
        ->and(array_column($components, 'order'))->toBe([40, 60]);
});

test('it accepts the minimum valid content of all nine component types', function () {
    $components = composePortfolio(publicPortfolioConfiguration(allValidComponents()))['components'];

    expect(array_column($components, 'type'))->toBe([
        'header',
        'hero',
        'services',
        'projects',
        'about',
        'testimonials',
        'contact_cta',
        'contact_form',
        'footer',
    ]);
});

test('it rejects required values with wrong types for all nine component types', function () {
    $invalidComponents = allValidComponents();
    $invalidComponents['header']['brand_label'] = ['invalid'];
    $invalidComponents['hero']['headline'] = 123;
    $invalidComponents['services']['title'] = true;
    $invalidComponents['projects']['items'] = 'invalid';
    $invalidComponents['about']['summary'] = false;
    $invalidComponents['testimonials']['items'][0]['quote'] = 123;
    $invalidComponents['contact_cta']['primary_action']['label'] = [];
    $invalidComponents['contact_form']['consent_required'] = 'true';
    $invalidComponents['footer']['text'] = ['invalid'];

    foreach ($invalidComponents as $type => $component) {
        $configuration = publicPortfolioConfiguration([$type => $component]);

        if (in_array($type, ['header', 'footer'], true)) {
            $configuration['global']['identity'] = null;
        }

        expect(composePortfolio($configuration)['components'])
            ->toBe([], "The {$type} component should be invalid.");
    }
});

test('it applies required text length constraints and omits invalid optional values', function () {
    $hero = validHero();
    $hero['headline'] = str_repeat('x', 141);
    $hero['eyebrow'] = str_repeat('x', 61);

    expect(composePortfolio(publicPortfolioConfiguration(['hero' => $hero]))['components'])
        ->toBe([]);

    $hero['headline'] = str_repeat('x', 10);
    $component = composePortfolio(publicPortfolioConfiguration(['hero' => $hero]))['components'][0];

    expect($component)->not->toHaveKey('eyebrow')
        ->and($component['headline'])->toBe(str_repeat('x', 10));
});

test('it validates required global blocks strictly and omits invalid optional global values', function () {
    $configuration = publicPortfolioConfiguration();
    $configuration['global']['identity']['display_name'] = 'x';
    $configuration['global']['appearance']['primary_color'] = '#FFF';
    $configuration['global']['seo']['indexing_enabled'] = 'false';

    $global = composePortfolio($configuration)['global'];

    expect($global)->not->toHaveKeys(['identity', 'appearance', 'seo']);

    $configuration = publicPortfolioConfiguration();
    $configuration['global']['identity']['tagline'] = ['invalid'];
    $configuration['global']['appearance']['accent_color'] = 'blue';
    $configuration['global']['contact']['phone'] = 123456;
    $configuration['global']['legal']['legal_notice_url'] = 'http://example.test/legal';

    $global = composePortfolio($configuration)['global'];

    expect($global['identity'])->not->toHaveKey('tagline')
        ->and($global['appearance'])->not->toHaveKey('accent_color')
        ->and($global['contact'])->not->toHaveKey('phone')
        ->and($global['legal'])->not->toHaveKey('legal_notice_url');
});

test('it filters invalid navigation social actions list items and form options individually', function () {
    $configuration = publicPortfolioConfiguration([
        'header' => [
            ...validHeader(),
            'navigation' => [
                ['label' => 'Services', 'target' => '#services'],
                ['label' => '', 'target' => '#services'],
                ['label' => 'Incomplete'],
            ],
            'primary_cta' => ['label' => 'Incomplete'],
        ],
        'services' => [
            ...validServices(),
            'items' => [
                validServices()['items'][0],
                ['title' => 'Invalid service', 'description' => 'Missing benefit'],
                [
                    'title' => 'Second service',
                    'description' => 'Second description.',
                    'benefit' => 'Second benefit.',
                    'cta' => ['label' => 'Incomplete'],
                ],
            ],
        ],
        'projects' => [
            ...validProjects(),
            'items' => [
                [
                    ...validProjects()['items'][0],
                    'tags' => ['valid', '', false, 'also-valid'],
                ],
                ['title' => 'Incomplete project'],
            ],
        ],
        'about' => [
            ...validAbout(),
            'expertise' => ['Valid expertise', '', 123],
            'highlights' => [false, 'Valid highlight'],
        ],
        'testimonials' => [
            ...validTestimonials(),
            'items' => [
                validTestimonials()['items'][0],
                ['quote' => 'Missing author name'],
            ],
        ],
        'contact_form' => [
            ...validContactForm(),
            'fields' => [
                ...validContactForm()['fields'],
                ['key' => 'invalid', 'type' => 'text', 'label' => 123, 'required' => true],
                [
                    'key' => 'subject',
                    'type' => 'select',
                    'label' => 'Example subject',
                    'required' => false,
                    'options' => ['Valid option', '', false, 'Second option'],
                ],
                ['key' => 'duplicate', 'type' => 'text', 'label' => 'First', 'required' => false],
                ['key' => 'duplicate', 'type' => 'text', 'label' => 'Second', 'required' => false],
            ],
        ],
    ]);
    $configuration['global']['socials'] = [
        ['platform' => 'github', 'url' => 'https://example.test/profile'],
        ['platform' => 'GitHub', 'url' => 'https://example.test/invalid'],
        ['platform' => 'other', 'url' => 'https://example.test/other'],
        ['platform' => 'other', 'url' => 'https://example.test/community', 'label' => 'Community'],
        ['platform' => 'x', 'url' => 'http://example.test/invalid'],
    ];

    $portfolio = composePortfolio($configuration);
    $components = collect($portfolio['components'])->keyBy('type');

    expect($portfolio['global']['socials'])->toHaveCount(2)
        ->and(array_column($portfolio['global']['socials'], 'platform'))->toBe(['github', 'other'])
        ->and($components['header']['navigation'])->toHaveCount(1)
        ->and($components['header'])->not->toHaveKey('primary_cta')
        ->and($components['services']['items'])->toHaveCount(2)
        ->and($components['services']['items'][1])->not->toHaveKey('cta')
        ->and($components['projects']['items'])->toHaveCount(1)
        ->and($components['projects']['items'][0]['tags'])->toBe(['valid', 'also-valid'])
        ->and($components['about']['expertise'])->toBe(['Valid expertise'])
        ->and($components['about']['highlights'])->toBe(['Valid highlight'])
        ->and($components['testimonials']['items'])->toHaveCount(1)
        ->and($components['contact_form']['fields'])->toHaveCount(3)
        ->and(array_column($components['contact_form']['fields'], 'key'))
        ->not->toContain('duplicate')
        ->and($components['contact_form']['fields'][2]['options'])
        ->toBe(['Valid option', 'Second option']);
});

test('it enforces the exact maximum sizes after individual list filtering', function () {
    $header = validHeader();
    $header['navigation'] = array_fill(0, 9, ['label' => 'Example', 'target' => '/example']);
    $services = validServices();
    $services['items'] = array_fill(0, 7, validServices()['items'][0]);
    $projects = validProjects();
    $projects['items'] = array_fill(0, 13, validProjects()['items'][0]);
    $contactForm = validContactForm();
    $additionalFieldKeys = [
        'company',
        'subject',
        'budget',
        'timeline',
        'location',
        'website',
        'role',
        'source',
        'notes',
    ];
    $contactForm['fields'] = [
        ...validContactForm()['fields'],
        ...array_map(
            fn (string $key): array => [
                'key' => $key,
                'type' => 'text',
                'label' => "Field {$key}",
                'required' => false,
            ],
            $additionalFieldKeys,
        ),
    ];
    $configuration = publicPortfolioConfiguration([
        'header' => $header,
        'services' => $services,
        'projects' => $projects,
        'contact_form' => $contactForm,
    ]);
    $configuration['global']['socials'] = array_fill(
        0,
        11,
        ['platform' => 'github', 'url' => 'https://example.test/profile'],
    );

    $portfolio = composePortfolio($configuration);
    $components = collect($portfolio['components'])->keyBy('type');

    expect($portfolio['global']['socials'])->toHaveCount(10)
        ->and($components['header']['navigation'])->toHaveCount(8)
        ->and($components['services']['items'])->toHaveCount(6)
        ->and($components['projects']['items'])->toHaveCount(12)
        ->and($components['contact_form']['fields'])->toHaveCount(10);
});

test('it validates navigation and action targets by their exact allowed formats', function () {
    $header = validHeader();
    $header['navigation'] = [
        ['label' => 'Hero', 'target' => '#hero'],
        ['label' => 'HTTPS', 'target' => 'https://example.test/path'],
        ['label' => 'Root', 'target' => '/path'],
        ['label' => 'HTTP', 'target' => 'http://example.test/path'],
        ['label' => 'Protocol relative', 'target' => '//example.test/path'],
        ['label' => 'Traversal', 'target' => '/path/../private'],
        ['label' => 'Mail', 'target' => 'mailto:test@example.test'],
        ['label' => 'Phone', 'target' => 'tel:+212600000000'],
    ];
    $hero = validHero();
    $hero['primary_cta']['target'] = 'mailto:test@example.test';
    $hero['secondary_cta'] = ['label' => 'Phone', 'target' => 'tel:+212600000000'];

    $components = collect(composePortfolio(publicPortfolioConfiguration([
        'header' => $header,
        'hero' => $hero,
    ]))['components'])->keyBy('type');

    expect(array_column($components['header']['navigation'], 'target'))
        ->toBe(['#hero', 'https://example.test/path', '/path'])
        ->and($components['hero']['primary_cta']['target'])->toBe('mailto:test@example.test')
        ->and($components['hero']['secondary_cta']['target'])->toBe('tel:+212600000000');
});

test('it rejects malformed mailto tel and credentialed HTTPS action targets', function () {
    $header = validHeader();
    $header['primary_cta'] = [
        'label' => 'Invalid mail',
        'target' => 'mailto:test@example.test?subject=example',
    ];
    $hero = validHero();
    $hero['secondary_cta'] = [
        'label' => 'Invalid HTTPS',
        'target' => 'https://user@example.test/private',
    ];
    $services = validServices();
    $services['items'][0]['cta'] = [
        'label' => 'Invalid phone',
        'target' => 'tel:+212 600-000-000',
    ];

    $components = collect(composePortfolio(publicPortfolioConfiguration([
        'header' => $header,
        'hero' => $hero,
        'services' => $services,
    ]))['components'])->keyBy('type');

    expect($components['header'])->not->toHaveKey('primary_cta')
        ->and($components['hero'])->not->toHaveKey('secondary_cta')
        ->and($components['services']['items'][0])->not->toHaveKey('cta');
});

test('it allows localhost IPv4 IPv6 and explicit ports for site URL only when indexing is disabled', function () {
    foreach ([
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://[2001:db8::1]:8080',
    ] as $siteUrl) {
        $configuration = publicPortfolioConfiguration();
        $configuration['global']['seo']['site_url'] = $siteUrl;

        expect(composePortfolio($configuration)['global']['seo']['site_url'])->toBe($siteUrl);

        $configuration['global']['seo']['indexing_enabled'] = true;

        expect(composePortfolio($configuration)['global'])->not->toHaveKey('seo');
    }
});

test('it rejects localhost IPv4 IPv6 and explicit ports from every public URL outside site URL', function () {
    $header = validHeader();
    $header['navigation'] = [
        ['label' => 'Localhost', 'target' => 'https://localhost/path'],
        ['label' => 'IPv4', 'target' => 'https://127.0.0.1/path'],
        ['label' => 'IPv6', 'target' => 'https://[2001:db8::1]/path'],
        ['label' => 'Port', 'target' => 'https://example.test:8443/path'],
        ['label' => 'Public', 'target' => 'https://example.test/path'],
    ];
    $hero = validHero();
    $hero['secondary_cta'] = ['label' => 'Local action', 'target' => 'https://localhost/action'];
    $projects = validProjects();
    $projects['items'][0]['project_url'] = 'https://127.0.0.1/project';
    $testimonials = validTestimonials();
    $testimonials['items'][0]['source_url'] = 'https://[2001:db8::1]/source';
    $configuration = publicPortfolioConfiguration([
        'header' => $header,
        'hero' => $hero,
        'projects' => $projects,
        'testimonials' => $testimonials,
    ]);
    $configuration['global']['seo']['site_url'] = 'http://localhost:8000';
    $configuration['global']['seo']['canonical_url'] = 'https://localhost/canonical';
    $configuration['global']['seo']['social_image_url'] = 'https://example.test:8443/image.png';
    $configuration['global']['identity']['logo_url'] = 'https://localhost/logo';
    $configuration['global']['socials'] = [
        ['platform' => 'github', 'url' => 'https://localhost/profile'],
        ['platform' => 'linkedin', 'url' => 'https://127.0.0.1/profile'],
        ['platform' => 'facebook', 'url' => 'https://[2001:db8::1]/profile'],
        ['platform' => 'x', 'url' => 'https://example.test:8443/profile'],
        ['platform' => 'other', 'url' => 'https://example.test/profile', 'label' => 'Public'],
    ];
    $configuration['global']['contact']['whatsapp_url'] = 'https://127.0.0.1/contact';
    $configuration['global']['contact']['booking_url'] = 'https://example.test:8443/booking';
    $configuration['global']['legal']['legal_notice_url'] = 'https://localhost/legal';

    $portfolio = composePortfolio($configuration);
    $components = collect($portfolio['components'])->keyBy('type');

    expect($portfolio['global']['seo']['site_url'])->toBe('http://localhost:8000')
        ->and($portfolio['global']['seo'])->not->toHaveKeys(['canonical_url', 'social_image_url'])
        ->and($portfolio['global']['identity'])->not->toHaveKey('logo_url')
        ->and($portfolio['global']['socials'])->toBe([
            ['platform' => 'other', 'url' => 'https://example.test/profile', 'label' => 'Public'],
        ])
        ->and($portfolio['global']['contact'])->not->toHaveKeys(['whatsapp_url', 'booking_url'])
        ->and($portfolio['global']['legal'])->not->toHaveKey('legal_notice_url')
        ->and($components['header']['navigation'])->toBe([
            ['label' => 'Public', 'target' => 'https://example.test/path'],
        ])
        ->and($components['hero'])->not->toHaveKey('secondary_cta')
        ->and($components['projects']['items'][0])->not->toHaveKey('project_url')
        ->and($components['testimonials']['items'][0])->not->toHaveKey('source_url');
});

test('it accepts extensionless HTTPS images and requires extensions for local images', function () {
    $configuration = publicPortfolioConfiguration([
        'hero' => [
            ...validHero(),
            'photo_override_url' => '/images/profile',
            'photo_alt' => 'Invalid local image',
        ],
        'about' => [
            ...validAbout(),
            'photo_override_url' => 'https://example.test/image-resource',
            'photo_alt' => 'Valid remote image',
        ],
        'projects' => [
            ...validProjects(),
            'items' => [[
                ...validProjects()['items'][0],
                'image_url' => '/images/project.PNG?version=1',
                'image_alt' => 'Valid local image',
            ]],
        ],
    ]);
    $configuration['global']['identity']['logo_url'] = 'https://example.test/logo';
    $configuration['global']['identity']['profile_photo_url'] = '/images/profile.webp';

    $portfolio = composePortfolio($configuration);
    $components = collect($portfolio['components'])->keyBy('type');

    expect($portfolio['global']['identity']['logo_url'])->toBe('https://example.test/logo')
        ->and($portfolio['global']['identity']['profile_photo_url'])->toBe('/images/profile.webp')
        ->and($components['hero'])->not->toHaveKeys(['photo_override_url', 'photo_alt'])
        ->and($components['about']['photo_override_url'])->toBe('https://example.test/image-resource')
        ->and($components['projects']['items'][0]['image_url'])
        ->toBe('/images/project.PNG?version=1');
});

test('it accepts only the five allowed extensions when an HTTPS image has an extension', function () {
    foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $extension) {
        $hero = [
            ...validHero(),
            'photo_override_url' => "https://example.test/profile.{$extension}?version=1",
            'photo_alt' => 'Valid image',
        ];
        $component = composePortfolio(publicPortfolioConfiguration(['hero' => $hero]))['components'][0];

        expect($component['photo_override_url'])
            ->toBe("https://example.test/profile.{$extension}?version=1")
            ->and($component['photo_alt'])->toBe('Valid image');
    }
});

test('it rejects forbidden HTTPS image extensions and removes their associated alternative text', function () {
    foreach (['gif', 'pdf', 'exe'] as $extension) {
        $hero = [
            ...validHero(),
            'photo_override_url' => "https://example.test/profile.{$extension}?version=1",
            'photo_alt' => 'Rejected image',
        ];
        $component = composePortfolio(publicPortfolioConfiguration(['hero' => $hero]))['components'][0];

        expect($component)->not->toHaveKeys(['photo_override_url', 'photo_alt']);
    }
});

test('it replaces absent and invalid copyright years with the current server year', function () {
    $configuration = publicPortfolioConfiguration();
    $configuration['global']['legal']['copyright_year'] = '2026';

    expect(composePortfolio($configuration)['global']['legal']['copyright_year'])
        ->toBe((int) date('Y'));

    unset($configuration['global']['legal']['copyright_year']);

    expect(composePortfolio($configuration)['global']['legal']['copyright_year'])
        ->toBe((int) date('Y'));
});

test('it excludes an active footer without any displayable content', function () {
    $configuration = publicPortfolioConfiguration([
        'footer' => [
            'active' => true,
            'order' => 90,
            'show_identity' => false,
            'show_navigation' => false,
            'show_socials' => false,
            'show_contact' => false,
            'show_legal_links' => false,
        ],
    ]);
    $configuration['global']['identity'] = null;
    $configuration['global']['legal']['copyright_owner'] = null;

    expect(composePortfolio($configuration)['components'])->toBe([]);
});

test('it uses the final valid header navigation as the footer navigation fallback', function () {
    $header = validHeader();
    $header['navigation'] = [
        ['label' => 'Services', 'target' => '#services'],
        ['label' => 'Missing', 'target' => '#projects'],
    ];
    $footer = [
        'active' => true,
        'order' => 90,
        'show_navigation' => true,
    ];

    $components = collect(composePortfolio(publicPortfolioConfiguration([
        'header' => $header,
        'services' => validServices(),
        'footer' => $footer,
    ]))['components'])->keyBy('type');

    expect($components['footer']['navigation'])->toBe([
        ['label' => 'Services', 'target' => '#services'],
    ]);
});

test('it removes required anchor dependencies iteratively and filters optional anchors after stabilization', function () {
    $header = validHeader();
    $header['navigation'] = [
        ['label' => 'Hero', 'target' => '#hero'],
        ['label' => 'Services', 'target' => '#services'],
    ];
    $header['primary_cta'] = ['label' => 'Hero', 'target' => '#hero'];
    $hero = validHero();
    $hero['primary_cta']['target'] = '#contact-cta';
    $contactCta = validContactCta();
    $contactCta['primary_action']['target'] = '#contact-form';

    $components = composePortfolio(publicPortfolioConfiguration([
        'header' => $header,
        'hero' => $hero,
        'services' => validServices(),
        'contact_cta' => $contactCta,
    ]))['components'];

    expect(array_column($components, 'type'))->toBe(['header', 'services'])
        ->and($components[0]['navigation'])->toBe([
            ['label' => 'Services', 'target' => '#services'],
        ])
        ->and($components[0])->not->toHaveKey('primary_cta');
});

test('it preserves a stable required-anchor cycle between intrinsically valid components', function () {
    $hero = validHero();
    $hero['primary_cta']['target'] = '#contact-cta';
    $contactCta = validContactCta();
    $contactCta['primary_action']['target'] = '#hero';

    $components = composePortfolio(publicPortfolioConfiguration([
        'hero' => $hero,
        'contact_cta' => $contactCta,
    ]))['components'];

    expect(array_column($components, 'type'))->toBe(['hero', 'contact_cta']);
});

test('it filters invalid optional anchors from service actions and footer navigation', function () {
    $services = validServices();
    $services['items'][0]['cta'] = ['label' => 'Missing', 'target' => '#projects'];
    $footer = validFooter();
    $footer['show_navigation'] = true;
    $footer['navigation'] = [
        ['label' => 'Missing', 'target' => '#projects'],
        ['label' => 'Services', 'target' => '#services'],
    ];

    $components = collect(composePortfolio(publicPortfolioConfiguration([
        'services' => $services,
        'footer' => $footer,
    ]))['components'])->keyBy('type');

    expect($components['services']['items'][0])->not->toHaveKey('cta')
        ->and($components['footer']['navigation'])->toBe([
            ['label' => 'Services', 'target' => '#services'],
        ]);
});

test('it rejects a contact form without its required privacy data', function () {
    $configuration = publicPortfolioConfiguration(['contact_form' => validContactForm()]);
    $configuration['global']['legal']['privacy_policy_url'] = null;

    expect(composePortfolio($configuration)['components'])->toBe([]);

    $configuration = publicPortfolioConfiguration(['contact_form' => validContactForm()]);
    $configuration['global']['legal']['form_consent_text'] = null;

    expect(composePortfolio($configuration)['components'])->toBe([]);
});

test('it fails closed for absent malformed or unsupported schema versions', function () {
    foreach ([null, 1.0, 'v1.0', '1', '1.0.0', '01.0', '1.1'] as $version) {
        $configuration = publicPortfolioConfiguration(['hero' => validHero()]);
        $configuration['schema_version'] = $version;

        expect(fn () => composePortfolio($configuration))
            ->toThrow(InvalidPortfolioConfiguration::class);

        config()->set('portfolio', $configuration);

        $this->get('/')
            ->assertStatus(500)
            ->assertHeaderMissing('X-Inertia')
            ->assertDontSee('schema_version', false)
            ->assertDontSee('data-page', false);
    }
});

test('it never exposes recipient email in Inertia properties', function () {
    $privateRecipient = 'private-recipient@example.test';
    $contactForm = validContactForm();
    $contactForm['recipient_email'] = $privateRecipient;

    config()->set('portfolio', publicPortfolioConfiguration([
        'contact_form' => $contactForm,
    ]));
    config()->set('portfolio-private.contact.recipient_email', $privateRecipient);

    $props = $this->get('/')->assertOk()->inertiaProps();

    expect(json_encode($props))
        ->not->toContain('recipient_email')
        ->not->toContain($privateRecipient);
});

test('it never exposes mail secrets or operational private parameters', function () {
    $privateValue = 'private-operational-test-value';
    $configuration = publicPortfolioConfiguration([
        'hero' => [
            ...validHero(),
            'operational_token' => $privateValue,
        ],
    ]);
    $configuration['private'] = ['secret' => $privateValue];
    $configuration['global']['mail'] = ['password' => $privateValue];
    $configuration['global']['identity']['secret'] = $privateValue;

    config()->set('portfolio', $configuration);
    config()->set('portfolio-private.operations.secret', $privateValue);
    config()->set('mail.mailers.smtp.password', $privateValue);

    $props = $this->get('/')->assertOk()->inertiaProps();

    expect(json_encode($props))
        ->not->toContain($privateValue)
        ->not->toContain('operational_token')
        ->not->toContain('password')
        ->not->toContain('secret');
});
