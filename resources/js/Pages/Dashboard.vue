<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    perso: Object,
    bodyImageUrl: String,
    money: [String, Number],
    age: Number,
    lifeGauges: Object,
    currentSicknesses: {
        type: Array,
        default: () => [],
    },
    studyDetails: Object,
    jobDetails: Object,
});

const vitalGaugeLabels = ["Faim", "Soif", "Santé"];

const safeGaugeValue = (value) => {
    const numericValue = Number(value);

    if (!Number.isFinite(numericValue)) {
        return 0;
    }

    return Math.min(100, Math.max(0, numericValue));
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

const gaugeGroups = computed(() => [
    {
        title: "Vital",
        gauges: gauges.value.filter((gauge) =>
            vitalGaugeLabels.includes(gauge.label),
        ),
    },
    {
        title: "Équilibre",
        gauges: gauges.value.filter(
            (gauge) => !vitalGaugeLabels.includes(gauge.label),
        ),
    },
]);

const lowestGauge = computed(() => {
    if (gauges.value.length === 0) {
        return null;
    }

    return gauges.value.reduce((lowest, gauge) =>
        gauge.value < lowest.value ? gauge : lowest,
    );
});

const hasActiveSicknesses = computed(() => props.currentSicknesses.length > 0);

const sicknessNames = computed(() =>
    props.currentSicknesses.map((sickness) => sickness.name).join(", "),
);

const globalStatus = computed(() => {
    if (hasActiveSicknesses.value) {
        const count = props.currentSicknesses.length;

        return {
            label: `${count} maladie${count > 1 ? "s" : ""} active${count > 1 ? "s" : ""}`,
            tone: "sickness",
        };
    }

    const lowest = lowestGauge.value;

    if (!lowest) {
        return {
            label: "État indisponible",
            tone: "watch",
        };
    }

    if (lowest.value <= 15) {
        return {
            label: "À surveiller",
            tone: "priority",
        };
    }

    if (lowest.value <= 60) {
        return {
            label: "À anticiper",
            tone: "watch",
        };
    }

    return {
        label: "En forme",
        tone: "stable",
    };
});

const todayMessage = computed(() => {
    if (hasActiveSicknesses.value) {
        if (props.currentSicknesses.length === 1) {
            return `${props.currentSicknesses[0].name} affecte actuellement ton Lifer.`;
        }

        return `${props.currentSicknesses.length} maladies actives demandent ton attention aujourd’hui.`;
    }

    const lowest = lowestGauge.value;

    if (!lowest) {
        return "Les jauges de ton Lifer ne sont pas disponibles pour le moment.";
    }

    if (lowest.value <= 15) {
        return `${lowest.label} demande ton attention en priorité (${lowest.value}/100).`;
    }

    if (lowest.value <= 60) {
        return `${lowest.label} est le prochain besoin à anticiper (${lowest.value}/100).`;
    }

    return "Les besoins de ton Lifer sont stables aujourd’hui.";
});

const characterName = computed(() =>
    [props.perso?.first_name, props.perso?.last_name]
        .filter(Boolean)
        .join(" "),
);

const bodyImageSrc = computed(() => {
    if (!props.bodyImageUrl) return null;

    return props.bodyImageUrl.startsWith("/")
        ? props.bodyImageUrl
        : `/${props.bodyImageUrl}`;
});

const formatDate = (date) => {
    if (!date) return null;

    const value = String(date);
    const containsTime = /T\d{2}:\d{2}/.test(value);
    const parsedDate = new Date(
        /^\d{4}-\d{2}-\d{2}$/.test(value) ? `${value}T12:00:00` : value,
    );

    if (Number.isNaN(parsedDate.getTime())) return "Date indisponible";

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        ...(containsTime
            ? {
                  hour: "2-digit",
                  minute: "2-digit",
              }
            : {}),
    }).format(parsedDate);
};
</script>

