<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

const props = defineProps({
    global: {
        type: Object,
        required: true,
    },
});

const identity = computed(() => props.global.identity ?? {});
const seo = computed(() => props.global.seo ?? {});
const indexingEnabled = computed(() => seo.value.indexing_enabled === true);

watch(
    () => identity.value.default_language,
    (language) => {
        if (typeof document !== 'undefined' && language) {
            document.documentElement.lang = language;
        }
    },
    { immediate: true },
);
</script>

<template>
    <Head :title="seo.meta_title">
        <meta
            v-if="seo.meta_description"
            head-key="description"
            name="description"
            :content="seo.meta_description"
        />
        <meta
            v-if="!indexingEnabled"
            head-key="robots"
            name="robots"
            content="noindex"
        />
        <link
            v-if="seo.canonical_url"
            head-key="canonical"
            rel="canonical"
            :href="seo.canonical_url"
        />
        <meta
            v-if="seo.social_image_url"
            head-key="social-image"
            property="og:image"
            :content="seo.social_image_url"
        />
    </Head>
</template>
