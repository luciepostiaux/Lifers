<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    jobs: { type: Array, default: () => [] },
    userDiplomas: { type: Array, default: () => [] },
    currentJob: Object,
    money: [String, Number],
});

const page = usePage();
const selectedJob = ref(null);
const dialogMode = ref(null);
const applicationPending = ref(false);

const diplomaIds = computed(
    () => new Set(props.userDiplomas.map((diploma) => diploma.id)),
);

const userCanApply = (job) =>
    !job.required_diploma_id || diplomaIds.value.has(job.required_diploma_id);

const availableJobs = computed(() =>
    props.jobs.filter((job) => userCanApply(job)),
);

const isCurrentJob = (job) => props.currentJob?.id === job.id;

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.errors?.job ??
        page.props.errors?.msg,
);

const jobStatus = (job) => {
    if (isCurrentJob(job)) return { label: "Métier actuel", tone: "active" };
    if (!userCanApply(job)) return { label: "Diplôme requis", tone: "locked" };

    return { label: "Accessible", tone: "available" };
};

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

const imageSource = (job) =>
    job.image_path || "/images/places/emploi.png";

const openDetails = (job) => {
    selectedJob.value = job;
    dialogMode.value = "details";
};

const requestApplication = (job) => {
    selectedJob.value = job;
    dialogMode.value = "application";
};

const closeDialog = () => {
    if (applicationPending.value) return;

    selectedJob.value = null;
    dialogMode.value = null;
};

const confirmApplication = () => {
    if (!selectedJob.value) return;

    applicationPending.value = true;
    router.post(
        route("job.apply", selectedJob.value.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                applicationPending.value = false;
                closeDialog();
            },
        },
    );
};
</script>

