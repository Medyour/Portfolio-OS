<script setup>
import { computed } from 'vue';
import HeaderSection from './HeaderSection.vue';
import HeroSection from './HeroSection.vue';

const props = defineProps({
    components: {
        type: Array,
        required: true,
    },
    global: {
        type: Object,
        default: () => ({}),
    },
});

const allowedTypes = new Set([
    'header',
    'hero',
    'services',
    'projects',
    'about',
    'testimonials',
    'contact_cta',
    'contact_form',
    'footer',
]);

const components = computed(() => props.components.filter((component) => allowedTypes.has(component.type)));
const header = computed(() => components.value.find((component) => component.type === 'header'));
const contentComponents = computed(() => components.value.filter((component) => component.type !== 'header'));
</script>

<template>
    <div data-testid="portfolio-composer">
        <HeaderSection
            v-if="header"
            :component="header"
            :global="global"
        />

        <main>
            <template v-for="component in contentComponents" :key="component.type">
                <HeroSection
                    v-if="component.type === 'hero'"
                    :component="component"
                    :global="global"
                />
                <div v-else-if="component.type === 'services'" data-portfolio-component="services"></div>
                <div v-else-if="component.type === 'projects'" data-portfolio-component="projects"></div>
                <div v-else-if="component.type === 'about'" data-portfolio-component="about"></div>
                <div v-else-if="component.type === 'testimonials'" data-portfolio-component="testimonials"></div>
                <div v-else-if="component.type === 'contact_cta'" data-portfolio-component="contact_cta"></div>
                <div v-else-if="component.type === 'contact_form'" data-portfolio-component="contact_form"></div>
                <div v-else-if="component.type === 'footer'" data-portfolio-component="footer"></div>
            </template>
        </main>
    </div>
</template>
