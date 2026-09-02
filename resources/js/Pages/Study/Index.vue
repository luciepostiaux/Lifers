<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    studies: { type: Array, default: () => [] },
    currentStudy: Object,
    persoDiplomas: { type: Array, default: () => [] },
    money: [String, Number],
});

const page = usePage();
const studyToEnroll = ref(null);
const enrollmentPending = ref(false);

const diplomaIds = computed(
    () => new Set(props.persoDiplomas.map((diploma) => diploma.id)),
);

const hasDiploma = (study) => diplomaIds.value.has(study.awarded_diploma_id);
const hasRequiredDiploma = (study) =>
    !study.required_diploma_id || diplomaIds.value.has(study.required_diploma_id);
const userCanEnroll = (study) => hasRequiredDiploma(study) && !hasDiploma(study);
const canAfford = (study) => Number(props.money) >= Number(study.price);
const isCurrentStudy = (study) => props.currentStudy?.id === study.id;

const affordableStudies = computed(() =>
    props.studies.filter(
        (study) => userCanEnroll(study) && canAfford(study),
    ),
);

const studyProgress = computed(() => {
    if (!props.currentStudy?.start_date || !props.currentStudy?.end_date) return 0;

    const start = new Date(props.currentStudy.start_date).getTime();
    const end = new Date(props.currentStudy.end_date).getTime();

    if (![start, end].every(Number.isFinite) || end <= start) return 0;

    return Math.min(
        100,
        Math.max(0, Math.round(((Date.now() - start) / (end - start)) * 100)),
    );
});

const remainingDays = computed(() => {
    if (!props.currentStudy?.end_date) return null;

    return Math.max(
        0,
        Math.ceil(
            (new Date(props.currentStudy.end_date).getTime() - Date.now()) /
                86400000,
        ),
    );
});

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.errors?.study ??
        page.props.errors?.msg,
);

const studyStatus = (study) => {
    if (isCurrentStudy(study)) return { label: "En cours", tone: "active" };
    if (hasDiploma(study)) return { label: "Diplôme obtenu", tone: "complete" };
    if (!hasRequiredDiploma(study)) {
        return { label: "Prérequis manquant", tone: "locked" };
    }
    if (!canAfford(study)) return { label: "Budget insuffisant", tone: "locked" };

    return { label: "Accessible", tone: "available" };
};

const formatDate = (date) => {
    if (!date) return "Non renseignée";

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(date));
};

const formatAmount = (amount) =>
    new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(
        Number(amount),
    );

const imageSource = (study) =>
    study.image_path || "/images/places/universite.png";

const closeEnrollmentDialog = () => {
    if (!enrollmentPending.value) studyToEnroll.value = null;
};

const confirmEnrollment = () => {
    if (!studyToEnroll.value) return;

    enrollmentPending.value = true;
    router.post(
        route("study.enroll", { studyId: studyToEnroll.value.id }),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                enrollmentPending.value = false;
                studyToEnroll.value = null;
            },
        },
    );
};
</script>

