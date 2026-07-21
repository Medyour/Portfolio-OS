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
        id="services"
        class="bg-white"
        data-portfolio-component="services"
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
                    class="flex h-full flex-col rounded-lg border border-slate-200 p-6 sm:p-8"
                    data-testid="service-item"
                >
                    <h3 class="text-xl font-semibold text-slate-950">
                        {{ item.title }}
                    </h3>
                    <p class="mt-4 leading-7 text-slate-700">
                        {{ item.description }}
                    </p>
                    <p class="mt-6 border-l-2 border-slate-950 pl-4 font-medium leading-7 text-slate-900">
                        {{ item.benefit }}
                    </p>
                    <a
                        v-if="item.cta"
                        class="mt-6 inline-flex min-h-11 w-fit items-center rounded-md font-semibold text-slate-950 underline decoration-slate-300 underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                        :href="item.cta.target"
                    >
                        {{ item.cta.label }}
                    </a>
                </article>
            </div>
        </div>
    </section>
</template>
