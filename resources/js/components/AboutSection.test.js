// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import AboutSection from './AboutSection.vue';

function aboutComponent() {
    return {
        type: 'about',
        title: 'À propos',
        summary: 'Développeur PHP, Laravel et Vue.js, avec une expérience en SQL Server, API et automatisation, je conçois des solutions web et de données adaptées aux besoins métier.',
        details: 'Mon parcours réunit développement web, bases de données et automatisation. Je construis également Portfolio OS, un moteur configurable et réutilisable destiné à créer des portfolios professionnels d’une page, dont ce site constitue la première instance.',
        expertise: ['PHP', 'Laravel', 'Vue.js', 'SQL Server', 'API', 'Automatisation'],
    };
}

describe('AboutSection', () => {
    test('renders the exact validated About content and six expertises', () => {
        const wrapper = mount(AboutSection, {
            props: { component: aboutComponent() },
        });

        expect(wrapper.get('section').attributes('id')).toBe('about');
        expect(wrapper.get('h2').text()).toBe('À propos');
        expect(wrapper.findAll('p').map((paragraph) => paragraph.text())).toEqual([
            aboutComponent().summary,
            aboutComponent().details,
        ]);
        expect(wrapper.get('[data-testid="about-expertise"]').attributes('aria-label'))
            .toBe('Expertises');
        expect(wrapper.get('[data-testid="about-expertise"]').findAll('li').map((item) => item.text()))
            .toEqual(['PHP', 'Laravel', 'Vue.js', 'SQL Server', 'API', 'Automatisation']);
    });

    test('does not render absent photo, highlights, or placeholders', () => {
        const wrapper = mount(AboutSection, {
            props: { component: aboutComponent() },
        });

        expect(wrapper.find('[data-testid="about-photo"]').exists()).toBe(false);
        expect(wrapper.find('[data-testid="about-highlights"]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.text()).not.toContain('placeholder');
    });

    test('uses the validated global profile photo only when it is available', () => {
        const wrapper = mount(AboutSection, {
            props: {
                component: aboutComponent(),
                global: {
                    identity: {
                        display_name: 'Personne Exemple',
                        profile_photo_url: '/images/profile.webp',
                    },
                },
            },
        });

        expect(wrapper.get('[data-testid="about-photo"]').attributes()).toMatchObject({
            src: '/images/profile.webp',
            alt: 'Personne Exemple',
        });
    });
});