<template>
    <AppLayout title="Tableau de bord" :money="money">
        <div class="dashboard-page">
            <section class="dashboard-hero" aria-labelledby="lifer-name">
                <div class="dashboard-hero__character">
                    <div class="dashboard-hero__portrait">
                        <img
                            v-if="bodyImageSrc"
                            :src="bodyImageSrc"
                            :alt="`Lifer ${characterName}`"
                            decoding="async"
                        />
                        <div v-else class="dashboard-hero__portrait-empty">
                            Visuel indisponible
                        </div>
                    </div>

                    <div class="dashboard-hero__identity">
                        <span class="dashboard-hero__eyebrow">Ton Lifer</span>
                        <h1 id="lifer-name" class="dashboard-hero__name">
                            {{ characterName }}
                        </h1>
                        <div class="dashboard-hero__meta">
                            <span v-if="age !== null">{{ age }} ans</span>
                        </div>
                    </div>
                </div>

                <div class="dashboard-hero__state">
                    <div class="dashboard-hero__state-heading">
                        <div>
                            <span class="dashboard-section-kicker">État actuel</span>
                            <h2>Les besoins essentiels</h2>
                        </div>
                        <span
                            class="dashboard-status"
                            :class="`dashboard-status--${globalStatus.tone}`"
                        >
                            {{ globalStatus.label }}
                        </span>
                    </div>

                    <div
                        v-if="hasActiveSicknesses"
                        class="dashboard-sickness-summary"
                        role="status"
                    >
                        <div>
                            <span>
                                Maladie{{ currentSicknesses.length > 1 ? "s" : "" }}
                                active{{ currentSicknesses.length > 1 ? "s" : "" }}
                            </span>
                            <strong>{{ sicknessNames }}</strong>
                        </div>
                        <Link :href="route('doctor.index')">
                            Voir les soins
                        </Link>
                    </div>

                    <div
                        v-if="gauges.length"
                        class="dashboard-gauge-groups"
                    >
                        <section
                            v-for="group in gaugeGroups"
                            :key="group.title"
                            class="dashboard-gauge-group"
                            :aria-labelledby="`gauge-group-${group.title}`"
                        >
                            <h3 :id="`gauge-group-${group.title}`">
                                {{ group.title }}
                            </h3>

                            <div class="dashboard-gauge-list">
                                <div
                                    v-for="gauge in group.gauges"
                                    :key="gauge.label"
                                    class="dashboard-gauge"
                                >
                                    <div class="dashboard-gauge__label-row">
                                        <span>{{ gauge.label }}</span>
                                        <strong>{{ gauge.value }}/100</strong>
                                    </div>
                                    <div
                                        class="dashboard-gauge__track"
                                        role="progressbar"
                                        :aria-label="gauge.label"
                                        :aria-valuenow="gauge.value"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    >
                                        <span
                                            class="dashboard-gauge__value"
                                            :class="`dashboard-gauge__value--${gauge.tone}`"
                                            :style="{ width: `${gauge.value}%` }"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>

                    <p v-else class="dashboard-empty-state">
                        Les jauges de vie ne sont pas disponibles.
                    </p>
                </div>
            </section>

            <div class="dashboard-card-row">
                <section class="dashboard-card dashboard-card--today">
                    <div class="dashboard-card__heading">
                        <div>
                            <span class="dashboard-section-kicker">Tes priorités</span>
                            <h2>Aujourd’hui</h2>
                        </div>
                        <span
                            class="dashboard-card__tone-dot"
                            :class="`dashboard-card__tone-dot--${globalStatus.tone}`"
                            aria-hidden="true"
                        ></span>
                    </div>
                    <p>{{ todayMessage }}</p>
                    <div
                        v-if="hasActiveSicknesses"
                        class="dashboard-priority-sicknesses"
                        aria-label="Maladies actives"
                    >
                        <span
                            v-for="sickness in currentSicknesses"
                            :key="sickness.id"
                        >
                            {{ sickness.name }}
                        </span>
                    </div>
                    <Link
                        :href="hasActiveSicknesses ? route('doctor.index') : route('athome')"
                        class="dashboard-text-link"
                    >
                        {{ hasActiveSicknesses ? "Consulter les soins" : "Prendre soin de mon Lifer" }}
                    </Link>
                </section>

                <section class="dashboard-card dashboard-card--community">
                    <div class="dashboard-card__heading">
                        <div>
                            <span class="dashboard-section-kicker">Vie sociale</span>
                            <h2>Communauté</h2>
                        </div>
                        <span
                            class="dashboard-card__tone-dot dashboard-card__tone-dot--community"
                            aria-hidden="true"
                        ></span>
                    </div>
                    <p>
                        Retrouve les autres Lifers et accède à tes espaces de
                        discussion.
                    </p>
                    <Link :href="route('social')" class="dashboard-text-link">
                        Ouvrir la communauté
                    </Link>
                </section>
            </div>

            <section class="dashboard-path" aria-labelledby="path-title">
                <div class="dashboard-path__heading">
                    <span class="dashboard-section-kicker">Progression</span>
                    <h2 id="path-title">Ton parcours</h2>
                </div>

                <div class="dashboard-path__items">
                    <article class="dashboard-path__item">
                        <div>
                            <span class="dashboard-path__label">Études</span>
                            <h3 v-if="studyDetails">{{ studyDetails.name }}</h3>
                            <h3 v-else>Aucune étude en cours</h3>
                            <p v-if="studyDetails?.end_date">
                                Fin prévue le {{ formatDate(studyDetails.end_date) }}
                            </p>
                            <p v-else-if="studyDetails">
                                Formation actuellement suivie
                            </p>
                            <p v-else>Choisis une formation pour progresser.</p>
                        </div>
                        <Link
                            :href="route('study.index')"
                            class="dashboard-path__action"
                        >
                            Gérer mes études
                        </Link>
                    </article>

                    <article class="dashboard-path__item">
                        <div>
                            <span class="dashboard-path__label">Métier</span>
                            <h3 v-if="jobDetails">{{ jobDetails.name }}</h3>
                            <h3 v-else>Aucun métier actif</h3>
                            <p v-if="jobDetails">
                                Activité professionnelle actuelle
                            </p>
                            <p v-else>Trouve un métier adapté à ton parcours.</p>
                        </div>
                        <Link :href="route('job')" class="dashboard-path__action">
                            Gérer mon métier
                        </Link>
                    </article>
                </div>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.dashboard-page {
    display: grid;
    width: min(100%, 1420px);
    margin-inline: auto;
    padding: clamp(22px, 2.5vw, 36px) clamp(20px, 3vw, 42px) 48px;
    gap: 20px;
}

