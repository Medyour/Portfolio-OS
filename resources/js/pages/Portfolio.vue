<script setup>
import PortfolioComposer from '../components/PortfolioComposer.vue';

defineProps({
    portfolio: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <PortfolioComposer :components="portfolio.components" />
</template>
