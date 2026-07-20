<script setup>
import { computed, ref } from 'vue';

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

const menuOpen = ref(false);
const identity = computed(() => props.global.identity ?? {});
const brandLabel = computed(() => props.component.brand_label ?? identity.value.display_name);
const logoUrl = computed(() => (
    props.component.show_logo === true ? identity.value.logo_url : null
));
const navigation = computed(() => (
    Array.isArray(props.component.navigation) ? props.component.navigation : []
));
const primaryCta = computed(() => props.component.primary_cta ?? null);
const hasMenuContent = computed(() => navigation.value.length > 0 || primaryCta.value !== null);
const menuLabel = computed(() => (
    menuOpen.value ? 'Fermer le menu de navigation' : 'Ouvrir le menu de navigation'
));

function closeMenu() {
    menuOpen.value = false;
}
</script>

<template>
    <header
        id="header"
        class="border-b border-slate-200 bg-white"
        data-portfolio-component="header"
        @keydown.esc="closeMenu"
    >
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <div class="flex min-h-16 items-center justify-between gap-6">
                <div
                    v-if="logoUrl || brandLabel"
                    class="shrink-0"
                    data-testid="header-identity"
                >
                    <img
                        v-if="logoUrl"
                        class="h-10 w-auto max-w-48 object-contain"
                        :src="logoUrl"
                        :alt="brandLabel"
                    />
                    <span
                        v-else
                        class="text-base font-semibold text-slate-950"
                    >
                        {{ brandLabel }}
                    </span>
                </div>

                <div
                    v-if="hasMenuContent"
                    class="hidden items-center gap-6 min-[768px]:flex"
                    data-testid="desktop-navigation"
                >
                    <nav v-if="navigation.length">
                        <ul class="flex items-center gap-5">
                            <li v-for="item in navigation" :key="`${item.label}-${item.target}`">
                                <a
                                    class="rounded-sm text-sm font-medium text-slate-700 hover:text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                                    :href="item.target"
                                >
                                    {{ item.label }}
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <a
                        v-if="primaryCta"
                        class="inline-flex min-h-11 items-center justify-center rounded-md bg-slate-950 px-5 py-2 text-sm font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950"
                        :href="primaryCta.target"
                    >
                        {{ primaryCta.label }}
                    </a>
                </div>

                <button
                    v-if="hasMenuContent"
                    class="inline-flex min-h-11 min-w-11 items-center justify-center rounded-md border border-slate-300 text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-slate-950 min-[768px]:hidden"
                    type="button"
                    aria-controls="portfolio-mobile-navigation"
                    :aria-expanded="menuOpen ? 'true' : 'false'"
                    :aria-label="menuLabel"
                    data-testid="mobile-menu-button"
                    @click="menuOpen = !menuOpen"
                >
                    <svg
                        aria-hidden="true"
                        class="h-5 w-5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            v-if="menuOpen"
                            stroke-linecap="round"
                            d="M6 6l12 12M18 6L6 18"
                        />
                        <path
                            v-else
                            stroke-linecap="round"
                            d="M4 7h16M4 12h16M4 17h16"
                        />
                    </svg>
                </button>
            </div>

            <div
                v-if="hasMenuContent && menuOpen"
                id="portfolio-mobile-navigation"
                class="border-t border-slate-200 py-4 min-[768px]:hidden"
                data-testid="mobile-navigation"
            >
                <nav v-if="navigation.length">
                    <ul class="flex flex-col gap-1">
                        <li v-for="item in navigation" :key="`${item.label}-${item.target}`">
                            <a
                                class="flex min-h-11 items-center rounded-md px-3 py-2 text-base font-medium text-slate-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                                :href="item.target"
                                @click="closeMenu"
                            >
                                {{ item.label }}
                            </a>
                        </li>
                    </ul>
                </nav>

                <a
                    v-if="primaryCta"
                    class="mt-3 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-slate-950 px-5 py-2 font-semibold text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                    :href="primaryCta.target"
                    @click="closeMenu"
                >
                    {{ primaryCta.label }}
                </a>
            </div>
        </div>
    </header>
</template>