.dashboard-hero,
.dashboard-card,
.dashboard-path {
    border: 1px solid rgb(70 50 78 / 7%);
    background: #f8f3ec;
    box-shadow:
        0 1px 0 rgb(70 50 78 / 4%),
        0 10px 28px rgb(70 50 78 / 8%);
}

.dashboard-hero {
    display: grid;
    min-height: 332px;
    border-radius: 22px;
    grid-template-columns: minmax(260px, 0.72fr) minmax(520px, 1.28fr);
    overflow: hidden;
}

.dashboard-hero__character {
    position: relative;
    display: grid;
    min-width: 0;
    padding: 24px 24px 20px;
    grid-template-columns: minmax(120px, 0.8fr) minmax(130px, 1fr);
    align-items: end;
    gap: 18px;
    background:
        radial-gradient(circle at 20% 18%, rgb(214 168 74 / 19%), transparent 38%),
        linear-gradient(145deg, rgb(111 146 123 / 15%), transparent 70%);
}

.dashboard-hero__portrait {
    display: flex;
    height: 282px;
    align-items: flex-end;
    justify-content: center;
    overflow: hidden;
}

.dashboard-hero__portrait img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center bottom;
}

.dashboard-hero__portrait-empty {
    display: flex;
    width: 100%;
    height: 86%;
    border: 1px dashed rgb(70 50 78 / 22%);
    border-radius: 16px;
    align-items: center;
    justify-content: center;
    color: rgb(70 50 78 / 58%);
    font-size: 13px;
    text-align: center;
}

.dashboard-hero__identity {
    min-width: 0;
    padding-bottom: 22px;
}

.dashboard-hero__eyebrow,
.dashboard-section-kicker,
.dashboard-path__label {
    color: #6f927b;
    font-size: 11px;
    font-weight: 700;
    line-height: 1.2;
    letter-spacing: 0.11em;
    text-transform: uppercase;
}

.dashboard-hero__name {
    margin: 7px 0 0;
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(30px, 3vw, 44px);
    font-weight: 700;
    line-height: 0.98;
    letter-spacing: -0.035em;
    overflow-wrap: anywhere;
}

.dashboard-hero__meta {
    display: flex;
    margin-top: 14px;
    flex-wrap: wrap;
    gap: 8px;
}

.dashboard-hero__meta span {
    display: inline-flex;
    min-height: 30px;
    padding: 5px 10px;
    border-radius: 9px;
    align-items: center;
    color: #46324e;
    background: rgb(244 238 229 / 82%);
    font-size: 12px;
    font-weight: 700;
}

.dashboard-hero__state {
    min-width: 0;
    padding: 26px clamp(24px, 3vw, 38px);
}

