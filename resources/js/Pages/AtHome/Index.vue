<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    lifer: { type: Object, required: true },
    bodyImageUrl: String,
    money: [String, Number],
    lifeGauges: { type: Object, default: () => ({}) },
    inventoryItemsByCategory: { type: Object, default: () => ({}) },
    currentSicknesses: { type: Array, default: () => [] },
});

const page = usePage();
const consumingItemId = ref(null);

const gaugeLabels = {
    hunger: "Faim",
    thirst: "Soif",
    clean: "Propreté",
    happiness: "Bonheur",
    entertainment: "Divertissement",
    physical_condition: "Condition physique",
    health: "Santé",
};

const characterName = computed(() =>
    [props.lifer.first_name, props.lifer.last_name].filter(Boolean).join(" "),
);

const safeGaugeValue = (value) => {
    const numericValue = Number(value);

    return Number.isFinite(numericValue)
        ? Math.min(100, Math.max(0, numericValue))
        : 0;
};

const gaugeTone = (value) => {
    const normalizedValue = safeGaugeValue(value);

    if (normalizedValue <= 15) return "priority";
    if (normalizedValue <= 60) return "watch";
    return "stable";
};

const gauges = computed(() =>
    Object.entries(props.lifeGauges ?? {}).map(([label, value]) => ({
        label,
        value: safeGaugeValue(value),
        tone: gaugeTone(value),
    })),
);

const lowestGauge = computed(() => {
    if (!gauges.value.length) return null;

    return gauges.value.reduce((lowest, gauge) =>
        gauge.value < lowest.value ? gauge : lowest,
    );
});

const categoryEntries = computed(() =>
    Object.entries(props.inventoryItemsByCategory ?? {}),
);

const totalItems = computed(() =>
    categoryEntries.value.reduce(
        (total, [, items]) =>
            total + items.reduce((subtotal, item) => subtotal + item.quantity, 0),
        0,
    ),
);

const feedbackMessage = computed(
    () =>
        page.props.flash?.success ??
        page.props.flash?.message ??
        page.props.errors?.itemId,
);

const bodyImageSource = computed(() => {
    if (!props.bodyImageUrl) return null;

    return props.bodyImageUrl.startsWith("/")
        ? props.bodyImageUrl
        : `/${props.bodyImageUrl}`;
});

const formatDate = (date) => {
    if (!date) return null;

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(date));
};

const effectLabel = (effect) =>
    `${effect.effect > 0 ? "+" : ""}${effect.effect} ${gaugeLabels[effect.gauge] || effect.gauge}`;

const consumeItem = (item) => {
    consumingItemId.value = item.id;
    router.post(
        route("consume-item"),
        { itemId: item.id },
        {
            preserveScroll: true,
            onFinish: () => (consumingItemId.value = null),
        },
    );
};
</script>

