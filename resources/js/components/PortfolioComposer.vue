<script setup>
import { computed } from 'vue';

const props = defineProps({
    components: {
        type: Array,
        required: true,
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
</script>

<template>
    <main data-testid="portfolio-composer">
        <template v-for="component in components" :key="component.type">
            <div v-if="component.type === 'header'" data-portfolio-component="header"></div>
            <div v-else-if="component.type === 'hero'" data-portfolio-component="hero"></div>
            <div v-else-if="component.type === 'services'" data-portfolio-component="services"></div>
            <div v-else-if="component.type === 'projects'" data-portfolio-component="projects"></div>
            <div v-else-if="component.type === 'about'" data-portfolio-component="about"></div>
            <div v-else-if="component.type === 'testimonials'" data-portfolio-component="testimonials"></div>
            <div v-else-if="component.type === 'contact_cta'" data-portfolio-component="contact_cta"></div>
            <div v-else-if="component.type === 'contact_form'" data-portfolio-component="contact_form"></div>
            <div v-else-if="component.type === 'footer'" data-portfolio-component="footer"></div>
        </template>
    </main>
</template>