.dashboard-hero__state-heading,
.dashboard-card__heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 18px;
}

.dashboard-hero__state-heading h2,
.dashboard-card__heading h2,
.dashboard-path__heading h2 {
    margin: 4px 0 0;
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-weight: 700;
    line-height: 1.05;
    letter-spacing: -0.025em;
}

.dashboard-hero__state-heading h2 {
    font-size: 28px;
}

.dashboard-status {
    display: inline-flex;
    min-height: 34px;
    padding: 7px 12px;
    border-radius: 999px;
    align-items: center;
    font-size: 12px;
    font-weight: 800;
    white-space: nowrap;
}

.dashboard-status--stable {
    color: #385443;
    background: rgb(111 146 123 / 20%);
}

.dashboard-status--watch {
    color: #705315;
    background: rgb(214 168 74 / 24%);
}

.dashboard-status--priority {
    color: #743344;
    background: rgb(217 142 155 / 24%);
}

.dashboard-status--sickness {
    color: #743344;
    background: rgb(217 142 155 / 30%);
}

.dashboard-sickness-summary {
    display: flex;
    min-height: 58px;
    margin-top: 18px;
    padding: 11px 14px;
    border: 1px solid rgb(174 89 107 / 18%);
    border-radius: 13px;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    color: #743344;
    background: rgb(217 142 155 / 13%);
}

.dashboard-sickness-summary div {
    display: grid;
    min-width: 0;
    gap: 2px;
}

.dashboard-sickness-summary span {
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 0.09em;
    text-transform: uppercase;
}

.dashboard-sickness-summary strong {
    color: #46324e;
    font-size: 13px;
    line-height: 1.35;
}

.dashboard-sickness-summary a {
    flex: 0 0 auto;
    color: #743344;
    font-size: 12px;
    font-weight: 800;
    text-decoration-line: underline;
    text-decoration-thickness: 2px;
    text-underline-offset: 4px;
}

.dashboard-gauge-groups {
    display: grid;
    margin-top: 20px;
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.1fr);
    gap: 16px;
}

.dashboard-gauge-group {
    padding: 16px;
    border: 1px solid rgb(70 50 78 / 7%);
    border-radius: 15px;
    background: #fcf8f2;
}

.dashboard-gauge-group h3 {
    margin: 0 0 12px;
    color: #46324e;
    font-size: 13px;
    font-weight: 800;
}

.dashboard-gauge-list {
    display: grid;
    gap: 10px;
}

.dashboard-gauge__label-row {
    display: flex;
    margin-bottom: 5px;
    align-items: baseline;
    justify-content: space-between;
    gap: 10px;
    color: #46324e;
    font-size: 11px;
    font-weight: 600;
}

.dashboard-gauge__label-row strong {
    font-size: 10px;
}

.dashboard-gauge__track {
    height: 7px;
    overflow: hidden;
    border-radius: 999px;
    background: rgb(70 50 78 / 10%);
}

.dashboard-gauge__value {
    display: block;
    height: 100%;
    min-width: 3px;
    border-radius: inherit;
}

.dashboard-gauge__value--stable {
    background: #6f927b;
}

.dashboard-gauge__value--watch {
    background: #d6a84a;
}

.dashboard-gauge__value--priority {
    background: #d98e9b;
}

.dashboard-empty-state {
    margin: 26px 0 0;
    color: rgb(70 50 78 / 68%);
    font-size: 14px;
}

.dashboard-card-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 20px;
}

.dashboard-card {
    display: flex;
    min-height: 184px;
    padding: 22px 24px;
    border-radius: 18px;
    flex-direction: column;
}

.dashboard-card--today {
    border-top: 4px solid #d6a84a;
}

.dashboard-card--community {
    border-top: 4px solid #d98e9b;
}

.dashboard-card__heading h2,
.dashboard-path__heading h2 {
    font-size: 25px;
}

.dashboard-card__tone-dot {
    width: 12px;
    height: 12px;
    margin-top: 8px;
    border-radius: 50%;
}

.dashboard-card__tone-dot--stable {
    background: #6f927b;
}

.dashboard-card__tone-dot--watch {
    background: #d6a84a;
}

.dashboard-card__tone-dot--priority,
.dashboard-card__tone-dot--community {
    background: #d98e9b;
}

.dashboard-card p {
    max-width: 560px;
    margin: 18px 0 16px;
    color: rgb(70 50 78 / 78%);
    font-size: 14px;
    line-height: 1.5;
}

