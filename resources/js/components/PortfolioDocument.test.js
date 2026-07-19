// @vitest-environment jsdom

import { mount } from '@vue/test-utils';
import { defineComponent, h } from 'vue';
import { beforeEach, describe, expect, test, vi } from 'vitest';

vi.mock('@inertiajs/vue3', () => ({
    Head: defineComponent({
        props: {
            title: String,
        },
        setup(props, { slots }) {
            return () => h('div', { 'data-head-title': props.title }, slots.default?.());
        },
    }),
}));

import PortfolioDocument from './PortfolioDocument.vue';

function globalConfiguration(indexingEnabled = true) {
    return {
        identity: {
            display_name: 'Example Profile',
            professional_title: 'Example Professional Title',
            default_language: 'en',
            profile_photo_url: 'https://example.test/profile.webp',
        },
        seo: {
            site_url: 'https://example.test',
            meta_title: 'Example Portfolio Title',
            meta_description: 'A neutral description used exclusively by the automated tests for Portfolio OS.',
            canonical_url: 'https://example.test/profile',
            social_image_url: 'https://example.test/social.png',
            indexing_enabled: indexingEnabled,
        },
    };
}

describe('PortfolioDocument', () => {
    beforeEach(() => {
        document.documentElement.lang = 'fr';
    });

    test('applies the configured language and public SEO metadata', () => {
        const wrapper = mount(PortfolioDocument, {
            props: {
                global: globalConfiguration(),
            },
        });

        expect(document.documentElement.lang).toBe('en');
        expect(wrapper.get('[data-head-title]').attributes('data-head-title')).toBe('Example Portfolio Title');
        expect(wrapper.get('meta[name="description"]').attributes('content')).toBe(
            'A neutral description used exclusively by the automated tests for Portfolio OS.',
        );
        expect(wrapper.get('link[rel="canonical"]').attributes('href')).toBe(
            'https://example.test/profile',
        );
        expect(wrapper.get('meta[property="og:image"]').attributes('content')).toBe(
            'https://example.test/social.png',
        );
        expect(wrapper.find('meta[name="robots"]').exists()).toBe(false);
    });

    test('adds noindex when indexing is disabled', () => {
        const wrapper = mount(PortfolioDocument, {
            props: {
                global: globalConfiguration(false),
            },
        });

        expect(wrapper.get('meta[name="robots"]').attributes('content')).toBe('noindex');
    });

    test('fails closed with noindex when the SEO block is absent', () => {
        const wrapper = mount(PortfolioDocument, {
            props: {
                global: {
                    identity: globalConfiguration().identity,
                },
            },
        });

        expect(wrapper.get('meta[name="robots"]').attributes('content')).toBe('noindex');
        expect(wrapper.find('meta[name="description"]').exists()).toBe(false);
        expect(wrapper.find('link[rel="canonical"]').exists()).toBe(false);
        expect(wrapper.find('meta[property="og:image"]').exists()).toBe(false);
    });
});
