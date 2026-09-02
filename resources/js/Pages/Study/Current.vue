<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    studyDetails: { type: Object, required: true },
    enrollmentDetails: { type: Object, required: true },
    canClaimDiploma: Boolean,
    money: [String, Number],
});

const page = usePage();
const showResignDialog = ref(false);
const actionPending = ref(false);

const progress = computed(() => {
    const start = new Date(props.enrollmentDetails.started_at).getTime();
    const end = new Date(props.enrollmentDetails.ends_at).getTime();

    if (![start, end].every(Number.isFinite) || end <= start) return 0;

    return Math.min(
        100,
        Math.max(0, Math.round(((Date.now() - start) / (end - start)) * 100)),
    );
});

const remainingDays = computed(() =>
    Math.max(
        0,
        Math.ceil(
            (new Date(props.enrollmentDetails.ends_at).getTime() - Date.now()) /
                86400000,
        ),
    ),
);

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.errors?.study ??
        page.props.errors?.msg,
);

const formatDate = (date) =>
    new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(date));

const resignFromStudy = () => {
    actionPending.value = true;
    router.post(
        route("study.resign"),
        {},
        {
            onFinish: () => {
                actionPending.value = false;
                showResignDialog.value = false;
            },
        },
    );
};

const claimDiploma = () => {
    actionPending.value = true;
    router.post(
        route("study.claimDiploma", { study: props.studyDetails.id }),
        {},
        { onFinish: () => (actionPending.value = false) },
    );
};
</script>

<template>
    <AppLayout title="Étude en cours" :money="money">
        <div class="path-page path-page--current">
            <Link :href="route('study.index')" class="path-back-link">
                <span aria-hidden="true">←</span> Toutes les études
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="path-detail-hero" aria-labelledby="current-study-name">
                <div class="path-detail-hero__copy">
                    <div class="path-detail-hero__heading">
                        <div>
                            <span class="path-kicker">Étude en cours</span>
                            <h1 id="current-study-name">{{ studyDetails.name }}</h1>
                        </div>
                        <span class="path-badge path-badge--active">En progression</span>
                    </div>
                    <p>{{ studyDetails.short_description }}</p>

                    <div class="path-progress-block path-progress-block--wide">
                        <div class="path-progress-block__label">
                            <span>Progression vers le diplôme</span>
                            <strong>{{ progress }} %</strong>
                        </div>
                        <div
                            class="path-progress"
                            role="progressbar"
                            aria-label="Progression vers le diplôme"
                            :aria-valuenow="progress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >
                            <span :style="{ width: `${progress}%` }"></span>
                        </div>
                        <small>
                            {{ canClaimDiploma ? "La formation est terminée." : `${remainingDays} jour${remainingDays > 1 ? "s" : ""} avant la fin.` }}
                        </small>
                    </div>
                </div>
                <div class="path-detail-hero__visual">
                    <img
                        :src="studyDetails.image_path || '/images/places/universite.png'"
                        :alt="`Illustration de ${studyDetails.name}`"
                    />
                </div>
            </section>

            <div class="path-detail-layout">
                <section class="path-detail-card" aria-labelledby="study-information-title">
                    <span class="path-kicker">Informations</span>
                    <h2 id="study-information-title">Le programme</h2>
                    <p>
                        {{ studyDetails.long_description || studyDetails.short_description }}
                    </p>
                    <dl class="path-detail-facts">
                        <div><dt>Début</dt><dd>{{ formatDate(enrollmentDetails.started_at) }}</dd></div>
                        <div><dt>Fin prévue</dt><dd>{{ formatDate(enrollmentDetails.ends_at) }}</dd></div>
                        <div><dt>Lieu</dt><dd>{{ studyDetails.place?.name || "Université" }}</dd></div>
                        <div><dt>Diplôme préparé</dt><dd>{{ studyDetails.awarded_diploma?.name || "Non renseigné" }}</dd></div>
                    </dl>
                </section>

                <aside class="path-action-card" aria-labelledby="study-next-step-title">
                    <span class="path-kicker">Prochaine étape</span>
                    <h2 id="study-next-step-title">
                        {{ canClaimDiploma ? "Ton diplôme est prêt" : "Poursuis ta formation" }}
                    </h2>
                    <p v-if="canClaimDiploma">
                        La date de fin est atteinte. Récupère ton diplôme pour terminer cette étude.
                    </p>
                    <p v-else>
                        Le diplôme sera disponible le {{ formatDate(enrollmentDetails.ends_at) }}.
                    </p>
                    <button
                        v-if="canClaimDiploma"
                        type="button"
                        class="path-button path-button--primary path-button--full"
                        :disabled="actionPending"
                        @click="claimDiploma"
                    >
                        {{ actionPending ? "Validation…" : "Récupérer mon diplôme" }}
                    </button>
                    <button
                        type="button"
                        class="path-button path-button--danger-link path-button--full"
                        :disabled="actionPending"
                        @click="showResignDialog = true"
                    >
                        Quitter cette étude
                    </button>
                </aside>
            </div>
        </div>

        <div
            v-if="showResignDialog"
            class="path-dialog-backdrop"
            role="presentation"
            @click.self="showResignDialog = false"
            @keydown.esc="showResignDialog = false"
        >
            <section class="path-dialog" role="dialog" aria-modal="true" aria-labelledby="resign-study-title">
                <span class="path-kicker">Attention</span>
                <h2 id="resign-study-title">Quitter cette étude ?</h2>
                <p>
                    La progression de <strong>{{ studyDetails.name }}</strong> sera perdue et aucun diplôme ne sera accordé.
                </p>
                <div class="path-dialog__actions">
                    <button type="button" class="path-button path-button--ghost" :disabled="actionPending" @click="showResignDialog = false">Continuer mes études</button>
                    <button type="button" class="path-button path-button--danger" :disabled="actionPending" @click="resignFromStudy">
                        {{ actionPending ? "Abandon…" : "Confirmer l’abandon" }}
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
