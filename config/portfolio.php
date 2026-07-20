<?php

return [
    'schema_version' => '1.0',

    'global' => [
        'identity' => [
            'display_name' => null,
            'professional_title' => null,
            'tagline' => null,
            'logo_url' => null,
            'profile_photo_url' => null,
            'default_language' => 'fr',
        ],

        'appearance' => [
            'primary_color' => null,
            'accent_color' => null,
            'background_color' => null,
            'text_color' => null,
            'font_family' => null,
            'color_mode' => null,
        ],

        'seo' => [
            'site_url' => null,
            'meta_title' => null,
            'meta_description' => null,
            'canonical_url' => null,
            'social_image_url' => null,
            'indexing_enabled' => false,
        ],

        'socials' => [],

        'contact' => [
            'email' => null,
            'phone' => null,
            'whatsapp_url' => null,
            'booking_url' => null,
            'location_text' => null,
        ],

        'legal' => [
            'publisher_name' => null,
            'registration_number' => null,
            'legal_notice_url' => null,
            'privacy_policy_url' => null,
            'form_consent_text' => null,
            'copyright_year' => null,
            'copyright_owner' => null,
        ],
    ],

    'components' => [
        'header' => [
            'active' => true,
            'order' => 10,
            'brand_label' => 'Mohamed M’Kirchel',
            'show_logo' => false,
            'navigation' => [
                [
                    'label' => 'Services',
                    'target' => '#services',
                ],
                [
                    'label' => 'Projets',
                    'target' => '#projects',
                ],
                [
                    'label' => 'À propos',
                    'target' => '#about',
                ],
                [
                    'label' => 'Contact',
                    'target' => '#contact-form',
                ],
            ],
            'primary_cta' => [
                'label' => 'Me contacter',
                'target' => '#contact-form',
            ],
        ],

        'hero' => [
            'active' => true,
            'order' => 20,
            'eyebrow' => 'PHP/Laravel/Vue.js — SQL Server, API, automatisation',
            'headline' => 'Transformer l’expertise et le réseau d’un professionnel en opportunités commerciales qualifiées.',
            'description' => null,
            'primary_cta' => [
                'label' => 'Me contacter',
                'target' => '#contact-form',
            ],
            'secondary_cta' => [
                'label' => 'Voir mes projets',
                'target' => '#projects',
            ],
            'photo_override_url' => null,
            'photo_alt' => null,
        ],

        'services' => [
            'active' => false,
            'order' => 30,
            'title' => null,
            'intro' => null,
            'items' => [],
        ],

        'projects' => [
            'active' => false,
            'order' => 40,
            'title' => null,
            'intro' => null,
            'items' => [],
        ],

        'about' => [
            'active' => false,
            'order' => 50,
            'title' => null,
            'summary' => null,
            'details' => null,
            'expertise' => [],
            'highlights' => [],
            'photo_override_url' => null,
            'photo_alt' => null,
        ],

        'testimonials' => [
            'active' => false,
            'order' => 60,
            'title' => null,
            'intro' => null,
            'items' => [],
        ],

        'contact_cta' => [
            'active' => false,
            'order' => 70,
            'title' => null,
            'description' => null,
            'primary_action' => null,
            'secondary_action' => null,
        ],

        'contact_form' => [
            'active' => false,
            'order' => 80,
            'title' => null,
            'intro' => null,
            'fields' => [],
            'submit_label' => null,
            'submitting_message' => null,
            'success_message' => null,
            'error_message' => null,
            'consent_required' => false,
        ],

        'footer' => [
            'active' => false,
            'order' => 90,
            'text' => null,
            'show_identity' => false,
            'show_navigation' => false,
            'navigation' => [],
            'show_socials' => false,
            'show_contact' => false,
            'show_legal_links' => false,
            'copyright_text' => null,
        ],
    ],
];
