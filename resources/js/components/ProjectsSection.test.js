// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { describe, expect, test } from 'vitest';
import ProjectsSection from './ProjectsSection.vue';

function projectsComponent() {
    return {
        type: 'projects',
        title: 'Projets',
        items: [{
            title: 'Portfolio OS',
            summary: 'Projet de moteur configurable et réutilisable pour créer des portfolios professionnels d’une page, dont le portfolio de Mohamed constitue la première instance.',
            tags: ['Laravel', 'Vue.js', 'Inertia', 'Tailwind CSS'],
        }],
    };
}

describe('ProjectsSection', () => {
    test('renders the exact project and its four validated tags', () => {
        const wrapper = mount(ProjectsSection, {
            props: {
                component: projectsComponent(),
            },
        });

        expect(wrapper.get('section').attributes('id')).toBe('projects');
        expect(wrapper.get('h2').text()).toBe('Projets');
        expect(wrapper.findAll('[data-testid="project-item"]')).toHaveLength(1);
        expect(wrapper.get('h3').text()).toBe('Portfolio OS');
        expect(wrapper.get('[data-testid="project-item"] > div > p').text())
            .toBe('Projet de moteur configurable et réutilisable pour créer des portfolios professionnels d’une page, dont le portfolio de Mohamed constitue la première instance.');
        expect(wrapper.get('[data-testid="project-tags"]').attributes('aria-label'))
            .toBe('Technologies');
        expect(wrapper.get('[data-testid="project-tags"]').findAll('li').map((tag) => tag.text()))
            .toEqual(['Laravel', 'Vue.js', 'Inertia', 'Tailwind CSS']);
    });

    test('does not render absent client, result, media, date, or project link areas', () => {
        const wrapper = mount(ProjectsSection, {
            props: {
                component: projectsComponent(),
            },
        });

        expect(wrapper.find('[data-testid="project-client"]').exists()).toBe(false);
        expect(wrapper.find('img').exists()).toBe(false);
        expect(wrapper.find('time').exists()).toBe(false);
        expect(wrapper.find('a').exists()).toBe(false);
        expect(wrapper.findAll('[data-testid="project-item"] p')).toHaveLength(1);
    });

    test('uses the validated responsive grid boundary', () => {
        const wrapper = mount(ProjectsSection, {
            props: {
                component: projectsComponent(),
            },
        });

        expect(wrapper.get('[data-testid="project-item"]').element.parentElement.classList)
            .toContain('min-[768px]:grid-cols-2');
    });
});
