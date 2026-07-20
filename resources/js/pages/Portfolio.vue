<script setup>
import PortfolioComposer from '../components/PortfolioComposer.vue';
import PortfolioDocument from '../components/PortfolioDocument.vue';

defineProps({
    portfolio: {
        type: Object,
        required: true,
    },
});
</script>

<template>
    <PortfolioDocument :global="portfolio.global" />
    <PortfolioComposer
        :components="portfolio.components"
        :global="portfolio.global"
    />
</template>
