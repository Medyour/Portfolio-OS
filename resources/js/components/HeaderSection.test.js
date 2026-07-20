// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import HeaderSection from './HeaderSection.vue';

function headerComponent() {
    return {
        type: 'header',
        brand_label: 'Example Profile',
        show_logo: false,
        navigation: [
            { label: 'Projects', target: '#projects' },
            { label: 'About', target: '#about' },
        ],
        primary_cta: {
            label: 'Contact',
            target: '#contact-form',
        },
    };
}

describe('HeaderSection', () => {
    test('exposes the validated mobile menu labels and expanded state', async () => {
        const wrapper = mount(HeaderSection, {
            props: {
                component: headerComponent(),
            },
        });
        const button = wrapper.get('[data-testid="mobile-menu-button"]');

        expect(button.attributes('type')).toBe('button');
        expect(button.attributes('aria-controls')).toBe('portfolio-mobile-navigation');
        expect(button.attributes('aria-expanded')).toBe('false');
        expect(button.attributes('aria-label')).toBe('Ouvrir le menu de navigation');
        expect(wrapper.find('[data-testid="mobile-navigation"]').exists()).toBe(false);

        await button.trigger('click');

        expect(button.attributes('aria-expanded')).toBe('true');
        expect(button.attributes('aria-label')).toBe('Fermer le menu de navigation');
        expect(wrapper.get('[data-testid="header-identity"]').isVisible()).toBe(true);
        expect(wrapper.get('[data-testid="header-identity"]').text()).toBe('Example Profile');
        expect(wrapper.get('[data-testid="mobile-navigation"]').attributes('id'))
            .toBe('portfolio-mobile-navigation');

        await wrapper.get('[data-testid="mobile-navigation"] a[href="#projects"]').trigger('click');

        expect(button.attributes('aria-expanded')).toBe('false');
        expect(button.attributes('aria-label')).toBe('Ouvrir le menu de navigation');
        expect(wrapper.find('[data-testid="mobile-navigation"]').exists()).toBe(false);
    });

    test('uses the exact 768 pixel responsive boundary and configured targets', async () => {
        const wrapper = mount(HeaderSection, {
            props: {
                component: headerComponent(),
            },
        });

        expect(wrapper.get('[data-testid="desktop-navigation"]').classes())
            .toContain('min-[768px]:flex');
        expect(wrapper.get('[data-testid="mobile-menu-button"]').classes())
            .toContain('min-[768px]:hidden');

        await wrapper.get('[data-testid="mobile-menu-button"]').trigger('click');

        const mobileNavigation = wrapper.get('[data-testid="mobile-navigation"]');

        expect(mobileNavigation.classes()).toContain('min-[768px]:hidden');
        expect(mobileNavigation.get('a[href="#projects"]').text()).toBe('Projects');
        expect(mobileNavigation.get('a[href="#about"]').text()).toBe('About');
        expect(mobileNavigation.get('a[href="#contact-form"]').text()).toBe('Contact');
    });

    test('does not render a menu, CTA, or empty logo when optional data is absent', () => {
        const wrapper = mount(HeaderSection, {
            props: {
                component: {
                    type: 'header',
                    brand_label: 'Example Profile',
                    show_logo: false,
                },
            },
        });

        expect(wrapper.text()).toContain('Example Profile');
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.find('nav').exists()).toBe(false);
        expect(wrapper.find('a').exists()).toBe(false);
        expect(wrapper.find('[data-testid="mobile-menu-button"]').exists()).toBe(false);
    });

    test('renders the configured global logo with an accessible alternative', () => {
        const wrapper = mount(HeaderSection, {
            props: {
                component: {
                    type: 'header',
                    show_logo: true,
                },
                global: {
                    identity: {
                        display_name: 'Example Profile',
                        logo_url: 'https://example.test/logo.svg',
                    },
                },
            },
        });

        expect(wrapper.get('img').attributes('src')).toBe('https://example.test/logo.svg');
        expect(wrapper.get('img').attributes('alt')).toBe('Example Profile');
    });
});
