// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import HeroSection from './HeroSection.vue';

function heroComponent() {
    return {
        type: 'hero',
        eyebrow: 'Example professional title',
        headline: 'Example headline for a professional portfolio',
    };
}

describe('HeroSection', () => {
    test('renders validated required content without empty optional areas', () => {
        const wrapper = mount(HeroSection, {
            props: {
                component: heroComponent(),
            },
        });

        expect(wrapper.get('p').text()).toBe('Example professional title');
        expect(wrapper.get('h1').text()).toBe('Example headline for a professional portfolio');
        expect(wrapper.find('[data-testid="hero-actions"]').exists()).toBe(false);
        expect(wrapper.find('[data-testid="hero-media"]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    test('renders only the configured accessible CTA links', () => {
        const wrapper = mount(HeroSection, {
            props: {
                component: {
                    ...heroComponent(),
                    primary_cta: {
                        label: 'Contact',
                        target: '#contact-form',
                    },
                    secondary_cta: {
                        label: 'Projects',
                        target: '#projects',
                    },
                },
            },
        });

        expect(wrapper.get('a[href="#contact-form"]').text()).toBe('Contact');
        expect(wrapper.get('a[href="#projects"]').text()).toBe('Projects');
        expect(wrapper.findAll('[data-testid="hero-actions"] a')).toHaveLength(2);
    });

    test('uses the global photo without creating a replacement slot', () => {
        const wrapper = mount(HeroSection, {
            props: {
                component: heroComponent(),
                global: {
                    identity: {
                        display_name: 'Example Profile',
                        profile_photo_url: 'https://example.test/profile.webp',
                    },
                },
            },
        });

        expect(wrapper.get('[data-testid="hero-media"] img').attributes('src'))
            .toBe('https://example.test/profile.webp');
        expect(wrapper.get('[data-testid="hero-media"] img').attributes('alt'))
            .toBe('Example Profile');
    });

    test('gives a valid configured replacement priority over the global photo', () => {
        const wrapper = mount(HeroSection, {
            props: {
                component: {
                    ...heroComponent(),
                    photo_override_url: 'https://example.test/hero.jpg',
                    photo_alt: 'Example replacement photo',
                },
                global: {
                    identity: {
                        display_name: 'Example Profile',
                        profile_photo_url: 'https://example.test/profile.webp',
                    },
                },
            },
        });

        expect(wrapper.get('[data-testid="hero-media"] img').attributes('src'))
            .toBe('https://example.test/hero.jpg');
        expect(wrapper.get('[data-testid="hero-media"] img').attributes('alt'))
            .toBe('Example replacement photo');
    });
});