.dashboard-priority-sicknesses {
    display: flex;
    margin: -5px 0 15px;
    flex-wrap: wrap;
    gap: 7px;
}

.dashboard-priority-sicknesses span {
    display: inline-flex;
    min-height: 28px;
    padding: 5px 9px;
    border-radius: 999px;
    align-items: center;
    color: #743344;
    background: rgb(217 142 155 / 17%);
    font-size: 11px;
    font-weight: 800;
}

.dashboard-text-link,
.dashboard-path__action {
    display: inline-flex;
    min-height: 44px;
    align-items: center;
    color: #46324e;
    font-size: 13px;
    font-weight: 800;
    text-decoration-line: underline;
    text-decoration-color: #d6a84a;
    text-decoration-thickness: 2px;
    text-underline-offset: 5px;
}

.dashboard-text-link {
    margin-top: auto;
    align-self: flex-start;
}

.dashboard-path {
    display: grid;
    min-height: 142px;
    padding: 22px 24px;
    border-radius: 18px;
    grid-template-columns: minmax(150px, 0.34fr) minmax(0, 1.66fr);
    align-items: center;
    gap: 24px;
}

.dashboard-path__items {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.dashboard-path__item {
    display: flex;
    min-width: 0;
    min-height: 96px;
    padding: 4px 22px;
    flex-direction: column;
    justify-content: space-between;
    gap: 10px;
}

.dashboard-path__item + .dashboard-path__item {
    border-left: 1px solid rgb(70 50 78 / 10%);
}

.dashboard-path__item h3 {
    margin: 5px 0 0;
    color: #46324e;
    font-size: 15px;
    font-weight: 800;
    line-height: 1.3;
}

.dashboard-path__item p {
    margin: 4px 0 0;
    color: rgb(70 50 78 / 66%);
    font-size: 12px;
    line-height: 1.4;
}

.dashboard-path__action {
    align-self: flex-start;
}

.dashboard-text-link:focus-visible,
.dashboard-path__action:focus-visible {
    outline: 3px solid #46324e;
    outline-offset: 4px;
    border-radius: 4px;
}

@media (max-width: 1240px) {
    .dashboard-hero {
        grid-template-columns: minmax(240px, 0.62fr) minmax(470px, 1.38fr);
    }

    .dashboard-hero__character {
        grid-template-columns: 1fr;
        align-content: end;
    }

    .dashboard-hero__portrait {
        height: 220px;
    }

    .dashboard-hero__identity {
        padding-bottom: 0;
    }
}

@media (max-width: 899px) {
    .dashboard-hero {
        grid-template-columns: 1fr;
    }

    .dashboard-hero__character {
        grid-template-columns: minmax(150px, 0.7fr) minmax(160px, 1fr);
    }

    .dashboard-hero__portrait {
        height: 260px;
    }

    .dashboard-hero__identity {
        padding-bottom: 24px;
    }

    .dashboard-path {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 639px) {
    .dashboard-page {
        padding: 18px 16px 36px;
        gap: 16px;
    }

    .dashboard-hero,
    .dashboard-card,
    .dashboard-path {
        border-radius: 16px;
    }

    .dashboard-hero__character {
        padding: 20px 18px 18px;
        grid-template-columns: minmax(110px, 0.72fr) minmax(130px, 1fr);
    }

    .dashboard-hero__portrait {
        height: 210px;
    }

    .dashboard-hero__identity {
        padding-bottom: 18px;
    }

    .dashboard-hero__name {
        font-size: 30px;
    }

    .dashboard-hero__state {
        padding: 22px 18px;
    }

    .dashboard-hero__state-heading {
        align-items: flex-start;
        flex-direction: column;
        gap: 12px;
    }

    .dashboard-sickness-summary {
        align-items: flex-start;
        flex-direction: column;
        gap: 9px;
    }

    .dashboard-gauge-groups,
    .dashboard-card-row,
    .dashboard-path__items {
        grid-template-columns: 1fr;
    }

    .dashboard-card {
        min-height: 174px;
        padding: 20px;
    }

    .dashboard-path {
        padding: 20px;
    }

    .dashboard-path__item {
        padding: 14px 0;
    }

    .dashboard-path__item + .dashboard-path__item {
        border-top: 1px solid rgb(70 50 78 / 10%);
        border-left: 0;
    }
}
</style>
