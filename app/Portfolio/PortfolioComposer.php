<?php

namespace App\Portfolio;

final class PortfolioComposer
{
    private const ANCHORS = [
        '#header' => 'header',
        '#hero' => 'hero',
        '#services' => 'services',
        '#projects' => 'projects',
        '#about' => 'about',
        '#testimonials' => 'testimonials',
        '#contact-cta' => 'contact_cta',
        '#contact-form' => 'contact_form',
        '#footer' => 'footer',
    ];

    private const COMPONENT_TYPES = [
        'header',
        'hero',
        'services',
        'projects',
        'about',
        'testimonials',
        'contact_cta',
        'contact_form',
        'footer',
    ];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

    private const SOCIAL_PLATFORMS = [
        'linkedin',
        'github',
        'facebook',
        'instagram',
        'youtube',
        'x',
        'other',
    ];

    public function compose(array $configuration): array
    {
        $this->assertSupportedSchemaVersion($configuration['schema_version'] ?? null);

        $globalSource = is_array($configuration['global'] ?? null)
            ? $configuration['global']
            : [];
        $global = $this->sanitizeGlobal($globalSource);
        $configuredComponents = is_array($configuration['components'] ?? null)
            ? $configuration['components']
            : [];

        $components = [];

        foreach (self::COMPONENT_TYPES as $type) {
            $source = $configuredComponents[$type] ?? null;

            if (! is_array($source)
                || ($source['active'] ?? null) !== true
                || ! is_int($source['order'] ?? null)
                || $source['order'] < 1) {
                continue;
            }

            $content = $this->sanitizeComponent($type, $source, $global, false);

            if ($content === null) {
                continue;
            }

            $components[$type] = [
                'type' => $type,
                'active' => true,
                'order' => $source['order'],
                ...$content,
            ];
        }

        $orderCounts = array_count_values(array_column($components, 'order'));
        $components = array_filter(
            $components,
            fn (array $component): bool => $orderCounts[$component['order']] === 1,
        );

        $components = $this->stabilizeComponents($components, $global);

        usort(
            $components,
            fn (array $left, array $right): int => $left['order'] <=> $right['order'],
        );

        return [
            'schema_version' => '1.0',
            'global' => $global,
            'components' => array_values($components),
        ];
    }

    private function assertSupportedSchemaVersion(mixed $version): void
    {
        if (! is_string($version)
            || preg_match('/^(0|[1-9]\d*)\.(0|[1-9]\d*)$/', $version) !== 1
            || $version !== '1.0') {
            throw new InvalidPortfolioConfiguration;
        }
    }

    private function sanitizeGlobal(array $source): array
    {
        $global = [];
        $identity = $this->sanitizeIdentity($source['identity'] ?? null, false);

        if ($identity !== null) {
            $global['identity'] = $identity;
        }

        $appearance = $this->sanitizeAppearance($source['appearance'] ?? null);

        if ($appearance !== null) {
            $global['appearance'] = $appearance;
        }

        $seo = $this->sanitizeSeo($source['seo'] ?? null);

        if ($seo !== null) {
            $global['seo'] = $seo;
        }

        $socials = $this->sanitizeSocials($source['socials'] ?? null, false);

        if ($socials !== []) {
            $global['socials'] = $socials;
        }

        $contact = $this->sanitizeContact($source['contact'] ?? null, false);

        if ($contact !== null) {
            $global['contact'] = $contact;
        }

        $global['legal'] = $this->sanitizeLegal(
            $source['legal'] ?? null,
            $identity,
            false,
        );

        return $global;
    }

    private function sanitizeIdentity(mixed $source, bool $allowLocalUrls): ?array
    {
        if (! is_array($source)) {
            return null;
        }

        $displayName = $this->text($source['display_name'] ?? null, 2, 100);
        $professionalTitle = $this->text($source['professional_title'] ?? null, 2, 120);
        $language = is_string($source['default_language'] ?? null)
            && preg_match('/^[A-Za-z]{2}$/', $source['default_language']) === 1
                ? $source['default_language']
                : null;

        if ($displayName === null || $professionalTitle === null || $language === null) {
            return null;
        }

        $identity = [
            'display_name' => $displayName,
            'professional_title' => $professionalTitle,
            'default_language' => $language,
        ];
        $this->includeText($identity, 'tagline', $source['tagline'] ?? null, 1, 160);
        $this->includeImage($identity, 'logo_url', $source['logo_url'] ?? null, $allowLocalUrls);
        $this->includeImage(
            $identity,
            'profile_photo_url',
            $source['profile_photo_url'] ?? null,
            $allowLocalUrls,
        );

        return $identity;
    }