<template>
    <AppLayout title="Chez moi" :money="money">
        <div class="path-page home-page">
            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="home-hero" aria-labelledby="home-title">
                <div class="home-hero__copy">
                    <span class="path-kicker">Espace personnel</span>
                    <h1 id="home-title">Chez {{ characterName }}</h1>
                    <p>
                        Surveille les besoins de ton Lifer et utilise les objets de
                        son inventaire pour prendre soin de lui.
                    </p>

                    <div class="home-hero__summary">
                        <span v-if="lowestGauge">
                            Besoin prioritaire
                            <strong>{{ lowestGauge.label }} · {{ lowestGauge.value }}/100</strong>
                        </span>
                        <span>
                            Inventaire
                            <strong>{{ totalItems }} objet{{ totalItems > 1 ? "s" : "" }}</strong>
                        </span>
                        <span>
                            Santé
                            <strong>
                                {{ currentSicknesses.length ? `${currentSicknesses.length} maladie${currentSicknesses.length > 1 ? "s" : ""}` : "Aucune maladie" }}
                            </strong>
                        </span>
                    </div>
                </div>

                <div class="home-hero__character">
                    <img
                        v-if="bodyImageSource"
                        :src="bodyImageSource"
                        :alt="`Lifer ${characterName}`"
                        decoding="async"
                    />
                    <span v-else>Visuel indisponible</span>
                </div>
            </section>

            <div class="home-status-grid">
                <section class="home-panel" aria-labelledby="home-gauges-title">
                    <div class="home-panel__heading">
                        <div>
                            <span class="path-kicker">État actuel</span>
                            <h2 id="home-gauges-title">Jauges de vie</h2>
                        </div>
                        <span
                            v-if="lowestGauge"
                            class="path-badge"
                            :class="`home-badge--${lowestGauge.tone}`"
                        >
                            {{ lowestGauge.tone === "stable" ? "Équilibre stable" : "À surveiller" }}
                        </span>
                    </div>

                    <div v-if="gauges.length" class="home-gauge-grid">
                        <div v-for="gauge in gauges" :key="gauge.label" class="home-gauge">
                            <div class="home-gauge__label">
                                <span>{{ gauge.label }}</span>
                                <strong>{{ gauge.value }}/100</strong>
                            </div>
                            <div
                                class="home-gauge__track"
                                role="progressbar"
                                :aria-label="gauge.label"
                                :aria-valuenow="gauge.value"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            >
                                <span
                                    :class="`home-gauge__value--${gauge.tone}`"
                                    :style="{ width: `${gauge.value}%` }"
                                ></span>
                            </div>
                        </div>
                    </div>
                    <p v-else class="path-empty">Les jauges ne sont pas disponibles.</p>
                </section>

                <section class="home-panel home-panel--health" aria-labelledby="home-health-title">
                    <div class="home-panel__heading">
                        <div>
                            <span class="path-kicker">Suivi</span>
                            <h2 id="home-health-title">Santé</h2>
                        </div>
                        <span
                            class="path-badge"
                            :class="currentSicknesses.length ? 'path-badge--locked' : 'path-badge--available'"
                        >
                            {{ currentSicknesses.length ? "Soin nécessaire" : "Tout va bien" }}
                        </span>
                    </div>

                    <div v-if="currentSicknesses.length" class="home-sickness-list">
                        <article v-for="sickness in currentSicknesses" :key="sickness.id" class="home-sickness">
                            <h3>{{ sickness.name }}</h3>
                            <p>{{ sickness.description }}</p>
                            <div class="home-sickness__meta">
                                <span>Contractée le {{ formatDate(sickness.contracted_at) }}</span>
                                <span v-if="sickness.expected_recovery_at">
                                    Guérison prévue le {{ formatDate(sickness.expected_recovery_at) }}
                                </span>
                                <span v-if="sickness.fatal_at">
                                    Traitement indispensable avant le {{ formatDate(sickness.fatal_at) }}
                                </span>
                                <span v-if="sickness.needs_doctor">Médecin nécessaire</span>
                            </div>
                        </article>
                    </div>
                    <div v-else class="home-health-empty">
                        <p>Aucune maladie active n’affecte ton Lifer.</p>
                    </div>

                    <Link :href="route('doctor.index')" class="path-text-link">
                        Aller chez le médecin
                    </Link>
                </section>
            </div>

            <section class="home-inventory" aria-labelledby="inventory-title">
                <div class="home-inventory__heading">
                    <div>
                        <span class="path-kicker">Tes possessions</span>
                        <h2 id="inventory-title">Inventaire</h2>
                    </div>
                    <div class="home-inventory__actions">
                        <span>{{ totalItems }} objet{{ totalItems > 1 ? "s" : "" }}</span>
                        <Link :href="route('city.lifemarket')" class="path-text-link">
                            Aller au LifeMarket
                        </Link>
                    </div>
                </div>

                <div v-if="categoryEntries.length" class="home-categories">
                    <section
                        v-for="([category, items]) in categoryEntries"
                        :key="category"
                        class="home-category"
                        :aria-labelledby="`category-${category}`"
                    >
                        <h3 :id="`category-${category}`">{{ category }}</h3>
                        <div class="home-item-grid">
                            <article v-for="item in items" :key="item.id" class="home-item-card">
                                <div class="home-item-card__visual">
                                    <img
                                        v-if="item.image_path"
                                        :src="item.image_path"
                                        :alt="`Illustration de ${item.name}`"
                                        loading="lazy"
                                    />
                                    <span v-else aria-hidden="true">{{ category.charAt(0) }}</span>
                                    <strong>x{{ item.quantity }}</strong>
                                </div>
                                <div class="home-item-card__content">
                                    <h4>{{ item.name }}</h4>
                                    <p>{{ item.description }}</p>
                                    <div v-if="item.effects.length" class="home-item-card__effects" aria-label="Effets de l’objet">
                                        <span v-for="effect in item.effects" :key="`${item.id}-${effect.gauge}`">
                                            {{ effectLabel(effect) }}
                                        </span>
                                    </div>
                                    <button
                                        type="button"
                                        class="path-button path-button--primary path-button--full"
                                        :disabled="consumingItemId !== null || !item.effects.length"
                                        @click="consumeItem(item)"
                                    >
                                        {{ consumingItemId === item.id ? "Utilisation…" : item.effects.length ? "Utiliser" : "Aucun effet disponible" }}
                                    </button>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="path-empty">
                    <p>Ton inventaire est vide pour le moment.</p>
                    <Link :href="route('city.lifemarket')" class="path-text-link">
                        Découvrir le LifeMarket
                    </Link>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
