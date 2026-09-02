<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    currentSicknesses: { type: Array, default: () => [] },
    health: [String, Number],
    doctorVisitCost: [String, Number],
    money: [String, Number],
});

const page = usePage();
const pendingAction = ref(null);

const feedbackMessage = computed(
    () =>
        page.props.flash?.success ??
        page.props.flash?.message ??
        page.props.errors?.doctor ??
        page.props.errors?.sicknessId,
);

const formatAmount = (amount) =>
    new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(
        Number(amount),
    );

const formatDate = (date) => {
    if (!date) return null;

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(date));
};

const canAfford = (amount) => Number(props.money) >= Number(amount);

const treatSickness = (sickness) => {
    pendingAction.value = `sickness-${sickness.id}`;
    router.post(
        route("treat-sickness"),
        { sicknessId: sickness.id },
        {
            preserveScroll: true,
            onFinish: () => (pendingAction.value = null),
        },
    );
};

const visitDoctor = () => {
    pendingAction.value = "visit";
    router.post(
        route("visit-doctor"),
        {},
        {
            preserveScroll: true,
            onFinish: () => (pendingAction.value = null),
        },
    );
};
</script>

<template>
    <AppLayout title="Médecin" :money="money">
        <div class="path-page service-page">
            <Link :href="route('city')" class="path-back-link">
                <span aria-hidden="true">←</span> Retour à la ville
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="service-hero service-hero--doctor" aria-labelledby="doctor-title">
                <div class="service-hero__copy">
                    <span class="path-kicker">Santé</span>
                    <h1 id="doctor-title">Centre médical</h1>
                    <p>
                        Consulte un médecin pour restaurer la santé de ton Lifer
                        ou traite directement une maladie active.
                    </p>
                    <div class="service-hero__stats">
                        <span><strong>{{ health }}/100</strong> santé actuelle</span>
                        <span><strong>{{ currentSicknesses.length }}</strong> maladie{{ currentSicknesses.length > 1 ? "s" : "" }} active{{ currentSicknesses.length > 1 ? "s" : "" }}</span>
                    </div>
                </div>
                <div class="service-hero__visual">
                    <img src="/images/places/hopital.png" alt="Hôpital de Lifers" decoding="async" />
                </div>
            </section>

            <section class="medical-visit" aria-labelledby="medical-visit-title">
                <div>
                    <span class="path-kicker">Consultation générale</span>
                    <h2 id="medical-visit-title">Remettre la santé à 100</h2>
                    <p>
                        Cette visite restaure uniquement la jauge Santé. Elle ne
                        supprime pas les maladies actives.
                    </p>
                </div>
                <div class="medical-visit__action">
                    <strong>{{ formatAmount(doctorVisitCost) }} Lif’coins</strong>
                    <button
                        type="button"
                        class="path-button path-button--primary"
                        :disabled="pendingAction !== null || Number(health) >= 100 || !canAfford(doctorVisitCost)"
                        @click="visitDoctor"
                    >
                        {{ pendingAction === "visit" ? "Consultation…" : Number(health) >= 100 ? "Santé déjà au maximum" : canAfford(doctorVisitCost) ? "Consulter le médecin" : "Solde insuffisant" }}
                    </button>
                </div>
            </section>

            <section class="service-catalog" aria-labelledby="sickness-title">
                <div class="service-catalog__heading">
                    <div>
                        <span class="path-kicker">Diagnostic</span>
                        <h2 id="sickness-title">Maladies actuelles</h2>
                    </div>
                    <Link :href="route('athome')" class="path-text-link">Retourner chez moi</Link>
                </div>

                <div v-if="currentSicknesses.length" class="medical-grid">
                    <article v-for="sickness in currentSicknesses" :key="sickness.id" class="medical-card">
                        <div class="medical-card__heading">
                            <div>
                                <span class="path-kicker">Maladie active</span>
                                <h3>{{ sickness.name }}</h3>
                            </div>
                            <span class="path-badge path-badge--locked">
                                {{ sickness.needs_doctor ? "Médecin requis" : sickness.self_resolving ? "Guérison possible" : "À traiter" }}
                            </span>
                        </div>
                        <p>{{ sickness.description }}</p>

                        <dl class="service-facts">
                            <div>
                                <dt>Contractée le</dt>
                                <dd>{{ formatDate(sickness.pivot?.contracted_at || sickness.pivot?.created_at) }}</dd>
                            </div>
                            <div>
                                <dt>Guérison prévue</dt>
                                <dd>{{ formatDate(sickness.pivot?.expected_recovery_at) || "Non prévue" }}</dd>
                            </div>
                            <div v-if="sickness.pivot?.fatal_at">
                                <dt>Échéance vitale</dt>
                                <dd>{{ formatDate(sickness.pivot.fatal_at) }}</dd>
                            </div>
                            <div>
                                <dt>Traitement</dt>
                                <dd>{{ sickness.treatment_cost !== null ? `${formatAmount(sickness.treatment_cost)} Lif’coins` : "Indisponible" }}</dd>
                            </div>
                        </dl>

                        <button
                            type="button"
                            class="path-button path-button--primary path-button--full"
                            :disabled="pendingAction !== null || sickness.treatment_cost === null || !canAfford(sickness.treatment_cost)"
                            @click="treatSickness(sickness)"
                        >
                            {{ pendingAction === `sickness-${sickness.id}` ? "Traitement…" : sickness.treatment_cost === null ? "Aucun traitement disponible" : canAfford(sickness.treatment_cost) ? "Payer le traitement" : "Solde insuffisant" }}
                        </button>
                    </article>
                </div>

                <div v-else class="medical-empty">
                    <span class="path-badge path-badge--available">En bonne santé</span>
                    <h3>Aucune maladie active</h3>
                    <p>Ton Lifer n’a aucun traitement spécifique à suivre.</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