    private function sanitizeAppearance(mixed $source): ?array
    {
        if (! is_array($source) || ! $this->isColor($source['primary_color'] ?? null)) {
            return null;
        }

        $appearance = ['primary_color' => $source['primary_color']];

        foreach (['accent_color', 'background_color', 'text_color'] as $key) {
            if ($this->isColor($source[$key] ?? null)) {
                $appearance[$key] = $source[$key];
            }
        }

        $this->includeText($appearance, 'font_family', $source['font_family'] ?? null, 1, 80);

        if (in_array($source['color_mode'] ?? null, ['light', 'dark'], true)) {
            $appearance['color_mode'] = $source['color_mode'];
        }

        return $appearance;
    }

    private function sanitizeSeo(mixed $source): ?array
    {
        if (! is_array($source) || ! is_bool($source['indexing_enabled'] ?? null)) {
            return null;
        }

        $indexingEnabled = $source['indexing_enabled'];
        $siteUrl = $this->absoluteUrl(
            $source['site_url'] ?? null,
            $indexingEnabled ? ['https'] : ['http', 'https'],
            ! $indexingEnabled,
        );
        $title = $this->text($source['meta_title'] ?? null, 10, 60);
        $description = $this->text($source['meta_description'] ?? null, 70, 160);

        if ($siteUrl === null || $title === null || $description === null) {
            return null;
        }

        $seo = [
            'site_url' => $siteUrl,
            'meta_title' => $title,
            'meta_description' => $description,
            'indexing_enabled' => $indexingEnabled,
        ];
        $canonical = $this->absoluteUrl(
            $source['canonical_url'] ?? null,
            ['https'],
            false,
        );

        if ($canonical !== null) {
            $seo['canonical_url'] = $canonical;
        }

        $this->includeImage(
            $seo,
            'social_image_url',
            $source['social_image_url'] ?? null,
            false,
        );

        return $seo;
    }

    private function sanitizeSocials(mixed $source, bool $allowLocalUrls): array
    {
        if (! is_array($source) || ! array_is_list($source)) {
            return [];
        }

        $socials = [];

        foreach ($source as $item) {
            if (! is_array($item)
                || ! in_array($item['platform'] ?? null, self::SOCIAL_PLATFORMS, true)) {
                continue;
            }

            $url = $this->absoluteUrl($item['url'] ?? null, ['https'], $allowLocalUrls);
            $label = $this->text($item['label'] ?? null, 1, 50);

            if ($url === null || ($item['platform'] === 'other' && $label === null)) {
                continue;
            }

            $social = [
                'platform' => $item['platform'],
                'url' => $url,
            ];

            if ($label !== null) {
                $social['label'] = $label;
            }

            $socials[] = $social;

            if (count($socials) === 10) {
                break;
            }
        }

        return $socials;
    }

    private function sanitizeContact(mixed $source, bool $allowLocalUrls): ?array
    {
        if (! is_array($source)) {
            return null;
        }

        $contact = [];

        if (is_string($source['email'] ?? null)
            && filter_var($source['email'], FILTER_VALIDATE_EMAIL) !== false) {
            $contact['email'] = $source['email'];
        }

        $this->includeText($contact, 'phone', $source['phone'] ?? null, 6, 20);

        foreach (['whatsapp_url', 'booking_url'] as $key) {
            $url = $this->absoluteUrl($source[$key] ?? null, ['https'], $allowLocalUrls);

            if ($url !== null) {
                $contact[$key] = $url;
            }
        }

        $this->includeText($contact, 'location_text', $source['location_text'] ?? null, 1, 100);

        if (! array_intersect(['email', 'phone', 'whatsapp_url', 'booking_url'], array_keys($contact))) {
            return null;
        }

        return $contact;
    }

