<script setup>
import { computed } from 'vue';

const props = defineProps({
    component: {
        type: Object,
        required: true,
    },
});

const items = computed(() => (
    Array.isArray(props.component.items) ? props.component.items : []
));
</script>

<template>
    <section
        id="testimonials"
        class="bg-slate-50"
        data-portfolio-component="testimonials"
    >
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 min-[768px]:py-24">
            <div v-if="component.title || component.intro" class="max-w-3xl">
                <h2
                    v-if="component.title"
                    class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl"
                >
                    {{ component.title }}
                </h2>
                <p
                    v-if="component.intro"
                    class="mt-4 text-lg leading-8 text-slate-700"
                >
                    {{ component.intro }}
                </p>
            </div>

            <div
                class="grid gap-6 min-[768px]:grid-cols-2"
                :class="{ 'mt-10': component.title || component.intro }"
            >
                <figure
                    v-for="item in items"
                    :key="`${item.author_name}-${item.quote}`"
                    class="rounded-lg border border-slate-200 bg-white p-6 sm:p-8"
                    data-testid="testimonial-item"
                >
                    <blockquote class="text-lg leading-8 text-slate-800">
                        “{{ item.quote }}”
                    </blockquote>
                    <figcaption class="mt-6 flex items-center gap-4">
                        <img
                            v-if="item.photo_url"
                            class="size-12 rounded-full object-cover"
                            :src="item.photo_url"
                            :alt="item.author_name"
                        />
                        <div>
                            <p class="font-semibold text-slate-950">
                                {{ item.author_name }}
                            </p>
                            <p
                                v-if="item.author_role || item.organization"
                                class="mt-1 text-sm text-slate-600"
                                data-testid="testimonial-attribution"
                            >
                                {{ [item.author_role, item.organization].filter(Boolean).join(' — ') }}
                            </p>
                        </div>
                        <img
                            v-if="item.company_logo_url"
                            class="ml-auto h-8 w-auto max-w-28 object-contain"
                            :src="item.company_logo_url"
                            :alt="item.organization ?? ''"
                        />
                    </figcaption>
                    <a
                        v-if="item.source_url"
                        class="mt-5 inline-flex min-h-11 items-center font-semibold text-slate-950 underline decoration-slate-300 underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                        :href="item.source_url"
                    >
                        Voir la source
                    </a>
                </figure>
            </div>
        </div>
    </section>
</template>
