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
});