    private function sanitizeLegal(mixed $source, ?array $identity, bool $allowLocalUrls): array
    {
        $source = is_array($source) ? $source : [];
        $legal = [];
        $publisher = $this->text($source['publisher_name'] ?? null, 1, 120)
            ?? ($identity['display_name'] ?? null);
        $owner = $this->text($source['copyright_owner'] ?? null, 1, 120)
            ?? ($identity['display_name'] ?? null);

        if ($publisher !== null) {
            $legal['publisher_name'] = $publisher;
        }

        $this->includeText(
            $legal,
            'registration_number',
            $source['registration_number'] ?? null,
            1,
            80,
        );

        foreach (['legal_notice_url', 'privacy_policy_url'] as $key) {
            $url = $this->absoluteUrl($source[$key] ?? null, ['https'], $allowLocalUrls);

            if ($url !== null) {
                $legal[$key] = $url;
            }
        }

        $this->includeText($legal, 'form_consent_text', $source['form_consent_text'] ?? null);
        $year = $source['copyright_year'] ?? null;
        $currentYear = (int) date('Y');
        $legal['copyright_year'] = is_int($year) && $year >= 2000 && $year <= $currentYear
            ? $year
            : $currentYear;

        if ($owner !== null) {
            $legal['copyright_owner'] = $owner;
        }

        return $legal;
    }

    private function sanitizeComponent(
        string $type,
        array $source,
        array $global,
        bool $allowLocalUrls,
    ): ?array {
        return match ($type) {
            'header' => $this->sanitizeHeader($source, $global, $allowLocalUrls),
            'hero' => $this->sanitizeHero($source, $allowLocalUrls),
            'services' => $this->sanitizeServices($source, $allowLocalUrls),
            'projects' => $this->sanitizeProjects($source, $allowLocalUrls),
            'about' => $this->sanitizeAbout($source, $allowLocalUrls),
            'testimonials' => $this->sanitizeTestimonials($source, $allowLocalUrls),
            'contact_cta' => $this->sanitizeContactCta($source, $allowLocalUrls),
            'contact_form' => $this->sanitizeContactForm($source, $global),
            'footer' => $this->sanitizeFooter($source, $allowLocalUrls),
        };
    }

    private function sanitizeHeader(array $source, array $global, bool $allowLocalUrls): ?array
    {
        $header = [];
        $this->includeText($header, 'brand_label', $source['brand_label'] ?? null, 1, 100);

        if (is_bool($source['show_logo'] ?? null)) {
            $header['show_logo'] = $source['show_logo'];
        }

        $navigation = $this->sanitizeNavigation($source['navigation'] ?? null, $allowLocalUrls);

        if ($navigation !== []) {
            $header['navigation'] = $navigation;
        }

        $primaryCta = $this->sanitizeAction($source['primary_cta'] ?? null, $allowLocalUrls);

        if ($primaryCta !== null) {
            $header['primary_cta'] = $primaryCta;
        }

        $hasIdentity = $this->text(data_get($global, 'identity.display_name'), 2, 100) !== null;
        $hasLogo = ($header['show_logo'] ?? false) === true
            && isset($global['identity']['logo_url']);

        if (! isset($header['brand_label']) && ! $hasIdentity && ! $hasLogo) {
            return null;
        }

        return $header;
    }

    private function sanitizeHero(array $source, bool $allowLocalUrls): ?array
    {
        $headline = $this->text($source['headline'] ?? null, 10, 140);
        $primaryCta = $this->sanitizeAction($source['primary_cta'] ?? null, $allowLocalUrls);

        if ($headline === null || $primaryCta === null) {
            return null;
        }

        $hero = [
            'headline' => $headline,
            'primary_cta' => $primaryCta,
        ];
        $this->includeText($hero, 'eyebrow', $source['eyebrow'] ?? null, 1, 60);
        $this->includeText($hero, 'description', $source['description'] ?? null, 1, 400);
        $secondaryCta = $this->sanitizeAction($source['secondary_cta'] ?? null, $allowLocalUrls);

        if ($secondaryCta !== null) {
            $hero['secondary_cta'] = $secondaryCta;
        }

        $this->includeImageWithAlt(
            $hero,
            'photo_override_url',
            'photo_alt',
            $source,
            $allowLocalUrls,
        );

        return $hero;
    }

