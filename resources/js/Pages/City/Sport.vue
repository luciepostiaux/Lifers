<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    singleSession: Object,
    sportSessions: { type: Array, default: () => [] },
    activeSubscription: Object,
    physicalCondition: [String, Number],
    money: [String, Number],
});

const page = usePage();
const selectedAction = ref(null);
const actionPending = ref(false);

const hasActiveSubscription = computed(
    () => props.activeSubscription?.status === "active",
);

const activePlan = computed(() => props.activeSubscription?.sport_session);

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.flash?.success ??
        page.props.errors?.sessionId ??
        page.props.errors?.sportSessionId ??
        page.props.errors?.subscription,
);

const formatAmount = (amount) =>
    new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(
        Number(amount),
    );

const formatDate = (date) =>
    new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(date));

const canAfford = (amount) => Number(props.money) >= Number(amount);
const formatPlanName = (name) =>
    name ? `${name.charAt(0).toUpperCase()}${name.slice(1)}` : "";
const isActiveSubscription = (session) =>
    props.activeSubscription?.sport_session_id === session.id &&
    hasActiveSubscription.value;

const requestAction = (type, session = null) => {
    selectedAction.value = { type, session };
};

const closeDialog = () => {
    if (!actionPending.value) selectedAction.value = null;
};

const confirmAction = () => {
    if (!selectedAction.value) return;

    const { type, session } = selectedAction.value;
    const routeName =
        type === "single"
            ? "city.buySingleSportSession"
            : type === "subscribe"
              ? "city.subscribeToGym"
              : "city.cancelGymSubscription";
    const data =
        type === "single"
            ? { sessionId: session.id }
            : type === "subscribe"
              ? { sportSessionId: session.id }
              : {};

    actionPending.value = true;
    router.post(route(routeName), data, {
        preserveScroll: true,
        onFinish: () => {
            actionPending.value = false;
            selectedAction.value = null;
        },
    });
};

const dialogTitle = computed(() => {
    if (selectedAction.value?.type === "cancel") return "Annuler l’abonnement ?";
    if (selectedAction.value?.type === "subscribe") return "Souscrire à cette formule ?";
    return "Acheter cette séance ?";
});
</script>

