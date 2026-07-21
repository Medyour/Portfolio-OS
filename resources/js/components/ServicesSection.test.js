// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import ServicesSection from './ServicesSection.vue';

function servicesComponent() {
    return {
        type: 'services',
        title: 'Services',
        items: [{
            title: 'Site de crédibilité B2B',
            description: 'Création d’un site professionnel d’une page, adapté aux mobiles, présentant clairement votre promesse, vos services, vos preuves, votre présentation et un moyen de contact.',
            benefit: 'Aider vos prospects LinkedIn et vos recommandations à comprendre rapidement votre expertise et à passer plus facilement à la prise de contact.',
        }],
    };
}

describe('ServicesSection', () => {
    test('renders the exact validated service without an empty intro or CTA', () => {
        const wrapper = mount(ServicesSection, {
            props: {
                component: servicesComponent(),
            },
        });

        expect(wrapper.get('section').attributes('id')).toBe('services');
        expect(wrapper.get('h2').text()).toBe('Services');
        expect(wrapper.findAll('[data-testid="service-item"]')).toHaveLength(1);
        expect(wrapper.get('h3').text()).toBe('Site de crédibilité B2B');
        expect(wrapper.findAll('p').map((paragraph) => paragraph.text())).toEqual([
            'Création d’un site professionnel d’une page, adapté aux mobiles, présentant clairement votre promesse, vos services, vos preuves, votre présentation et un moyen de contact.',
            'Aider vos prospects LinkedIn et vos recommandations à comprendre rapidement votre expertise et à passer plus facilement à la prise de contact.',
        ]);
        expect(wrapper.find('a').exists()).toBe(false);
        expect(wrapper.find('button').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
    });

    test('uses the validated responsive grid boundary', () => {
        const wrapper = mount(ServicesSection, {
            props: {
                component: servicesComponent(),
            },
        });

        expect(wrapper.get('[data-testid="service-item"]').element.parentElement.classList)
            .toContain('min-[768px]:grid-cols-2');
    });
});