    private function sanitizeServices(array $source, bool $allowLocalUrls): ?array
    {
        $title = $this->text($source['title'] ?? null, 3, 100);
        $items = [];
        $sourceItems = $source['items'] ?? null;

        if (is_array($sourceItems) && array_is_list($sourceItems)) {
            foreach ($sourceItems as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $itemTitle = $this->text($item['title'] ?? null, 2, 100);
                $description = $this->text($item['description'] ?? null, 1, 350);
                $benefit = $this->text($item['benefit'] ?? null, 1, 180);

                if ($itemTitle === null || $description === null || $benefit === null) {
                    continue;
                }

                $service = [
                    'title' => $itemTitle,
                    'description' => $description,
                    'benefit' => $benefit,
                ];
                $cta = $this->sanitizeAction($item['cta'] ?? null, $allowLocalUrls);

                if ($cta !== null) {
                    $service['cta'] = $cta;
                }

                $items[] = $service;

                if (count($items) === 6) {
                    break;
                }
            }
        }

        if ($title === null || $items === []) {
            return null;
        }

        $services = ['title' => $title, 'items' => $items];
        $this->includeText($services, 'intro', $source['intro'] ?? null, 1, 300);

        return $services;
    }

    private function sanitizeProjects(array $source, bool $allowLocalUrls): ?array
    {
        $title = $this->text($source['title'] ?? null, 3, 100);
        $items = [];
        $sourceItems = $source['items'] ?? null;

        if (is_array($sourceItems) && array_is_list($sourceItems)) {
            foreach ($sourceItems as $item) {
                $project = is_array($item)
                    ? $this->sanitizeProject($item, $allowLocalUrls)
                    : null;

                if ($project !== null) {
                    $items[] = $project;
                }

                if (count($items) === 12) {
                    break;
                }
            }
        }

        if ($title === null || $items === []) {
            return null;
        }

        $projects = ['title' => $title, 'items' => $items];
        $this->includeText($projects, 'intro', $source['intro'] ?? null, 1, 300);

        return $projects;
    }

    private function sanitizeProject(array $source, bool $allowLocalUrls): ?array
    {
        $title = $this->text($source['title'] ?? null, 2, 120);
        $summary = $this->text($source['summary'] ?? null, 1, 400);

        if ($title === null || $summary === null) {
            return null;
        }

        $project = ['title' => $title, 'summary' => $summary];

        foreach ([
            'challenge' => 500,
            'solution' => 700,
            'result' => 500,
            'client_name' => 100,
        ] as $key => $maximum) {
            $this->includeText($project, $key, $source[$key] ?? null, 1, $maximum);
        }

        $this->includeImage(
            $project,
            'client_logo_url',
            $source['client_logo_url'] ?? null,
            $allowLocalUrls,
        );
        $this->includeImageWithAlt(
            $project,
            'image_url',
            'image_alt',
            $source,
            $allowLocalUrls,
        );
        $tags = $this->sanitizeTextList($source['tags'] ?? null, 8);

        if ($tags !== []) {
            $project['tags'] = $tags;
        }

        $projectUrl = $this->absoluteUrl($source['project_url'] ?? null, ['https'], $allowLocalUrls);

        if ($projectUrl !== null) {
            $project['project_url'] = $projectUrl;
        }

        return $project;
    }

    private function sanitizeAbout(array $source, bool $allowLocalUrls): ?array
    {
        $title = $this->text($source['title'] ?? null, 2, 100);
        $summary = $this->text($source['summary'] ?? null, 1, 500);

        if ($title === null || $summary === null) {
            return null;
        }

        $about = ['title' => $title, 'summary' => $summary];
        $this->includeText($about, 'details', $source['details'] ?? null, 1, 1500);
        $expertise = $this->sanitizeTextList($source['expertise'] ?? null, 12, 60);
        $highlights = $this->sanitizeTextList($source['highlights'] ?? null, 8, 120);

        if ($expertise !== []) {
            $about['expertise'] = $expertise;
        }

        if ($highlights !== []) {
            $about['highlights'] = $highlights;
        }

        $this->includeImageWithAlt(
            $about,
            'photo_override_url',
            'photo_alt',
            $source,
            $allowLocalUrls,
        );

        return $about;
    }

