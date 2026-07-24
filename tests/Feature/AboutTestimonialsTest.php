<?php

use App\Portfolio\PortfolioComposer;

function lotFiveConfiguration(): array
{
    return config('portfolio');
}

test('it exposes the exact validated About payload after Projects and excludes Testimonials', function () {
    config()->set('portfolio', lotFiveConfiguration());

    $portfolio = $this->get('/')->assertOk()->inertiaProps('portfolio');
    $components = collect($portfolio['components'])->keyBy('type');

    expect(array_column($portfolio['components'], 'type'))->toBe([
        'header',
        'hero',
        'services',
        'projects',
        'about',
    ])->and(array_column($portfolio['components'], 'order'))->toBe([10, 20, 30, 40, 50])
        ->and($components['about'])->toBe([
            'type' => 'about',
            'active' => true,
            'order' => 50,
            'title' => 'À propos',
            'summary' => 'Développeur PHP, Laravel et Vue.js, avec une expérience en SQL Server, API et automatisation, je conçois des solutions web et de données adaptées aux besoins métier.',
            'details' => 'Mon parcours réunit développement web, bases de données et automatisation. Je construis également Portfolio OS, un moteur configurable et réutilisable destiné à créer des portfolios professionnels d’une page, dont ce site constitue la première instance.',
            'expertise' => ['PHP', 'Laravel', 'Vue.js', 'SQL Server', 'API', 'Automatisation'],
        ])->and($components)->not->toHaveKey('testimonials')
        ->and($components['header']['navigation'])->toBe([
            ['label' => 'Services', 'target' => '#services'],
            ['label' => 'Projets', 'target' => '#projects'],
            ['label' => 'À propos', 'target' => '#about'],
        ]);
});

test('it excludes About when mandatory content is invalid', function () {
    $validConfiguration = lotFiveConfiguration();
    $invalidAboutComponents = [];

    $withoutTitle = $validConfiguration['components']['about'];
    unset($withoutTitle['title']);
    $invalidAboutComponents['missing title'] = $withoutTitle;

    $withInvalidSummary = $validConfiguration['components']['about'];
    $withInvalidSummary['summary'] = false;
    $invalidAboutComponents['invalid summary'] = $withInvalidSummary;

    foreach ($invalidAboutComponents as $case => $about) {
        $configuration = $validConfiguration;
        $configuration['components']['about'] = $about;
        $components = (new PortfolioComposer)->compose($configuration)['components'];

        expect(array_column($components, 'type'))->not->toContain('about', $case);
    }
});

test('it omits invalid or absent optional About fields without invalidating About', function () {
    $configuration = lotFiveConfiguration();
    $configuration['components']['about']['details'] = [];
    $configuration['components']['about']['expertise'] = [
        'PHP',
        '',
        false,
        str_repeat('x', 61),
    ];
    $configuration['components']['about']['highlights'] = ['Valid highlight', false, ''];
    $configuration['components']['about']['photo_override_url'] = 'javascript:alert(1)';
    $configuration['components']['about']['photo_alt'] = 'Unused alt';

    $components = collect(
        (new PortfolioComposer)->compose($configuration)['components'],
    )->keyBy('type');

    expect($components['about'])->not->toHaveKeys([
        'details',
        'photo_override_url',
        'photo_alt',
    ])->and($components['about']['expertise'])->toBe(['PHP'])
        ->and($components['about']['highlights'])->toBe(['Valid highlight']);
});

test('it excludes Testimonials without valid items and composes valid fixture fields contractually', function () {
    $configuration = lotFiveConfiguration();
    $configuration['components']['testimonials'] = [
        'active' => true,
        'order' => 60,
        'title' => false,
        'intro' => [],
        'items' => [
            [
                'quote' => 'Too short',
                'author_name' => 'Invalid',
            ],
            [
                'quote' => 'Une collaboration précise, fiable et très professionnelle.',
                'author_name' => 'Personne Exemple',
                'author_role' => 'Consultante',
                'organization' => 'Entreprise Exemple',
                'photo_url' => '/images/personne-exemple.webp',
                'company_logo_url' => '/images/entreprise-exemple.svg',
                'source_url' => 'https://example.test/temoignage',
            ],
        ],
    ];

    $components = collect(
        (new PortfolioComposer)->compose($configuration)['components'],
    )->keyBy('type');

    expect($components['testimonials'])->toBe([
        'type' => 'testimonials',
        'active' => true,
        'order' => 60,
        'items' => [[
            'quote' => 'Une collaboration précise, fiable et très professionnelle.',
            'author_name' => 'Personne Exemple',
            'author_role' => 'Consultante',
            'organization' => 'Entreprise Exemple',
            'photo_url' => '/images/personne-exemple.webp',
            'company_logo_url' => '/images/entreprise-exemple.svg',
            'source_url' => 'https://example.test/temoignage',
        ]],
    ]);

    $configuration['components']['testimonials']['items'] = [[
        'quote' => 'Too short',
        'author_name' => 'Invalid',
    ]];
    $types = array_column(
        (new PortfolioComposer)->compose($configuration)['components'],
        'type',
    );

    expect($types)->not->toContain('testimonials');
});

test('it exposes no absent Lot 5 fields, fixture values, or private values publicly', function () {
    $privateRecipient = 'private-lot-five-recipient@example.test';
    $privateSecret = 'private-lot-five-secret';
    $configuration = lotFiveConfiguration();
    $configuration['private'] = ['secret' => $privateSecret];

    config()->set('portfolio', $configuration);
    config()->set('portfolio-private.contact.recipient_email', $privateRecipient);
    config()->set('portfolio-private.operations.secret', $privateSecret);

    $response = $this->get('/')->assertOk();
    $portfolio = $response->inertiaProps('portfolio');
    $components = collect($portfolio['components'])->keyBy('type');
    $browserPayload = $response->getContent();

    expect($components['about'])->not->toHaveKeys([
        'highlights',
        'photo_override_url',
        'photo_alt',
    ])->and($components)->not->toHaveKey('testimonials')
        ->and($browserPayload)
        ->not->toContain('Personne Exemple')
        ->not->toContain('Entreprise Exemple')
        ->not->toContain('recipient_email')
        ->not->toContain($privateRecipient)
        ->not->toContain($privateSecret);
});