<template>
    <AppLayout title="Métier" :money="money">
        <div class="path-page">
            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="path-hero path-hero--job" aria-labelledby="job-title">
                <div class="path-hero__copy">
                    <span class="path-kicker">Vie professionnelle</span>
                    <h1 id="job-title">Choisis ton prochain métier</h1>
                    <p>
                        Consulte les métiers disponibles et vérifie les diplômes
                        nécessaires. Tous les 3 jours dans le même poste, ton
                        salaire augmente de 2 % grâce à l’ancienneté.
                    </p>
                    <div class="path-hero__stats" aria-label="Résumé des métiers">
                        <span><strong>{{ jobs.length }}</strong> métiers</span>
                        <span><strong>{{ availableJobs.length }}</strong> accessibles</span>
                        <span><strong>{{ userDiplomas.length }}</strong> diplômes obtenus</span>
                    </div>
                </div>
                <div class="path-hero__visual">
                    <img
                        src="/images/places/emploi.png"
                        alt="Espace consacré à la recherche d’un métier"
                        decoding="async"
                    />
                </div>
            </section>

            <section class="path-current" aria-labelledby="current-job-title">
                <div class="path-current__heading">
                    <div>
                        <span class="path-kicker">En ce moment</span>
                        <h2 id="current-job-title">Ton métier actuel</h2>
                    </div>
                    <span
                        class="path-badge"
                        :class="currentJob ? 'path-badge--active' : 'path-badge--neutral'"
                    >
                        {{ currentJob ? "En poste" : "Sans emploi" }}
                    </span>
                </div>

                <div v-if="currentJob" class="path-current__body">
                    <div>
                        <h3>{{ currentJob.name }}</h3>
                        <p>{{ currentJob.short_description }}</p>
                        <div class="path-meta-list">
                            <span>Salaire actuel : <strong>{{ formatAmount(currentJob.current_salary) }} Lif’coins</strong></span>
                            <span>Ancienneté : <strong>{{ currentJob.seniority_years }} an{{ currentJob.seniority_years > 1 ? "s" : "" }}</strong></span>
                            <span>Lieu : <strong>{{ currentJob.place?.name || "Non renseigné" }}</strong></span>
                            <span v-if="currentJob.next_raise_at">
                                Prochaine hausse : <strong>{{ formatDate(currentJob.next_raise_at) }}</strong>
                            </span>
                            <span v-else><strong>Augmentation maximale atteinte</strong></span>
                        </div>
                    </div>
                    <Link
                        :href="route('job.current.show', currentJob.id)"
                        class="path-button path-button--secondary"
                    >
                        Voir mon métier
                    </Link>
                </div>

                <div v-else class="path-empty">
                    <p>Ton Lifer n’exerce aucun métier pour le moment.</p>
                    <a href="#job-catalog" class="path-text-link">Consulter les offres</a>
                </div>
            </section>

            <section id="job-catalog" class="path-catalog" aria-labelledby="job-catalog-title">
                <div class="path-catalog__heading">
                    <div>
                        <span class="path-kicker">Catalogue</span>
                        <h2 id="job-catalog-title">Métiers disponibles</h2>
                    </div>
                    <p>Les métiers verrouillés deviendront accessibles après l’obtention du diplôme indiqué.</p>
                </div>

                <div class="path-grid">
                    <article v-for="job in jobs" :key="job.id" class="path-card">
                        <div class="path-card__visual">
                            <img :src="imageSource(job)" :alt="`Illustration du métier ${job.name}`" loading="lazy" />
                            <span class="path-badge" :class="`path-badge--${jobStatus(job).tone}`">
                                {{ jobStatus(job).label }}
                            </span>
                        </div>

                        <div class="path-card__content">
                            <div>
                                <span class="path-card__place">{{ job.place?.name || "Lieu non renseigné" }}</span>
                                <h3>{{ job.name }}</h3>
                                <p>{{ job.short_description }}</p>
                            </div>

                            <dl class="path-card__facts path-card__facts--job">
                                <div><dt>Salaire de départ</dt><dd>{{ formatAmount(job.salary) }} Lif’coins</dd></div>
                                <div><dt>Prérequis</dt><dd>{{ job.required_diploma?.name || "Aucun" }}</dd></div>
                            </dl>

                            <div class="path-card__actions">
                                <Link
                                    v-if="isCurrentJob(job)"
                                    :href="route('job.current.show', job.id)"
                                    class="path-button path-button--secondary path-button--full"
                                >
                                    Voir mon métier
                                </Link>
                                <template v-else>
                                    <button type="button" class="path-button path-button--ghost" @click="openDetails(job)">
                                        Détails
                                    </button>
                                    <button
                                        type="button"
                                        class="path-button path-button--primary"
                                        :disabled="!userCanApply(job)"
                                        @click="requestApplication(job)"
                                    >
                                        {{ currentJob ? "Choisir ce métier" : "Postuler" }}
                                    </button>
                                </template>
                            </div>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <div
            v-if="selectedJob && dialogMode"
            class="path-dialog-backdrop"
            role="presentation"
            @click.self="closeDialog"
            @keydown.esc="closeDialog"
        >
            <section class="path-dialog" role="dialog" aria-modal="true" aria-labelledby="job-dialog-title">
                <span class="path-kicker">{{ dialogMode === "details" ? "Détails du métier" : "Confirmation" }}</span>
                <h2 id="job-dialog-title">
                    {{ dialogMode === "details" ? selectedJob.name : currentJob ? "Changer de métier ?" : "Postuler à ce métier ?" }}
                </h2>

                <template v-if="dialogMode === 'details'">
                    <p>{{ selectedJob.long_description || selectedJob.short_description }}</p>
                    <dl class="path-dialog__facts">
                        <div><dt>Salaire de départ</dt><dd>{{ formatAmount(selectedJob.salary) }} Lif’coins</dd></div>
                        <div><dt>Lieu</dt><dd>{{ selectedJob.place?.name || "Non renseigné" }}</dd></div>
                        <div><dt>Diplôme requis</dt><dd>{{ selectedJob.required_diploma?.name || "Aucun" }}</dd></div>
                    </dl>
                </template>
                <p v-else>
                    Tu vas choisir <strong>{{ selectedJob.name }}</strong>.
                    <template v-if="currentJob">
                        Ce métier remplacera ton poste actuel de {{ currentJob.name }}.
                    </template>
                </p>

                <div class="path-dialog__actions">
                    <button type="button" class="path-button path-button--ghost" :disabled="applicationPending" @click="closeDialog">
                        {{ dialogMode === "details" ? "Fermer" : "Annuler" }}
                    </button>
                    <button
                        v-if="dialogMode === 'application'"
                        type="button"
                        class="path-button path-button--primary"
                        :disabled="applicationPending"
                        @click="confirmApplication"
                    >
                        {{ applicationPending ? "Validation…" : "Confirmer" }}
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