    private function sanitizeTestimonials(array $source, bool $allowLocalUrls): ?array
    {
        $items = [];
        $sourceItems = $source['items'] ?? null;

        if (is_array($sourceItems) && array_is_list($sourceItems)) {
            foreach ($sourceItems as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $quote = $this->text($item['quote'] ?? null, 10, 800);
                $authorName = $this->text($item['author_name'] ?? null, 1, 100);

                if ($quote === null || $authorName === null) {
                    continue;
                }

                $testimonial = ['quote' => $quote, 'author_name' => $authorName];

                foreach (['author_role', 'organization'] as $key) {
                    $this->includeText($testimonial, $key, $item[$key] ?? null, 1, 100);
                }

                foreach (['photo_url', 'company_logo_url'] as $key) {
                    $this->includeImage($testimonial, $key, $item[$key] ?? null, $allowLocalUrls);
                }

                $sourceUrl = $this->absoluteUrl(
                    $item['source_url'] ?? null,
                    ['https'],
                    $allowLocalUrls,
                );

                if ($sourceUrl !== null) {
                    $testimonial['source_url'] = $sourceUrl;
                }

                $items[] = $testimonial;

                if (count($items) === 12) {
                    break;
                }
            }
        }

        if ($items === []) {
            return null;
        }

        $testimonials = ['items' => $items];
        $this->includeText($testimonials, 'title', $source['title'] ?? null, 1, 100);
        $this->includeText($testimonials, 'intro', $source['intro'] ?? null, 1, 300);

        return $testimonials;
    }

    private function sanitizeContactCta(array $source, bool $allowLocalUrls): ?array
    {
        $title = $this->text($source['title'] ?? null, 5, 140);
        $primaryAction = $this->sanitizeAction($source['primary_action'] ?? null, $allowLocalUrls);

        if ($title === null || $primaryAction === null) {
            return null;
        }

        $contactCta = ['title' => $title, 'primary_action' => $primaryAction];
        $this->includeText($contactCta, 'description', $source['description'] ?? null, 1, 350);
        $secondaryAction = $this->sanitizeAction(
            $source['secondary_action'] ?? null,
            $allowLocalUrls,
        );

        if ($secondaryAction !== null) {
            $contactCta['secondary_action'] = $secondaryAction;
        }

        return $contactCta;
    }

    private function sanitizeContactForm(array $source, array $global): ?array
    {
        $title = $this->text($source['title'] ?? null, 3, 100);
        $submitLabel = $this->text($source['submit_label'] ?? null, 1, 50);
        $successMessage = $this->text($source['success_message'] ?? null, 1, 200);
        $consentRequired = $source['consent_required'] ?? null;
        $fields = $this->sanitizeFormFields($source['fields'] ?? null);

        if ($title === null
            || $submitLabel === null
            || $successMessage === null
            || ! is_bool($consentRequired)
            || count($fields) < 2
            || ! in_array('textarea', array_column($fields, 'type'), true)
            || array_intersect(['email', 'tel'], array_column($fields, 'type')) === []
            || ! isset($global['legal']['privacy_policy_url'])
            || ($consentRequired && ! isset($global['legal']['form_consent_text']))) {
            return null;
        }

        $contactForm = [
            'title' => $title,
            'fields' => $fields,
            'submit_label' => $submitLabel,
            'success_message' => $successMessage,
            'consent_required' => $consentRequired,
        ];

        foreach ([
            'intro' => 300,
            'submitting_message' => 150,
            'error_message' => 200,
        ] as $key => $maximum) {
            $this->includeText($contactForm, $key, $source[$key] ?? null, 1, $maximum);
        }

        return $contactForm;
    }

