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
const expertise = computed(() => (
    Array.isArray(props.component.expertise) ? props.component.expertise : []
));
const highlights = computed(() => (
    Array.isArray(props.component.highlights) ? props.component.highlights : []
));
</script>

<template>
    <section
        id="about"
        class="bg-white"
        data-portfolio-component="about"
    >
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 min-[768px]:py-24">
            <div class="grid gap-10 min-[768px]:grid-cols-[minmax(0,2fr)_minmax(0,1fr)] min-[768px]:gap-16">
                <div class="max-w-3xl">
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
                        {{ component.title }}
                    </h2>
                    <p class="mt-6 text-lg leading-8 text-slate-700">
                        {{ component.summary }}
                    </p>
                    <p
                        v-if="component.details"
                        class="mt-5 leading-7 text-slate-700"
                    >
                        {{ component.details }}
                    </p>

                    <ul
                        v-if="highlights.length"
                        class="mt-8 space-y-3 text-slate-700"
                        data-testid="about-highlights"
                    >
                        <li
                            v-for="highlight in highlights"
                            :key="highlight"
                            class="border-l-2 border-slate-950 pl-4"
                        >
                            {{ highlight }}
                        </li>
                    </ul>
                </div>

                <div>
                    <img
                        v-if="photoUrl"
                        class="mb-8 aspect-square w-full rounded-lg object-cover"
                        :src="photoUrl"
                        :alt="photoAlt"
                        data-testid="about-photo"
                    />

                    <ul
                        v-if="expertise.length"
                        class="flex flex-wrap gap-2 min-[768px]:pt-14"
                        aria-label="Expertises"
                        data-testid="about-expertise"
                    >
                        <li
                            v-for="item in expertise"
                            :key="item"
                            class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700"
                        >
                            {{ item }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>