<template>
    <AppLayout title="Sport" :money="money">
        <div class="path-page service-page">
            <Link :href="route('city')" class="path-back-link">
                <span aria-hidden="true">←</span> Retour à la ville
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="service-hero service-hero--sport" aria-labelledby="sport-title">
                <div class="service-hero__copy">
                    <span class="path-kicker">Bien-être</span>
                    <h1 id="sport-title">Bouger à ton rythme</h1>
                    <p>
                        Choisis une séance immédiate ou un abonnement renouvelable
                        pour améliorer la condition physique de ton Lifer.
                    </p>
                    <div class="service-hero__stats">
                        <span><strong>{{ physicalCondition }}/100</strong> condition physique</span>
                        <span><strong>{{ hasActiveSubscription ? "1" : "0" }}</strong> abonnement actif</span>
                    </div>
                </div>
                <div class="service-hero__visual">
                    <img src="/images/places/sport.png" alt="Centre sportif de Lifers" decoding="async" />
                </div>
            </section>

            <section class="sport-status" aria-labelledby="sport-status-title">
                <div>
                    <span class="path-kicker">Abonnement actuel</span>
                    <h2 id="sport-status-title">{{ activePlan ? formatPlanName(activePlan.name) : "Aucune formule active" }}</h2>
                    <p v-if="activeSubscription">
                        Actif jusqu’au {{ formatDate(activeSubscription.ends_at) }}.
                        Le renouvellement coûte {{ formatAmount(activePlan.price) }} Lif’coins et ajoute
                        {{ activePlan.physical_condition_effect }} points de condition physique.
                    </p>
                    <p v-else>
                        Choisis une formule ci-dessous si tu souhaites un renouvellement automatique.
                    </p>
                </div>
                <span
                    class="path-badge"
                    :class="hasActiveSubscription ? 'path-badge--active' : 'path-badge--neutral'"
                >
                    {{ hasActiveSubscription ? "Actif" : "Inactif" }}
                </span>
            </section>

            <section class="service-catalog" aria-labelledby="sport-options-title">
                <div class="service-catalog__heading">
                    <div>
                        <span class="path-kicker">Formules</span>
                        <h2 id="sport-options-title">Séances et abonnements</h2>
                    </div>
                    <p>Une seule formule d’abonnement peut être active à la fois.</p>
                </div>

                <div class="sport-grid">
                    <article v-if="singleSession" class="sport-card sport-card--single">
                        <div>
                            <span class="path-badge path-badge--available">Effet immédiat</span>
                            <h3>{{ singleSession.name }}</h3>
                            <p>Une séance ponctuelle sans renouvellement automatique.</p>
                        </div>
                        <dl class="service-facts">
                            <div><dt>Prix</dt><dd>{{ formatAmount(singleSession.price) }} Lif’coins</dd></div>
                            <div><dt>Effet</dt><dd>+{{ singleSession.physical_condition_effect }} Condition physique</dd></div>
                        </dl>
                        <button
                            type="button"
                            class="path-button path-button--primary path-button--full"
                            :disabled="actionPending || Number(physicalCondition) >= 100 || !canAfford(singleSession.price)"
                            @click="requestAction('single', singleSession)"
                        >
                            {{ Number(physicalCondition) >= 100 ? "Condition déjà au maximum" : canAfford(singleSession.price) ? "Acheter la séance" : "Solde insuffisant" }}
                        </button>
                    </article>

                    <article v-for="session in sportSessions" :key="session.id" class="sport-card">
                        <div>
                            <span
                                class="path-badge"
                                :class="isActiveSubscription(session) ? 'path-badge--active' : 'path-badge--neutral'"
                            >
                                {{ isActiveSubscription(session) ? "Formule active" : "Abonnement" }}
                            </span>
                            <h3>{{ formatPlanName(session.name) }}</h3>
                            <p>Renouvellement automatique à la fin de chaque période.</p>
                        </div>
                        <dl class="service-facts">
                            <div><dt>Prix</dt><dd>{{ formatAmount(session.price) }} Lif’coins</dd></div>
                            <div><dt>Durée</dt><dd>{{ session.duration_days }} jour{{ session.duration_days > 1 ? "s" : "" }}</dd></div>
                            <div><dt>Effet au renouvellement</dt><dd>+{{ session.physical_condition_effect }} Condition physique</dd></div>
                        </dl>
                        <button
                            v-if="isActiveSubscription(session)"
                            type="button"
                            class="path-button path-button--danger-link path-button--full"
                            :disabled="actionPending"
                            @click="requestAction('cancel', session)"
                        >
                            Annuler l’abonnement
                        </button>
                        <button
                            v-else
                            type="button"
                            class="path-button path-button--primary path-button--full"
                            :disabled="actionPending || hasActiveSubscription || !canAfford(session.price)"
                            @click="requestAction('subscribe', session)"
                        >
                            {{ hasActiveSubscription ? "Un abonnement est déjà actif" : canAfford(session.price) ? "Souscrire" : "Solde insuffisant" }}
                        </button>
                    </article>
                </div>
            </section>
        </div>

        <div
            v-if="selectedAction"
            class="path-dialog-backdrop"
            role="presentation"
            @click.self="closeDialog"
            @keydown.esc="closeDialog"
        >
            <section class="path-dialog" role="dialog" aria-modal="true" aria-labelledby="sport-dialog-title">
                <span class="path-kicker">Confirmation</span>
                <h2 id="sport-dialog-title">{{ dialogTitle }}</h2>
                <p v-if="selectedAction.type === 'cancel'">
                    La formule <strong>{{ formatPlanName(activePlan?.name) }}</strong> sera arrêtée immédiatement.
                </p>
                <p v-else-if="selectedAction.type === 'subscribe'">
                    Tu vas payer <strong>{{ formatAmount(selectedAction.session.price) }} Lif’coins</strong>
                    pour la formule {{ formatPlanName(selectedAction.session.name) }}.
                </p>
                <p v-else>
                    Tu vas payer <strong>{{ formatAmount(selectedAction.session.price) }} Lif’coins</strong>
                    et gagner {{ selectedAction.session.physical_condition_effect }} points de condition physique.
                </p>
                <div class="path-dialog__actions">
                    <button type="button" class="path-button path-button--ghost" :disabled="actionPending" @click="closeDialog">Annuler</button>
                    <button
                        type="button"
                        class="path-button"
                        :class="selectedAction.type === 'cancel' ? 'path-button--danger' : 'path-button--primary'"
                        :disabled="actionPending"
                        @click="confirmAction"
                    >
                        {{ actionPending ? "Validation…" : "Confirmer" }}
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