    private function sanitizeFormFields(mixed $source): array
    {
        if (! is_array($source) || ! array_is_list($source)) {
            return [];
        }

        $fields = [];

        foreach ($source as $item) {
            if (! is_array($item)) {
                continue;
            }

            $key = is_string($item['key'] ?? null)
                && preg_match('/^[a-z_]+$/', $item['key']) === 1
                    ? $item['key']
                    : null;
            $type = in_array(
                $item['type'] ?? null,
                ['text', 'email', 'tel', 'textarea', 'select'],
                true,
            ) ? $item['type'] : null;
            $label = $this->text($item['label'] ?? null, 1, 80);
            $required = $item['required'] ?? null;

            if ($key === null || $type === null || $label === null || ! is_bool($required)) {
                continue;
            }

            $field = compact('key', 'type', 'label', 'required');
            $this->includeText($field, 'placeholder', $item['placeholder'] ?? null, 1, 120);

            if (is_int($item['max_length'] ?? null)
                && $item['max_length'] >= 1
                && $item['max_length'] <= 5000) {
                $field['max_length'] = $item['max_length'];
            }

            if ($type === 'select') {
                $options = $this->sanitizeTextList($item['options'] ?? null);

                if ($options === []) {
                    continue;
                }

                $field['options'] = $options;
            }

            $fields[] = $field;
        }

        $keyCounts = array_count_values(array_column($fields, 'key'));
        $fields = array_values(array_filter(
            $fields,
            fn (array $field): bool => $keyCounts[$field['key']] === 1,
        ));

        return array_slice($fields, 0, 10);
    }

    private function sanitizeFooter(array $source, bool $allowLocalUrls): array
    {
        $footer = [];
        $this->includeText($footer, 'text', $source['text'] ?? null, 1, 200);

        foreach ([
            'show_identity',
            'show_navigation',
            'show_socials',
            'show_contact',
            'show_legal_links',
        ] as $key) {
            if (is_bool($source[$key] ?? null)) {
                $footer[$key] = $source[$key];
            }
        }

        $navigation = $this->sanitizeNavigation($source['navigation'] ?? null, $allowLocalUrls);

        if ($navigation !== []) {
            $footer['navigation'] = $navigation;
        }

        $this->includeText(
            $footer,
            'copyright_text',
            $source['copyright_text'] ?? null,
            1,
            150,
        );

        return $footer;
    }

    private function sanitizeNavigation(mixed $source, bool $allowLocalUrls): array
    {
        if (! is_array($source) || ! array_is_list($source)) {
            return [];
        }

        $navigation = [];

        foreach ($source as $item) {
            if (! is_array($item)) {
                continue;
            }

            $label = $this->text($item['label'] ?? null, 1, 40);
            $target = $this->navigationTarget($item['target'] ?? null, $allowLocalUrls);

            if ($label !== null && $target !== null) {
                $navigation[] = compact('label', 'target');
            }

            if (count($navigation) === 8) {
                break;
            }
        }

        return $navigation;
    }

    private function sanitizeAction(mixed $source, bool $allowLocalUrls): ?array
    {
        if (! is_array($source)) {
            return null;
        }

        $label = $this->text($source['label'] ?? null, 1, 50);
        $target = $this->actionTarget($source['target'] ?? null, $allowLocalUrls);

        return $label !== null && $target !== null ? compact('label', 'target') : null;
    }

    private function stabilizeComponents(array $components, array $global): array
    {
        do {
            $before = array_keys($components);
            $components = $this->removeInvalidRequiredAnchors($components);
            $components = $this->filterOptionalAnchors($components);

            if (isset($components['footer'])
                && ! $this->footerHasDisplayableContent($components['footer'], $global, $components)) {
                unset($components['footer']);
            }
        } while ($before !== array_keys($components));

        if (isset($components['footer'])
            && ($components['footer']['show_navigation'] ?? false) === true
            && ! isset($components['footer']['navigation'])
            && isset($components['header']['navigation'])) {
            $components['footer']['navigation'] = $components['header']['navigation'];
        }

        return $components;
    }

