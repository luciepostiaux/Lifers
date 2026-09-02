<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    activitiesByCategory: { type: Object, default: () => ({}) },
    lifeGauges: { type: Object, default: () => ({}) },
    money: [String, Number],
});

const page = usePage();
const pendingActivityId = ref(null);

const gaugeLabels = {
    happiness: "Bonheur",
    entertainment: "Divertissement",
    physical_condition: "Condition physique",
};

const categoryEntries = computed(() =>
    Object.entries(props.activitiesByCategory ?? {}),
);

const activities = computed(() =>
    categoryEntries.value.flatMap(([, categoryActivities]) => categoryActivities),
);

const feedbackMessage = computed(
    () =>
        page.props.flash?.success ??
        page.props.flash?.message ??
        page.props.errors?.activityId,
);

const formatAmount = (amount) =>
    new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(
        Number(amount),
    );

const effectLabel = (effect) =>
    `${effect.effect > 0 ? "+" : ""}${effect.effect} ${gaugeLabels[effect.gauge] || effect.gauge}`;

const canAfford = (activity) => Number(props.money) >= Number(activity.price);

const activityUseful = (activity) =>
    activity.effects.some(
        (effect) =>
            effect.effect > 0 && Number(props.lifeGauges[effect.gauge] ?? 0) < 100,
    );

const participate = (activity) => {
    pendingActivityId.value = activity.id;
    router.post(
        route("city.participate"),
        { activityId: activity.id },
        {
            preserveScroll: true,
            onFinish: () => (pendingActivityId.value = null),
        },
    );
};
</script>

<template>
    <AppLayout title="Loisirs" :money="money">
        <div class="path-page service-page">
            <Link :href="route('city')" class="path-back-link">
                <span aria-hidden="true">←</span> Retour à la ville
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="service-hero service-hero--entertainment" aria-labelledby="entertainment-title">
                <div class="service-hero__copy">
                    <span class="path-kicker">Temps libre</span>
                    <h1 id="entertainment-title">Profiter de la ville</h1>
                    <p>
                        Choisis une activité et découvre précisément les besoins
                        qu’elle améliorera avant de participer.
                    </p>
                    <div class="service-hero__stats">
                        <span><strong>{{ lifeGauges.happiness }}/100</strong> bonheur</span>
                        <span><strong>{{ lifeGauges.entertainment }}/100</strong> divertissement</span>
                        <span><strong>{{ activities.length }}</strong> activités</span>
                    </div>
                </div>
                <div class="service-hero__visual">
                    <img src="/images/places/loisir.png" alt="Lieu de loisirs de Lifers" decoding="async" />
                </div>
            </section>

            <section class="service-catalog" aria-labelledby="activities-title">
                <div class="service-catalog__heading">
                    <div>
                        <span class="path-kicker">Activités</span>
                        <h2 id="activities-title">Que veux-tu faire ?</h2>
                    </div>
                    <p>L’effet est appliqué immédiatement après le paiement.</p>
                </div>

                <div v-if="categoryEntries.length" class="service-categories">
                    <section
                        v-for="([category, categoryActivities]) in categoryEntries"
                        :key="category"
                        class="service-category"
                        :aria-labelledby="`activity-category-${category}`"
                    >
                        <h3 :id="`activity-category-${category}`">{{ category }}</h3>
                        <div class="activity-grid">
                            <article v-for="activity in categoryActivities" :key="activity.id" class="activity-card">
                                <div class="activity-card__marker" aria-hidden="true">
                                    {{ category.charAt(0) }}
                                </div>
                                <div class="activity-card__content">
                                    <div>
                                        <span class="path-kicker">{{ category }}</span>
                                        <h4>{{ activity.name }}</h4>
                                        <p>{{ activity.description }}</p>
                                    </div>
                                    <div class="service-effects" aria-label="Effets de l’activité">
                                        <span v-for="effect in activity.effects" :key="`${activity.id}-${effect.gauge}`">
                                            {{ effectLabel(effect) }}
                                        </span>
                                    </div>
                                    <div class="activity-card__footer">
                                        <strong>{{ formatAmount(activity.price) }} Lif’coins</strong>
                                        <button
                                            type="button"
                                            class="path-button path-button--primary"
                                            :disabled="pendingActivityId !== null || !canAfford(activity) || !activityUseful(activity)"
                                            @click="participate(activity)"
                                        >
                                            {{ pendingActivityId === activity.id ? "Participation…" : !canAfford(activity) ? "Solde insuffisant" : !activityUseful(activity) ? "Besoins déjà au maximum" : "Participer" }}
                                        </button>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="path-empty">
                    <p>Aucune activité n’est disponible pour le moment.</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