<template>
    <AppLayout title="Études" :money="money">
        <div class="path-page">
            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="path-hero" aria-labelledby="study-title">
                <div class="path-hero__copy">
                    <span class="path-kicker">Progression</span>
                    <h1 id="study-title">Construis ton parcours d’études</h1>
                    <p>
                        Choisis une formation compatible avec tes diplômes et ton
                        budget. Une seule étude peut être suivie à la fois.
                    </p>
                    <div class="path-hero__stats" aria-label="Résumé des études">
                        <span><strong>{{ studies.length }}</strong> formations</span>
                        <span><strong>{{ persoDiplomas.length }}</strong> diplômes obtenus</span>
                        <span><strong>{{ affordableStudies.length }}</strong> accessibles maintenant</span>
                    </div>
                </div>
                <div class="path-hero__visual">
                    <img
                        src="/images/places/universite.png"
                        alt="Bâtiment de l’université"
                        decoding="async"
                    />
                </div>
            </section>

            <section class="path-current" aria-labelledby="current-study-title">
                <div class="path-current__heading">
                    <div>
                        <span class="path-kicker">En ce moment</span>
                        <h2 id="current-study-title">Ton étude actuelle</h2>
                    </div>
                    <span
                        class="path-badge"
                        :class="currentStudy ? 'path-badge--active' : 'path-badge--neutral'"
                    >
                        {{ currentStudy ? "En cours" : "Aucune étude" }}
                    </span>
                </div>

                <div v-if="currentStudy" class="path-current__body">
                    <div>
                        <h3>{{ currentStudy.name }}</h3>
                        <p>{{ currentStudy.description }}</p>
                        <div class="path-meta-list">
                            <span v-if="currentStudy.awarded_diploma">
                                Diplôme préparé : <strong>{{ currentStudy.awarded_diploma.name }}</strong>
                            </span>
                            <span>
                                Fin prévue : <strong>{{ formatDate(currentStudy.end_date) }}</strong>
                            </span>
                        </div>
                    </div>
                    <div class="path-progress-block">
                        <div class="path-progress-block__label">
                            <span>Progression</span>
                            <strong>{{ studyProgress }} %</strong>
                        </div>
                        <div
                            class="path-progress"
                            role="progressbar"
                            aria-label="Progression de l’étude"
                            :aria-valuenow="studyProgress"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >
                            <span :style="{ width: `${studyProgress}%` }"></span>
                        </div>
                        <small v-if="remainingDays !== null">
                            {{ remainingDays === 0 ? "Diplôme prêt à être récupéré" : `${remainingDays} jour${remainingDays > 1 ? "s" : ""} restant${remainingDays > 1 ? "s" : ""}` }}
                        </small>
                        <Link
                            :href="route('study.current.show', currentStudy.id)"
                            class="path-button path-button--secondary"
                        >
                            Voir mon étude
                        </Link>
                    </div>
                </div>

                <div v-else class="path-empty">
                    <p>Tu ne suis aucune étude pour le moment.</p>
                    <a href="#study-catalog" class="path-text-link">Découvrir les formations</a>
                </div>
            </section>

            <section id="study-catalog" class="path-catalog" aria-labelledby="study-catalog-title">
                <div class="path-catalog__heading">
                    <div>
                        <span class="path-kicker">Catalogue</span>
                        <h2 id="study-catalog-title">Formations disponibles</h2>
                    </div>
                    <p>Le diplôme requis et le coût sont vérifiés avant chaque inscription.</p>
                </div>

                <div class="path-grid">
                    <article v-for="study in studies" :key="study.id" class="path-card">
                        <div class="path-card__visual">
                            <img :src="imageSource(study)" :alt="`Illustration de ${study.name}`" loading="lazy" />
                            <span class="path-badge" :class="`path-badge--${studyStatus(study).tone}`">
                                {{ studyStatus(study).label }}
                            </span>
                        </div>

                        <div class="path-card__content path-card__content--study">
                            <div>
                                <span class="path-card__place">{{ study.place?.name || "Université" }}</span>
                                <h3>{{ study.name }}</h3>
                                <p>{{ study.short_description }}</p>
                            </div>

                            <dl class="path-card__facts path-card__facts--study">
                                <div><dt>Durée</dt><dd>{{ study.duration_days }} jours</dd></div>
                                <div><dt>Coût</dt><dd>{{ formatAmount(study.price) }} Lif’coins</dd></div>
                                <div><dt>Diplôme obtenu</dt><dd>{{ study.awarded_diploma?.name || "Non renseigné" }}</dd></div>
                                <div><dt>Prérequis</dt><dd>{{ study.required_diploma?.name || "Aucun" }}</dd></div>
                            </dl>

                            <Link
                                v-if="isCurrentStudy(study)"
                                :href="route('study.current.show', study.id)"
                                class="path-button path-button--secondary path-button--full"
                            >
                                Voir l’étude en cours
                            </Link>
                            <button
                                v-else
                                type="button"
                                class="path-button path-button--primary path-button--full"
                                :disabled="!userCanEnroll(study) || !canAfford(study)"
                                @click="studyToEnroll = study"
                            >
                                {{ currentStudy ? "Changer pour cette étude" : "Commencer cette étude" }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>
        </div>

        <div
            v-if="studyToEnroll"
            class="path-dialog-backdrop"
            role="presentation"
            @click.self="closeEnrollmentDialog"
            @keydown.esc="closeEnrollmentDialog"
        >
            <section class="path-dialog" role="dialog" aria-modal="true" aria-labelledby="study-dialog-title">
                <span class="path-kicker">Confirmation</span>
                <h2 id="study-dialog-title">{{ currentStudy ? "Changer d’étude ?" : "Commencer cette étude ?" }}</h2>
                <p>
                    Tu vas t’inscrire à <strong>{{ studyToEnroll.name }}</strong> pour
                    {{ formatAmount(studyToEnroll.price) }} Lif’coins.
                    <template v-if="currentStudy">
                        Ton étude actuelle sera quittée et sa progression sera perdue.
                    </template>
                </p>
                <div class="path-dialog__actions">
                    <button type="button" class="path-button path-button--ghost" :disabled="enrollmentPending" @click="closeEnrollmentDialog">Annuler</button>
                    <button type="button" class="path-button path-button--primary" :disabled="enrollmentPending" @click="confirmEnrollment">
                        {{ enrollmentPending ? "Inscription…" : "Confirmer" }}
                    </button>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