    private function removeInvalidRequiredAnchors(array $components): array
    {
        do {
            $invalid = [];

            foreach ($components as $type => $component) {
                $action = match ($type) {
                    'hero' => $component['primary_cta'],
                    'contact_cta' => $component['primary_action'],
                    default => null,
                };
                $dependency = is_array($action)
                    ? self::ANCHORS[$action['target']] ?? null
                    : null;

                if ($dependency !== null && ! isset($components[$dependency])) {
                    $invalid[] = $type;
                }
            }

            foreach ($invalid as $type) {
                unset($components[$type]);
            }
        } while ($invalid !== []);

        return $components;
    }

    private function filterOptionalAnchors(array $components): array
    {
        foreach ($components as $type => &$component) {
            if ($type === 'header') {
                $this->filterNavigationAnchors($component, 'navigation', $components);
                $this->filterActionAnchor($component, 'primary_cta', $components);
            } elseif ($type === 'hero') {
                $this->filterActionAnchor($component, 'secondary_cta', $components);
            } elseif ($type === 'services') {
                foreach ($component['items'] as &$item) {
                    $this->filterActionAnchor($item, 'cta', $components);
                }
                unset($item);
            } elseif ($type === 'contact_cta') {
                $this->filterActionAnchor($component, 'secondary_action', $components);
            } elseif ($type === 'footer') {
                $this->filterNavigationAnchors($component, 'navigation', $components);
            }
        }
        unset($component);

        return $components;
    }

    private function filterNavigationAnchors(array &$source, string $key, array $components): void
    {
        if (! isset($source[$key])) {
            return;
        }

        $source[$key] = array_values(array_filter(
            $source[$key],
            fn (array $item): bool => ! isset(self::ANCHORS[$item['target']])
                || isset($components[self::ANCHORS[$item['target']]]),
        ));

        if ($source[$key] === []) {
            unset($source[$key]);
        }
    }

    private function filterActionAnchor(array &$source, string $key, array $components): void
    {
        if (isset($source[$key])
            && isset(self::ANCHORS[$source[$key]['target']])
            && ! isset($components[self::ANCHORS[$source[$key]['target']]])) {
            unset($source[$key]);
        }
    }

    private function footerHasDisplayableContent(array $footer, array $global, array $components): bool
    {
        if (isset($footer['text']) || isset($footer['copyright_text'])) {
            return true;
        }

        if (($footer['show_identity'] ?? false) === true && isset($global['identity']['display_name'])) {
            return true;
        }

        if (($footer['show_navigation'] ?? false) === true
            && (isset($footer['navigation']) || isset($components['header']['navigation']))) {
            return true;
        }

        if (($footer['show_socials'] ?? false) === true && ($global['socials'] ?? []) !== []) {
            return true;
        }

        if (($footer['show_contact'] ?? false) === true && isset($global['contact'])) {
            return true;
        }

        if (($footer['show_legal_links'] ?? false) === true
            && (isset($global['legal']['legal_notice_url'])
                || isset($global['legal']['privacy_policy_url']))) {
            return true;
        }

        return isset($global['legal']['copyright_year'], $global['legal']['copyright_owner']);
    }

    private function navigationTarget(mixed $target, bool $allowLocalUrls): ?string
    {
        if (! $this->isSafeTargetString($target)) {
            return null;
        }

        if (isset(self::ANCHORS[$target]) || $this->isRootRelativePath($target)) {
            return $target;
        }

        return $this->absoluteUrl($target, ['https'], $allowLocalUrls);
    }

    private function actionTarget(mixed $target, bool $allowLocalUrls): ?string
    {
        if (! $this->isSafeTargetString($target)) {
            return null;
        }

        if (isset(self::ANCHORS[$target]) || $this->isRootRelativePath($target)) {
            return $target;
        }

        $https = $this->absoluteUrl($target, ['https'], $allowLocalUrls);

        if ($https !== null || $this->isMailto($target) || $this->isTel($target)) {
            return $target;
        }

        return null;
    }

