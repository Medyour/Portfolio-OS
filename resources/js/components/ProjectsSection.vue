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
        id="projects"
        class="bg-slate-50"
        data-portfolio-component="projects"
    >
        <div class="mx-auto max-w-6xl px-4 py-16 sm:px-6 min-[768px]:py-24">
            <div class="max-w-3xl">
                <h2 class="text-3xl font-semibold tracking-tight text-slate-950 sm:text-4xl">
                    {{ component.title }}
                </h2>
                <p
                    v-if="component.intro"
                    class="mt-4 text-lg leading-8 text-slate-700"
                >
                    {{ component.intro }}
                </p>
            </div>

            <div class="mt-10 grid gap-6 min-[768px]:grid-cols-2">
                <article
                    v-for="item in items"
                    :key="item.title"
                    class="overflow-hidden rounded-lg border border-slate-200 bg-white"
                    data-testid="project-item"
                >
                    <img
                        v-if="item.image_url"
                        class="aspect-video w-full object-cover"
                        :src="item.image_url"
                        :alt="item.image_alt"
                    />

                    <div class="p-6 sm:p-8">
                        <div
                            v-if="item.client_logo_url || item.client_name"
                            class="mb-5 flex items-center gap-3"
                            data-testid="project-client"
                        >
                            <img
                                v-if="item.client_logo_url"
                                class="h-8 w-auto max-w-32 object-contain"
                                :src="item.client_logo_url"
                                :alt="item.client_name ?? ''"
                            />
                            <p v-if="item.client_name" class="text-sm font-medium text-slate-600">
                                {{ item.client_name }}
                            </p>
                        </div>

                        <h3 class="text-xl font-semibold text-slate-950">
                            {{ item.title }}
                        </h3>
                        <p class="mt-4 leading-7 text-slate-700">
                            {{ item.summary }}
                        </p>
                        <p v-if="item.challenge" class="mt-5 leading-7 text-slate-700">
                            {{ item.challenge }}
                        </p>
                        <p v-if="item.solution" class="mt-5 leading-7 text-slate-700">
                            {{ item.solution }}
                        </p>
                        <p
                            v-if="item.result"
                            class="mt-5 border-l-2 border-slate-950 pl-4 font-medium leading-7 text-slate-900"
                        >
                            {{ item.result }}
                        </p>

                        <ul
                            v-if="item.tags?.length"
                            class="mt-6 flex flex-wrap gap-2"
                            aria-label="Technologies"
                            data-testid="project-tags"
                        >
                            <li
                                v-for="tag in item.tags"
                                :key="tag"
                                class="rounded-full bg-slate-100 px-3 py-1 text-sm font-medium text-slate-700"
                            >
                                {{ tag }}
                            </li>
                        </ul>

                        <a
                            v-if="item.project_url"
                            class="mt-6 inline-flex min-h-11 items-center font-semibold text-slate-950 underline decoration-slate-300 underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                            :href="item.project_url"
                        >
                            Voir le projet
                        </a>
                    </div>
                </article>
            </div>
        </div>
    </section>
</template>
