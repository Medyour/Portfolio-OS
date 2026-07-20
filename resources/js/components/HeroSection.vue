<script setup>
import { computed } from 'vue';

const props = defineProps({
    component: {
        type: Object,
        required: true,
    },
    global: {
        type: Object,
        default: () => ({}),
    },
});

const identity = computed(() => props.global.identity ?? {});
const photoUrl = computed(() => (
    props.component.photo_override_url ?? identity.value.profile_photo_url ?? null
));
const photoAlt = computed(() => (
    props.component.photo_override_url
        ? props.component.photo_alt
        : identity.value.display_name ?? ''
));
const hasActions = computed(() => (
    props.component.primary_cta !== undefined || props.component.secondary_cta !== undefined
));
</script>

<template>
    <section
        id="hero"
        class="bg-slate-50"
        data-portfolio-component="hero"
    >
        <div
            class="mx-auto max-w-6xl px-4 py-16 sm:px-6 min-[768px]:py-24"
            :class="{ 'grid items-center gap-10 min-[768px]:grid-cols-2': photoUrl }"
        >
            <div class="max-w-3xl">
                <p
                    v-if="component.eyebrow"
                    class="mb-4 text-sm font-semibold tracking-wide text-slate-600"
                >
                    {{ component.eyebrow }}
                </p>
                <h1 class="text-4xl font-semibold tracking-tight text-slate-950 sm:text-5xl">
                    {{ component.headline }}
                </h1>
                <p
                    v-if="component.description"
                    class="mt-6 max-w-2xl text-lg leading-8 text-slate-700"
                >
                    {{ component.description }}
                </p>

                <div
                    v-if="hasActions"
                    class="mt-8 flex flex-col gap-3 sm:flex-row sm:flex-wrap"
                    data-testid="hero-actions"
                >
                    <a
                        v-if="component.primary_cta"
                        class="inline-flex min-h-11 items-center justify-center rounded-md bg-slate-950 px-6 py-3 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                        :href="component.primary_cta.target"
                    >
                        {{ component.primary_cta.label }}
                    </a>
                    <a
                        v-if="component.secondary_cta"
                        class="inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                        :href="component.secondary_cta.target"
                    >
                        {{ component.secondary_cta.label }}
                    </a>
                </div>
            </div>

            <div v-if="photoUrl" data-testid="hero-media">
                <img
                    class="aspect-square w-full rounded-lg object-cover"
                    :src="photoUrl"
                    :alt="photoAlt"
                />
            </div>
        </div>
    </section>
</template>
