<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    jobDetails: { type: Object, required: true },
    employmentDetails: { type: Object, required: true },
    money: [String, Number],
});

const page = usePage();
const showResignDialog = ref(false);
const resignationPending = ref(false);

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.errors?.job ??
        page.props.errors?.msg,
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

const resign = () => {
    resignationPending.value = true;
    router.post(
        route("job.resign"),
        {},
        {
            onFinish: () => {
                resignationPending.value = false;
                showResignDialog.value = false;
            },
        },
    );
};
</script>

<template>
    <AppLayout title="Métier actuel" :money="money">
        <div class="path-page path-page--current">
            <Link :href="route('job')" class="path-back-link">
                <span aria-hidden="true">←</span> Tous les métiers
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="path-detail-hero path-detail-hero--job" aria-labelledby="current-job-name">
                <div class="path-detail-hero__copy">
                    <div class="path-detail-hero__heading">
                        <div>
                            <span class="path-kicker">Métier actuel</span>
                            <h1 id="current-job-name">{{ jobDetails.name }}</h1>
                        </div>
                        <span class="path-badge path-badge--active">En poste</span>
                    </div>
                    <p>{{ jobDetails.short_description }}</p>
                    <div class="path-employment-highlight">
                        <span>Salaire actuel</span>
                        <strong>{{ formatAmount(employmentDetails.current_salary) }} Lif’coins</strong>
                        <small>
                            Salaire de départ : {{ formatAmount(jobDetails.salary) }} Lif’coins
                        </small>
                    </div>
                </div>
                <div class="path-detail-hero__visual">
                    <img
                        :src="jobDetails.image_path || '/images/places/emploi.png'"
                        :alt="`Illustration du métier ${jobDetails.name}`"
                    />
                </div>
            </section>

            <div class="path-detail-layout">
                <section class="path-detail-card" aria-labelledby="job-information-title">
                    <span class="path-kicker">Informations</span>
                    <h2 id="job-information-title">Ton poste</h2>
                    <p>{{ jobDetails.long_description || jobDetails.short_description }}</p>
                    <dl class="path-detail-facts">
                        <div><dt>En poste depuis</dt><dd>{{ formatDate(employmentDetails.started_at) }}</dd></div>
                        <div><dt>Lieu</dt><dd>{{ jobDetails.place?.name || "Non renseigné" }}</dd></div>
                        <div><dt>Salaire actuel</dt><dd>{{ formatAmount(employmentDetails.current_salary) }} Lif’coins</dd></div>
                        <div><dt>Ancienneté</dt><dd>{{ employmentDetails.seniority_years }} an{{ employmentDetails.seniority_years > 1 ? "s" : "" }}</dd></div>
                        <div><dt>Hausses obtenues</dt><dd>{{ employmentDetails.raise_count }} / {{ employmentDetails.max_raises }}</dd></div>
                        <div><dt>Diplôme requis</dt><dd>{{ jobDetails.required_diploma?.name || "Aucun" }}</dd></div>
                    </dl>
                </section>

                <aside class="path-action-card" aria-labelledby="job-management-title">
                    <span class="path-kicker">Gestion</span>
                    <h2 id="job-management-title">Gérer ton métier</h2>
                    <p>
                        <template v-if="employmentDetails.next_raise_at">
                            Ta prochaine hausse de {{ employmentDetails.raise_rate }} % sera appliquée le
                            {{ formatDate(employmentDetails.next_raise_at) }} si tu conserves ce poste.
                        </template>
                        <template v-else>
                            Tu as atteint le maximum de {{ employmentDetails.max_raises }} augmentations pour ce poste.
                        </template>
                    </p>
                    <Link :href="route('job')" class="path-button path-button--secondary path-button--full">
                        Voir les autres métiers
                    </Link>
                    <button
                        type="button"
                        class="path-button path-button--danger-link path-button--full"
                        :disabled="resignationPending"
                        @click="showResignDialog = true"
                    >
                        Démissionner
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
            <section class="path-dialog" role="dialog" aria-modal="true" aria-labelledby="resign-job-title">
                <span class="path-kicker">Attention</span>
                <h2 id="resign-job-title">Démissionner de ce métier ?</h2>
                <p>
                    Ton Lifer quittera son poste de <strong>{{ jobDetails.name }}</strong> et n’aura plus de métier actif.
                </p>
                <div class="path-dialog__actions">
                    <button type="button" class="path-button path-button--ghost" :disabled="resignationPending" @click="showResignDialog = false">Garder mon métier</button>
                    <button type="button" class="path-button path-button--danger" :disabled="resignationPending" @click="resign">
                        {{ resignationPending ? "Démission…" : "Confirmer la démission" }}
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
