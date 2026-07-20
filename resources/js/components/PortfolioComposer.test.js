// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import PortfolioComposer from './PortfolioComposer.vue';

describe('PortfolioComposer', () => {
    test('renders the nine allowed component types and ignores an unknown type', () => {
        const allowedTypes = [
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
        const wrapper = mount(PortfolioComposer, {
            props: {
                components: [
                    ...allowedTypes.map((type) => ({ type })),
                    { type: 'unknown_component' },
                ],
            },
        });
        const renderedTypes = wrapper
            .findAll('[data-portfolio-component]')
            .map((component) => component.attributes('data-portfolio-component'));

        expect(renderedTypes).toEqual(allowedTypes);
        expect(wrapper.find('[data-portfolio-component="unknown_component"]').exists()).toBe(false);
    });

    test('renders the global Header outside the single main content landmark', () => {
        const contentTypes = [
            'hero',
            'services',
            'projects',
            'about',
            'testimonials',
            'contact_cta',
            'contact_form',
        ];
        const wrapper = mount(PortfolioComposer, {
            props: {
                components: [
                    { type: 'header', brand_label: 'Example Profile' },
                    ...contentTypes.map((type) => ({ type })),
                ],
            },
        });
        const composer = wrapper.get('[data-testid="portfolio-composer"]');
        const header = composer.get('[data-portfolio-component="header"]');
        const main = composer.get('main');

        expect(composer.findAll('main')).toHaveLength(1);
        expect(main.element.contains(header.element)).toBe(false);
        expect(composer.element.firstElementChild).toBe(header.element);

        contentTypes.forEach((type) => {
            expect(main.find(`[data-portfolio-component="${type}"]`).exists()).toBe(true);
        });
    });
});