    private function absoluteUrl(mixed $value, array $schemes, bool $allowLocalUrls): ?string
    {
        if (! $this->isSafeTargetString($value)
            || filter_var($value, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parts = parse_url($value);

        if (! is_array($parts)
            || ! in_array($parts['scheme'] ?? null, $schemes, true)
            || ! isset($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $unwrappedHost = str_starts_with($host, '[') && str_ends_with($host, ']')
            ? substr($host, 1, -1)
            : $host;

        if (! $allowLocalUrls
            && (isset($parts['port'])
                || $host === 'localhost'
                || filter_var($unwrappedHost, FILTER_VALIDATE_IP) !== false)) {
            return null;
        }

        return $value;
    }

    private function isRootRelativePath(string $value): bool
    {
        if (! str_starts_with($value, '/') || str_starts_with($value, '//')) {
            return false;
        }

        $path = parse_url($value, PHP_URL_PATH);

        return is_string($path) && ! in_array('..', explode('/', $path), true);
    }

    private function isMailto(string $value): bool
    {
        if (! str_starts_with($value, 'mailto:')
            || str_contains($value, '?')
            || str_contains($value, '#')) {
            return false;
        }

        $email = substr($value, 7);

        return ! str_contains($email, ',')
            && ! str_contains($email, ';')
            && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isTel(string $value): bool
    {
        return str_starts_with($value, 'tel:')
            && preg_match('/^\+?[0-9]{6,20}$/', substr($value, 4)) === 1;
    }

    private function isSafeTargetString(mixed $value): bool
    {
        return is_string($value)
            && $value !== ''
            && preg_match('/[\s\x00-\x1F\x7F]/', $value) !== 1;
    }

    private function includeImage(
        array &$target,
        string $key,
        mixed $value,
        bool $allowLocalUrls,
    ): void {
        if ($this->isImage($value, $allowLocalUrls)) {
            $target[$key] = $value;
        }
    }

    private function includeImageWithAlt(
        array &$target,
        string $imageKey,
        string $altKey,
        array $source,
        bool $allowLocalUrls,
    ): void {
        $alt = $this->text($source[$altKey] ?? null);

        if ($alt !== null && $this->isImage($source[$imageKey] ?? null, $allowLocalUrls)) {
            $target[$imageKey] = $source[$imageKey];
            $target[$altKey] = $alt;
        }
    }

    private function isImage(mixed $value, bool $allowLocalUrls): bool
    {
        if (! $this->isSafeTargetString($value)) {
            return false;
        }

        if ($this->absoluteUrl($value, ['https'], $allowLocalUrls) !== null) {
            $path = parse_url($value, PHP_URL_PATH);

            return is_string($path) && $this->hasAllowedOrNoImageExtension($path);
        }

        if (! $this->isRootRelativePath($value)) {
            return false;
        }

        $path = parse_url($value, PHP_URL_PATH);

        return is_string($path) && $this->hasAllowedImageExtension($path);
    }

    private function hasAllowedOrNoImageExtension(string $path): bool
    {
        $extension = $this->imageExtension($path);

        return $extension === null || in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private function hasAllowedImageExtension(string $path): bool
    {
        $extension = $this->imageExtension($path);

        return $extension !== null && in_array($extension, self::IMAGE_EXTENSIONS, true);
    }

    private function imageExtension(string $path): ?string
    {
        $basename = basename($path);

        if (! str_contains($basename, '.')) {
            return null;
        }

        return strtolower(pathinfo($basename, PATHINFO_EXTENSION));
    }

    private function sanitizeTextList(mixed $source, ?int $limit = null, ?int $maximum = null): array
    {
        if (! is_array($source) || ! array_is_list($source)) {
            return [];
        }

        $items = [];

        foreach ($source as $item) {
            $text = $this->text($item, 1, $maximum);

            if ($text !== null) {
                $items[] = $text;
            }

            if ($limit !== null && count($items) === $limit) {
                break;
            }
        }

        return $items;
    }

    private function includeText(
        array &$target,
        string $key,
        mixed $value,
        int $minimum = 1,
        ?int $maximum = null,
    ): void {
        $text = $this->text($value, $minimum, $maximum);

        if ($text !== null) {
            $target[$key] = $text;
        }
    }

    private function text(mixed $value, int $minimum = 1, ?int $maximum = null): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $length = mb_strlen($value);

        if ($length < $minimum || ($maximum !== null && $length > $maximum)) {
            return null;
        }

        return $value;
    }

    private function isColor(mixed $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value) === 1;
    }
}
