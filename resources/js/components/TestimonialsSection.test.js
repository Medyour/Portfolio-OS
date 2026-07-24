// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import TestimonialsSection from './TestimonialsSection.vue';

describe('TestimonialsSection', () => {
    test('renders a valid testimonial fixture with all contractual optional fields', () => {
        const wrapper = mount(TestimonialsSection, {
            props: {
                component: {
                    type: 'testimonials',
                    title: 'Témoignages',
                    intro: 'Des retours publiables.',
                    items: [{
                        quote: 'Une collaboration précise, fiable et très professionnelle.',
                        author_name: 'Personne Exemple',
                        author_role: 'Consultante',
                        organization: 'Entreprise Exemple',
                        photo_url: '/images/personne-exemple.webp',
                        company_logo_url: '/images/entreprise-exemple.svg',
                        source_url: 'https://example.test/temoignage',
                    }],
                },
            },
        });

        expect(wrapper.get('h2').text()).toBe('Témoignages');
        expect(wrapper.get('[data-testid="testimonial-item"] blockquote').text())
            .toBe('“Une collaboration précise, fiable et très professionnelle.”');
        expect(wrapper.get('figcaption p').text()).toBe('Personne Exemple');
        expect(wrapper.get('[data-testid="testimonial-attribution"]').text())
            .toBe('Consultante — Entreprise Exemple');
        expect(wrapper.findAll('img')).toHaveLength(2);
        expect(wrapper.get('a').attributes('href')).toBe('https://example.test/temoignage');
    });

    test('does not create optional placeholders when only required fields are present', () => {
        const wrapper = mount(TestimonialsSection, {
            props: {
                component: {
                    type: 'testimonials',
                    items: [{
                        quote: 'Un témoignage réel suffisamment long.',
                        author_name: 'Personne Exemple',
                    }],
                },
            },
        });

        expect(wrapper.find('h2').exists()).toBe(false);
        expect(wrapper.find('[data-testid="testimonial-attribution"]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.find('a').exists()).toBe(false);
    });
});
