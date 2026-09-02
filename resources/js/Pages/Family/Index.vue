<script setup>
import { computed, ref } from "vue";
import { Link, router, useForm, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    money: [String, Number],
    currentLifer: { type: Object, required: true },
    parents: { type: Array, default: () => [] },
    spouse: { type: Object, default: null },
    receivedRequests: { type: Array, default: () => [] },
    sentRequests: { type: Array, default: () => [] },
    pregnancies: { type: Array, default: () => [] },
    children: { type: Array, default: () => [] },
    otherLifers: { type: Array, default: () => [] },
    actionStatus: { type: Object, required: true },
});
const page = usePage();

const selectedLiferId = ref(
    props.spouse?.id
    ?? props.otherLifers.find((lifer) => lifer.is_favorite)?.id
    ?? "",
);
const liferSearch = ref("");
const respondingRequestId = ref(null);
const cancellingRequestId = ref(null);
const favoriteLiferId = ref(null);
const divorcing = ref(false);
const namingChildId = ref(null);
const caringChildId = ref(null);
const caringAll = ref(false);
const changingCustodyChildId = ref(null);
const namingErrors = ref({});
const childNames = ref(
    Object.fromEntries(
        props.pregnancies.flatMap((pregnancy) =>
            pregnancy.children.map((child) => [
                child.id,
                {
                    first_name: child.first_name ?? "",
                    last_name: child.last_name ?? pregnancy.available_last_names[0] ?? "",
                },
            ]),
        ),
    ),
);
const requestForm = useForm({
    recipient_lifer_id: selectedLiferId.value,
    type: "marriage",
});

const selectedLifer = computed(() =>
    props.otherLifers.find((lifer) => Number(lifer.id) === Number(selectedLiferId.value)),
);

const normalizedSearch = computed(() =>
    liferSearch.value
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .trim()
        .toLocaleLowerCase("fr-FR"),
);

const quickLifers = computed(() =>
    [...props.otherLifers]
        .filter((lifer) => lifer.is_favorite || (props.spouse && lifer.id === props.spouse.id))
        .sort((first, second) => {
            if (props.spouse && first.id === props.spouse.id) return -1;
            if (props.spouse && second.id === props.spouse.id) return 1;
            return first.name.localeCompare(second.name, "fr");
        }),
);

const filteredLifers = computed(() => {
    if (!normalizedSearch.value) return [];

    return [...props.otherLifers]
        .filter((lifer) => {
            return lifer.name
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .toLocaleLowerCase("fr-FR")
                .includes(normalizedSearch.value);
        })
        .sort((first, second) => {
            if (first.is_favorite !== second.is_favorite) return first.is_favorite ? -1 : 1;
            return first.name.localeCompare(second.name, "fr");
        });
});

const protectedRemaining = computed(() =>
    Math.max(0, props.actionStatus.daily_limit - props.actionStatus.protected_used),
);
const protectedRequestSlots = computed(() =>
    Math.max(0, protectedRemaining.value - props.actionStatus.protected_pending),
);

const babyAttemptsRemaining = computed(() =>
    Math.max(0, props.actionStatus.daily_limit - props.actionStatus.baby_attempts_used),
);
const babyRequestSlots = computed(() =>
    Math.max(0, babyAttemptsRemaining.value - props.actionStatus.baby_attempts_pending),
);
const childrenNeedingCare = computed(() =>
    props.children.filter((child) =>
        child.gauges && Object.values(child.gauges).some((value) => Number(value) < 100),
    ),
);
const careAllCost = computed(() =>
    childrenNeedingCare.value.length * (props.actionStatus.feed_cost + props.actionStatus.wash_cost),
);
const careError = computed(() => page.props.errors?.care);

const requestLabels = {
    marriage: "Demande en mariage",
    intimacy_protected: "Moment protégé",
    baby_attempt: "Tentative de bébé",
    child_abandonment: "Demande d’abandon",
};

function sendRequest(type) {
    requestForm.recipient_lifer_id = selectedLiferId.value;
    requestForm.type = type;
    requestForm.post(route("family.requests.store"), {
        preserveScroll: true,
    });
}

function respondToRequest(requestId, accepted) {
    respondingRequestId.value = requestId;
    router.patch(
        route("family.requests.respond", requestId),
        { accepted },
        {
            preserveScroll: true,
            onFinish: () => {
                respondingRequestId.value = null;
            },
        },
    );
}

function cancelRequest(request) {
    if (!window.confirm(`Annuler la demande « ${requestLabels[request.type]} » envoyée à ${request.other_lifer.name} ?`)) {
        return;
    }

    cancellingRequestId.value = request.id;
    router.delete(route("family.requests.cancel", request.id), {
        preserveScroll: true,
        onFinish: () => {
            cancellingRequestId.value = null;
        },
    });
}

function divorce() {
    if (!props.spouse || !window.confirm(`Divorcer de ${props.spouse.name} ? Les gardes existantes des enfants seront conservées.`)) {
        return;
    }

    divorcing.value = true;
    router.delete(route("family.marriage.divorce"), {
        preserveScroll: true,
        onFinish: () => {
            divorcing.value = false;
        },
    });
}

function toggleFavorite(lifer) {
    favoriteLiferId.value = lifer.id;
    const options = {
        preserveScroll: true,
        onFinish: () => {
            favoriteLiferId.value = null;
        },
    };

    if (lifer.is_favorite) {
        router.delete(route("family.favorites.destroy", lifer.id), options);
        return;
    }

    router.post(route("family.favorites.store", lifer.id), {}, options);
}

function selectLifer(lifer) {
    selectedLiferId.value = lifer.id;
    liferSearch.value = "";
}

function saveChildName(pregnancy, child) {
    namingChildId.value = child.id;
    namingErrors.value = {};
    router.patch(
        route("family.children.name", {
            pregnancy: pregnancy.id,
            child: child.id,
        }),
        childNames.value[child.id],
        {
            preserveScroll: true,
            onError: (errors) => {
                namingErrors.value = errors;
            },
            onFinish: () => {
                namingChildId.value = null;
            },
        },
    );
}

function careForChild(child, care) {
    caringChildId.value = child.id;
    router.post(
        route("family.children.care", child.id),
        { care },
        {
            preserveScroll: true,
            onFinish: () => {
                caringChildId.value = null;
            },
        },
    );
}

function careForAllChildren() {
    caringAll.value = true;
    router.post(
        route("family.children.care-all"),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                caringAll.value = false;
            },
        },
    );
}

function renounceChild(child) {
    if (!window.confirm(`Renier ${child.name} ? Ton Lifer perdra immédiatement et définitivement sa garde, mais restera inscrit dans sa filiation biologique.`)) {
        return;
    }

    changingCustodyChildId.value = child.id;
    router.post(
        route("family.children.renounce", child.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                changingCustodyChildId.value = null;
            },
        },
    );
}

function abandonChild(child) {
    const message = child.custodian_count > 1
        ? `Demander l’abandon de ${child.name} ? L’autre responsable devra accepter et chacun paiera 50 Lif’coins.`
        : `Confier ${child.name} à l’orphelinat ? Ton Lifer paiera 100 Lif’coins.`;

    if (!window.confirm(message)) return;

    changingCustodyChildId.value = child.id;
    router.post(
        route("family.children.abandon", child.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                changingCustodyChildId.value = null;
            },
        },
    );
}

function formatDate(value, withTime = false) {
    if (!value) return "Date inconnue";

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        ...(withTime ? { hour: "2-digit", minute: "2-digit" } : {}),
    }).format(new Date(value));
}

function childCountLabel(count) {
    if (count === 1) return "Un enfant est attendu";
    if (count === 2) return "Des jumeaux sont attendus";
    return "Des triplés sont attendus";
}

function gaugeTone(value) {
    if (value <= 15) return "danger";
    if (value <= 60) return "warning";
    return "stable";
}
</script>

<template>
    <AppLayout title="Famille" :money="money">
        <main class="family-page">
            <section class="family-hero" aria-labelledby="family-title">
                <div class="family-hero__copy">
                    <span class="family-kicker">Histoire de vie</span>
                    <h1 id="family-title">Ma famille</h1>
                    <p>
                        Construis les liens de ton Lifer, suis les naissances et
                        prends soin de chaque membre de son foyer.
                    </p>
                </div>
                <div class="family-hero__summary" :class="{ 'family-hero__summary--paired': spouse }">
                    <span>{{ spouse ? "Mariage" : "Situation" }}</span>
                    <strong>{{ spouse ? spouse.name : "Célibataire" }}</strong>
                    <small v-if="spouse">Depuis le {{ formatDate(spouse.married_at) }}</small>
                    <small v-else>Aucun mariage actif</small>
                    <button
                        v-if="spouse"
                        type="button"
                        class="family-divorce"
                        :disabled="divorcing"
                        @click="divorce"
                    >
                        {{ divorcing ? "Divorce…" : "Divorcer" }}
                    </button>
                </div>
            </section>

            <section
                v-if="parents.length"
                class="family-panel family-origin"
                aria-labelledby="family-origin-title"
            >
                <div class="family-heading family-heading--wide">
                    <div>
                        <span class="family-kicker">Origines</span>
                        <h2 id="family-origin-title">Mes parents</h2>
                    </div>
                    <p>
                        Ces liens appartiennent à l’histoire de ton Lifer et
                        restent attachés à son identité.
                    </p>
                </div>

                <div class="family-parent-grid">
                    <article
                        v-for="parent in parents"
                        :key="`${parent.role}-${parent.id}`"
                        class="family-parent-card"
                        :class="`family-parent-card--${parent.role === 'Mère' ? 'female' : 'male'}`"
                    >
                        <span>{{ parent.role }}</span>
                        <strong>
                            <Link
                                v-if="parent.is_active"
                                :href="route('lifers.profile.show', parent.id)"
                            >
                                {{ parent.name }}
                            </Link>
                            <template v-else>{{ parent.name }}</template>
                        </strong>
                        <small v-if="!parent.is_active">Lifer décédé</small>
                    </article>
                </div>
            </section>

            <section v-if="receivedRequests.length" class="family-panel family-panel--requests" aria-labelledby="family-requests-title">
                <div class="family-heading">
                    <div>
                        <span class="family-kicker">Ton accord compte</span>
                        <h2 id="family-requests-title">Demandes reçues</h2>
                    </div>
                    <span class="family-count">{{ receivedRequests.length }}</span>
                </div>

                <div class="family-request-list">
                    <article v-for="request in receivedRequests" :key="request.id" class="family-request">
                        <span class="family-avatar" aria-hidden="true">{{ request.other_lifer.name.charAt(0) }}</span>
                        <div>
                            <strong>{{ request.other_lifer.name }}</strong>
                            <p>{{ requestLabels[request.type] }}</p>
                            <p v-if="request.child">Pour {{ request.child.name }}</p>
                            <small>Envoyée le {{ formatDate(request.created_at, true) }}</small>
                        </div>
                        <div class="family-request__actions">
                            <button
                                type="button"
                                class="family-button family-button--quiet"
                                :disabled="respondingRequestId === request.id"
                                @click="respondToRequest(request.id, false)"
                            >
                                Refuser
                            </button>
                            <button
                                type="button"
                                class="family-button"
                                :disabled="respondingRequestId === request.id"
                                @click="respondToRequest(request.id, true)"
                            >
                                {{ respondingRequestId === request.id ? "Réponse…" : "Accepter" }}
                            </button>
                        </div>
                    </article>
                </div>
            </section>

            <div class="family-layout">
                <section class="family-panel family-actions" aria-labelledby="family-actions-title">
                    <div class="family-heading">
                        <div>
                            <span class="family-kicker">Créer un lien</span>
                            <h2 id="family-actions-title">À deux</h2>
                        </div>
                    </div>

                    <label class="family-field" for="family-lifer-search">
                        <span>Choisir un Lifer</span>
                        <input
                            id="family-lifer-search"
                            v-model="liferSearch"
                            type="search"
                            autocomplete="off"
                            placeholder="Rechercher par prénom ou nom…"
                        />
                    </label>

                    <div v-if="otherLifers.length" class="family-lifer-picker">
                        <p v-if="selectedLifer" class="family-lifer-picker__selected">
                            <span>Sélection actuelle</span>
                            <strong>{{ selectedLifer.name }}</strong>
                            <small v-if="spouse && selectedLifer.id === spouse.id">Ton conjoint est sélectionné par défaut.</small>
                        </p>

                        <div v-if="quickLifers.length" class="family-lifer-shortcuts">
                            <span>Conjoint et favoris</span>
                            <div class="family-lifer-results" role="listbox" aria-label="Conjoint et Lifers favoris">
                                <div
                                    v-for="lifer in quickLifers"
                                    :key="`quick-${lifer.id}`"
                                    class="family-lifer-result"
                                    :class="{ 'family-lifer-result--selected': lifer.id === selectedLiferId }"
                                >
                                    <button
                                        type="button"
                                        class="family-lifer-result__select"
                                        role="option"
                                        :aria-selected="lifer.id === selectedLiferId"
                                        @click="selectLifer(lifer)"
                                    >
                                        <span class="family-avatar" aria-hidden="true">{{ lifer.name.charAt(0) }}</span>
                                        <span>
                                            <strong>{{ lifer.name }}</strong>
                                            <small v-if="spouse && lifer.id === spouse.id">Conjoint</small>
                                            <small v-else>Favori</small>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        class="family-favorite"
                                        :class="{ 'family-favorite--active': lifer.is_favorite }"
                                        :disabled="favoriteLiferId === lifer.id"
                                        :aria-label="lifer.is_favorite ? `Retirer ${lifer.name} des favoris` : `Ajouter ${lifer.name} aux favoris`"
                                        :title="lifer.is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                                        @click="toggleFavorite(lifer)"
                                    >
                                        {{ lifer.is_favorite ? "★" : "☆" }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-if="normalizedSearch" class="family-search-results">
                            <span>Résultats de recherche</span>
                            <div v-if="filteredLifers.length" class="family-lifer-results family-lifer-results--search" role="listbox" aria-label="Résultats de la recherche">
                                <div
                                    v-for="lifer in filteredLifers"
                                    :key="lifer.id"
                                    class="family-lifer-result"
                                    :class="{ 'family-lifer-result--selected': lifer.id === selectedLiferId }"
                                >
                                    <button
                                        type="button"
                                        class="family-lifer-result__select"
                                        role="option"
                                        :aria-selected="lifer.id === selectedLiferId"
                                        @click="selectLifer(lifer)"
                                    >
                                        <span class="family-avatar" aria-hidden="true">{{ lifer.name.charAt(0) }}</span>
                                        <span>
                                            <strong>{{ lifer.name }}</strong>
                                            <small v-if="spouse && lifer.id === spouse.id">Conjoint</small>
                                            <small v-else-if="lifer.is_favorite">Favori</small>
                                        </span>
                                    </button>
                                    <button
                                        type="button"
                                        class="family-favorite"
                                        :class="{ 'family-favorite--active': lifer.is_favorite }"
                                        :disabled="favoriteLiferId === lifer.id"
                                        :aria-label="lifer.is_favorite ? `Retirer ${lifer.name} des favoris` : `Ajouter ${lifer.name} aux favoris`"
                                        :title="lifer.is_favorite ? 'Retirer des favoris' : 'Ajouter aux favoris'"
                                        @click="toggleFavorite(lifer)"
                                    >
                                        {{ lifer.is_favorite ? "★" : "☆" }}
                                    </button>
                                </div>
                            </div>
                            <p v-else class="family-empty">Aucun Lifer ne correspond à cette recherche.</p>
                        </div>
                        <p v-else-if="!quickLifers.length" class="family-empty family-search-hint">
                            Commence à écrire un prénom ou un nom pour rechercher un Lifer.
                        </p>
                    </div>

                    <p v-if="otherLifers.length === 0" class="family-empty">
                        Aucun autre Lifer actif n’est disponible pour le moment.
                    </p>

                    <div v-else class="family-action-grid">
                        <article class="family-action-card family-action-card--rose">
                            <span class="family-action-card__icon" aria-hidden="true">♡</span>
                            <div>
                                <h3>Se marier</h3>
                                <p>Propose un mariage. Le lien n’existera qu’après acceptation.</p>
                            </div>
                            <button
                                type="button"
                                class="family-button"
                                :disabled="requestForm.processing || !selectedLiferId || Boolean(spouse)"
                                @click="sendRequest('marriage')"
                            >
                                {{ spouse ? "Déjà marié·e" : "Faire la demande" }}
                            </button>
                        </article>

                        <article class="family-action-card family-action-card--sage">
                            <span class="family-action-card__icon" aria-hidden="true">＋</span>
                            <div>
                                <h3>Moment protégé</h3>
                                <p>Une protection est consommée uniquement si la demande est acceptée.</p>
                                <small>{{ actionStatus.protection_quantity }} protection(s) · {{ protectedRequestSlots }} envoi(s) disponible(s) · {{ actionStatus.protected_pending }} en attente</small>
                            </div>
                            <button
                                type="button"
                                class="family-button"
                                :disabled="requestForm.processing || !selectedLiferId || protectedRequestSlots === 0"
                                @click="sendRequest('intimacy_protected')"
                            >
                                Envoyer la demande
                            </button>
                        </article>

                        <article class="family-action-card family-action-card--gold">
                            <span class="family-action-card__icon" aria-hidden="true">✦</span>
                            <div>
                                <h3>Tenter d’avoir un bébé</h3>
                                <p>Chaque tentative acceptée offre 25 % de chance de conception.</p>
                                <small>{{ babyRequestSlots }} envoi(s) disponible(s) · {{ actionStatus.baby_attempts_pending }} en attente</small>
                            </div>
                            <button
                                type="button"
                                class="family-button"
                                :disabled="requestForm.processing || !selectedLiferId || !selectedLifer?.can_attempt_baby || babyRequestSlots === 0"
                                @click="sendRequest('baby_attempt')"
                            >
                                {{ selectedLifer && !selectedLifer.can_attempt_baby ? "Indisponible pour ce duo" : "Envoyer la demande" }}
                            </button>
                        </article>
                    </div>

                    <p v-if="requestForm.hasErrors" class="family-error" role="alert">
                        {{ Object.values(requestForm.errors)[0] }}
                    </p>
                </section>

                <aside class="family-panel family-pending" aria-labelledby="family-pending-title">
                    <div class="family-heading">
                        <div>
                            <span class="family-kicker">En attente</span>
                            <h2 id="family-pending-title">Demandes envoyées</h2>
                        </div>
                    </div>
                    <p v-if="sentRequests.length === 0" class="family-empty">
                        Tu n’as aucune demande en attente.
                    </p>
                    <ul v-else class="family-pending__list">
                        <li v-for="request in sentRequests" :key="request.id">
                            <span class="family-avatar" aria-hidden="true">{{ request.other_lifer.name.charAt(0) }}</span>
                            <span>
                                <strong>{{ request.other_lifer.name }}</strong>
                                <small>{{ requestLabels[request.type] }}</small>
                            </span>
                            <button
                                type="button"
                                class="family-cancel-request"
                                :disabled="cancellingRequestId === request.id"
                                @click="cancelRequest(request)"
                            >
                                {{ cancellingRequestId === request.id ? "Annulation…" : "Annuler" }}
                            </button>
                        </li>
                    </ul>
                </aside>
            </div>

            <section class="family-panel" aria-labelledby="pregnancies-title">
                <div class="family-heading family-heading--wide">
                    <div>
                        <span class="family-kicker">À venir</span>
                        <h2 id="pregnancies-title">Naissances</h2>
                    </div>
                    <p>La grossesse dure deux jours réels, soit environ neuf mois dans Lifers.</p>
                </div>

                <div v-if="pregnancies.length" class="family-pregnancy-grid">
                    <article v-for="pregnancy in pregnancies" :key="pregnancy.id" class="family-pregnancy-card">
                        <div class="family-pregnancy-card__summary">
                            <div class="family-pregnancy-card__number">{{ pregnancy.children_count }}</div>
                            <div>
                                <span class="family-kicker">Avec {{ pregnancy.other_parent?.name }}</span>
                                <h3>{{ childCountLabel(pregnancy.children_count) }}</h3>
                                <p>Naissance prévue le {{ formatDate(pregnancy.due_at, true) }}</p>
                            </div>
                        </div>
                        <div class="family-name-list">
                            <form
                                v-for="child in pregnancy.children"
                                :key="child.id"
                                class="family-name-form"
                                @submit.prevent="saveChildName(pregnancy, child)"
                            >
                                <div
                                    class="family-expected-child"
                                    :class="`family-expected-child--${child.sex}`"
                                >
                                    <span class="family-expected-child__symbol" aria-hidden="true">
                                        {{ child.sex === "female" ? "♀" : "♂" }}
                                    </span>
                                    <strong>{{ childNames[child.id].first_name.trim() || "?" }}</strong>
                                    <span class="family-visually-hidden">
                                        {{ child.sex === "female" ? "Fille" : "Garçon" }}
                                    </span>
                                </div>
                                <label>
                                    <span>Prénom</span>
                                    <input
                                        v-model="childNames[child.id].first_name"
                                        type="text"
                                        maxlength="45"
                                        required
                                        autocomplete="off"
                                        placeholder="Choisir un prénom"
                                    />
                                </label>
                                <label>
                                    <span>Nom</span>
                                    <select v-model="childNames[child.id].last_name" required>
                                        <option
                                            v-for="lastName in pregnancy.available_last_names"
                                            :key="lastName"
                                            :value="lastName"
                                        >
                                            {{ lastName }}
                                        </option>
                                    </select>
                                </label>
                                <button class="family-button" type="submit" :disabled="namingChildId === child.id">
                                    {{ namingChildId === child.id ? "Enregistrement…" : child.first_name ? "Modifier" : "Enregistrer" }}
                                </button>
                            </form>
                            <p v-if="namingErrors.first_name || namingErrors.last_name || namingErrors.child" class="family-error" role="alert">
                                {{ namingErrors.first_name || namingErrors.last_name || namingErrors.child }}
                            </p>
                        </div>
                    </article>
                </div>
                <div v-else class="family-empty-state">
                    <span aria-hidden="true">✦</span>
                    <div>
                        <h3>Aucune naissance prévue</h3>
                        <p>Les futures grossesses apparaîtront ici après une conception réussie.</p>
                    </div>
                </div>
            </section>

            <section class="family-panel" aria-labelledby="children-title">
                <div class="family-heading family-heading--wide">
                    <div>
                        <span class="family-kicker">Le foyer</span>
                        <h2 id="children-title">Mes enfants</h2>
                    </div>
                    <div class="family-care-all">
                        <p>Les soins sont partagés entre tous les Lifers qui en ont la garde.</p>
                        <button
                            v-if="children.length"
                            type="button"
                            class="family-button"
                            :disabled="caringAll || !childrenNeedingCare.length"
                            @click="careForAllChildren"
                        >
                            {{ caringAll
                                ? "Soins…"
                                : childrenNeedingCare.length
                                    ? `S’occuper des enfants · ${childrenNeedingCare.length} concerné${childrenNeedingCare.length > 1 ? "s" : ""} · ${careAllCost} Lif’coins`
                                    : "Tous les besoins sont comblés"
                            }}
                        </button>
                    </div>
                </div>

                <p v-if="careError" class="family-error" role="alert">{{ careError }}</p>

                <div v-if="children.length" class="family-children-grid">
                    <article v-for="child in children" :key="child.id" class="family-child-card">
                        <div class="family-child-card__heading">
                            <span class="family-avatar family-avatar--child" aria-hidden="true">{{ child.name.charAt(0) }}</span>
                            <div><h3>{{ child.name }}</h3><p>{{ child.age }} an(s)</p></div>
                        </div>
                        <div v-if="child.gauges" class="family-gauges">
                            <div v-for="(value, key) in child.gauges" :key="key">
                                <span><strong>{{ { hunger: "Faim", hygiene: "Hygiène", affection: "Affection" }[key] }}</strong><small>{{ value }}/100</small></span>
                                <i><b :class="`family-gauge--${gaugeTone(value)}`" :style="{ width: `${value}%` }"></b></i>
                            </div>
                        </div>
                        <div class="family-child-care">
                            <button type="button" :disabled="caringChildId === child.id" @click="careForChild(child, 'feed')">
                                Nourrir +{{ actionStatus.care_gain }} · {{ actionStatus.feed_cost }}
                            </button>
                            <button type="button" :disabled="caringChildId === child.id" @click="careForChild(child, 'wash')">
                                Laver +{{ actionStatus.care_gain }} · {{ actionStatus.wash_cost }}
                            </button>
                            <button type="button" :disabled="caringChildId === child.id" @click="careForChild(child, 'cuddle')">
                                Câliner +{{ actionStatus.care_gain }} · gratuit
                            </button>
                        </div>
                        <div v-if="child.is_guardian" class="family-child-legal">
                            <button
                                v-if="child.custodian_count > 1"
                                type="button"
                                :disabled="changingCustodyChildId === child.id"
                                @click="renounceChild(child)"
                            >
                                Renier
                            </button>
                            <button
                                type="button"
                                :disabled="changingCustodyChildId === child.id"
                                @click="abandonChild(child)"
                            >
                                Confier à l’orphelinat
                            </button>
                        </div>
                    </article>
                </div>
                <div v-else class="family-empty-state">
                    <span aria-hidden="true">♡</span>
                    <div><h3>Le foyer est encore calme</h3><p>Les enfants dont tu as la garde apparaîtront ici.</p></div>
                </div>
            </section>

            <p class="family-note">
                Besoin d’écrire à quelqu’un avant une demande ?
                <Link :href="route('social')">Ouvrir la communauté</Link>
            </p>
        </main>
    </AppLayout>
</template>

<style scoped>
.family-page{display:grid;width:min(100%,1480px);margin-inline:auto;padding:clamp(22px,3vw,48px);gap:24px;color:#46324e}.family-hero,.family-panel{border:1px solid rgb(70 50 78/8%);border-radius:24px;background:#f8f3ec;box-shadow:0 14px 34px rgb(70 50 78/8%)}.family-hero{display:grid;min-height:255px;padding:clamp(30px,5vw,72px);overflow:hidden;grid-template-columns:minmax(0,1.5fr) minmax(250px,.65fr);align-items:center;gap:36px;background:linear-gradient(115deg,#f8f3ec 0 64%,#eee5da 64% 100%)}.family-kicker{color:#6f927b;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.family-hero h1,.family-heading h2,.family-action-card h3,.family-pregnancy-card h3,.family-empty-state h3,.family-child-card h3{font-family:"Bricolage Grotesque",ui-sans-serif,system-ui,sans-serif;letter-spacing:-.035em}.family-hero h1{margin:8px 0 12px;font-size:clamp(48px,6vw,82px);line-height:.92}.family-hero__copy p{max-width:630px;margin:0;color:#7e6e81;font-size:18px;line-height:1.65}.family-hero__summary{display:grid;min-height:145px;padding:25px;border-radius:20px;align-content:center;background:rgb(255 252 247/72%);box-shadow:0 10px 24px rgb(70 50 78/7%)}.family-hero__summary span,.family-hero__summary small{color:#8d7c8f}.family-hero__summary strong{margin:6px 0;font-size:29px}.family-hero__summary--paired{border-left:5px solid #d6a84a}.family-panel{padding:clamp(24px,3.2vw,42px)}.family-heading{display:flex;margin-bottom:26px;align-items:center;justify-content:space-between;gap:20px}.family-heading h2{margin:5px 0 0;font-size:clamp(30px,3.6vw,44px);line-height:1}.family-heading--wide>p{max-width:420px;margin:0;color:#8d7c8f;line-height:1.55}.family-care-all{display:grid;max-width:430px;justify-items:end;gap:10px}.family-care-all p{margin:0;color:#8d7c8f;line-height:1.5;text-align:right}.family-count{display:grid;width:42px;height:42px;border-radius:50%;place-items:center;background:#d6a84a;font-weight:800}.family-request-list{display:grid;gap:12px}.family-request{display:grid;padding:16px 18px;border:1px solid rgb(70 50 78/8%);border-radius:17px;grid-template-columns:auto 1fr auto;align-items:center;gap:15px;background:#fffaf4}.family-avatar{display:grid;width:46px;height:46px;border-radius:15px;place-items:center;color:#fff;background:#6f927b;font-family:"Bricolage Grotesque",sans-serif;font-size:20px;font-weight:700}.family-request p,.family-request small{margin:2px 0;color:#8d7c8f}.family-request__actions{display:flex;gap:9px}.family-button{min-height:42px;padding:0 17px;border:0;border-radius:12px;color:#46324e;background:#d6a84a;font-weight:800;cursor:pointer;box-shadow:0 6px 14px rgb(70 50 78/10%)}.family-button:hover:not(:disabled){filter:brightness(.97);transform:translateY(-1px)}.family-button:disabled{cursor:not-allowed;opacity:.52}.family-button--quiet{border:1px solid rgb(70 50 78/14%);background:transparent;box-shadow:none}.family-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(270px,.72fr);gap:24px}.family-field{display:grid;max-width:540px;margin-bottom:24px;gap:8px;font-weight:800}.family-field select{min-height:50px;padding:0 44px 0 15px;border:1px solid rgb(70 50 78/18%);border-radius:13px;color:#46324e;background:#fffaf4;font:inherit}.family-action-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.family-action-card{display:flex;min-height:295px;padding:21px;border-radius:19px;flex-direction:column;align-items:flex-start;gap:17px;background:#f4eee6}.family-action-card--rose{background:#f2e5e3}.family-action-card--sage{background:#e5ebe3}.family-action-card--gold{background:#f3e7c8}.family-action-card__icon{display:grid;width:44px;height:44px;border-radius:14px;place-items:center;background:rgb(255 255 255/55%);font-size:23px}.family-action-card>div{flex:1}.family-action-card h3{margin:0 0 8px;font-size:23px}.family-action-card p{margin:0;color:#766678;line-height:1.5}.family-action-card small{display:block;margin-top:12px;font-weight:750}.family-action-card .family-button{width:100%}.family-pending__list{display:grid;margin:0;padding:0;gap:11px;list-style:none}.family-pending__list li{display:flex;padding:11px;border-radius:15px;align-items:center;gap:11px;background:#f4eee6}.family-pending__list .family-avatar{width:38px;height:38px;border-radius:12px;font-size:16px}.family-pending__list span:last-child{display:grid;gap:2px}.family-pending__list small,.family-empty{color:#8d7c8f}.family-error{margin:18px 0 0;padding:12px 15px;border-radius:12px;color:#7c3e49;background:#f4e2e2;font-weight:700}.family-pregnancy-grid,.family-children-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.family-pregnancy-card{display:grid;padding:24px;border-radius:19px;gap:22px;background:#f3e7c8}.family-pregnancy-card__summary{display:flex;align-items:center;gap:20px}.family-pregnancy-card__number{display:grid;min-width:70px;height:70px;border-radius:21px;place-items:center;color:#fff;background:#46324e;font-size:31px;font-weight:800}.family-pregnancy-card h3{margin:5px 0 6px;font-size:25px}.family-pregnancy-card p,.family-pregnancy-card small{margin:0;color:#766678}.family-name-list{display:grid;gap:10px}.family-name-form{display:grid;padding:13px;border-radius:15px;grid-template-columns:auto minmax(120px,1fr) minmax(120px,1fr) auto;align-items:end;gap:10px;background:rgb(255 250 244/68%)}.family-name-form>strong{align-self:center}.family-name-form label{display:grid;gap:5px;color:#766678;font-size:11px;font-weight:800}.family-name-form input,.family-name-form select{width:100%;min-height:40px;padding:0 10px;border:1px solid rgb(70 50 78/16%);border-radius:10px;color:#46324e;background:#fffaf4;font:inherit}.family-name-form .family-button{min-height:40px}.family-empty-state{display:flex;min-height:125px;padding:23px;border:1px dashed rgb(70 50 78/18%);border-radius:18px;align-items:center;gap:18px;background:#fffaf4}.family-empty-state>span{font-size:34px;color:#d6a84a}.family-empty-state h3{margin:0 0 5px;font-size:23px}.family-empty-state p{margin:0;color:#8d7c8f}.family-children-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.family-child-card{padding:20px;border-radius:18px;background:#f4eee6}.family-child-card__heading{display:flex;align-items:center;gap:12px}.family-avatar--child{background:#d6a84a}.family-child-card h3,.family-child-card p{margin:0}.family-child-card p{color:#8d7c8f}.family-gauges{display:grid;margin-top:19px;gap:13px}.family-gauges>div>span{display:flex;margin-bottom:5px;justify-content:space-between;font-size:12px}.family-gauges i{display:block;height:7px;overflow:hidden;border-radius:99px;background:rgb(70 50 78/9%)}.family-gauges b{display:block;height:100%;border-radius:inherit}.family-gauge--stable{background:#6f927b}.family-gauge--warning{background:#d6a84a}.family-gauge--danger{background:#b86b73}.family-child-care{display:grid;margin-top:18px;grid-template-columns:repeat(3,minmax(0,1fr));gap:7px}.family-child-care button{min-height:38px;padding:6px;border:1px solid rgb(70 50 78/12%);border-radius:10px;color:#46324e;background:#fffaf4;font-size:11px;font-weight:800;cursor:pointer}.family-child-care button:hover:not(:disabled){border-color:#d6a84a}.family-child-care button:disabled{opacity:.5}.family-child-legal{display:flex;margin-top:12px;padding-top:12px;border-top:1px solid rgb(70 50 78/10%);gap:8px}.family-child-legal button{padding:0;border:0;color:#8d5a63;background:transparent;font-size:11px;font-weight:800;text-decoration:underline;text-underline-offset:3px;cursor:pointer}.family-child-legal button:disabled{opacity:.5}.family-note{margin:2px 0 12px;text-align:center;color:#8d7c8f}.family-note a{color:#46324e;font-weight:800;text-decoration-color:#d6a84a;text-decoration-thickness:2px;text-underline-offset:4px}.family-button:focus-visible,.family-field select:focus-visible,.family-name-form input:focus-visible,.family-name-form select:focus-visible,.family-child-care button:focus-visible,.family-child-legal button:focus-visible,.family-note a:focus-visible{outline:3px solid rgb(111 146 123/45%);outline-offset:3px}
.family-divorce{justify-self:start;margin-top:12px;padding:0;border:0;color:#8d5a63;background:transparent;font-weight:800;text-decoration:underline;text-decoration-color:rgb(141 90 99/45%);text-underline-offset:4px;cursor:pointer}.family-divorce:disabled{opacity:.5;cursor:not-allowed}.family-field input{min-height:50px;padding:0 15px;border:1px solid rgb(70 50 78/18%);border-radius:13px;color:#46324e;background:#fffaf4;font:inherit}.family-lifer-picker{display:grid;max-width:620px;margin:-10px 0 26px;gap:10px}.family-lifer-picker__selected{display:grid;margin:0;padding:13px 16px;border-left:4px solid #d6a84a;border-radius:12px;gap:2px;background:#f3e7c8}.family-lifer-picker__selected span,.family-lifer-picker__selected small{color:#766678;font-size:11px}.family-lifer-results{display:grid;max-height:250px;padding:7px;overflow:auto;border:1px solid rgb(70 50 78/10%);border-radius:15px;gap:5px;background:#fffaf4}.family-lifer-result{display:grid;border-radius:12px;grid-template-columns:minmax(0,1fr) auto;align-items:center;background:#f8f3ec}.family-lifer-result--selected{background:#e5ebe3;box-shadow:inset 0 0 0 1px rgb(111 146 123/28%)}.family-lifer-result__select{display:flex;min-width:0;padding:9px;border:0;align-items:center;gap:10px;color:#46324e;background:transparent;text-align:left;cursor:pointer}.family-lifer-result__select>span:last-child{display:grid;min-width:0;gap:1px}.family-lifer-result__select strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.family-lifer-result__select small{color:#8d7c8f}.family-lifer-result .family-avatar{width:38px;height:38px;border-radius:11px;font-size:16px}.family-favorite{width:42px;height:42px;margin-right:7px;border:0;border-radius:11px;color:#9a879c;background:transparent;font-size:24px;line-height:1;cursor:pointer}.family-favorite--active{color:#d6a84a}.family-favorite:hover:not(:disabled){background:rgb(214 168 74/12%)}.family-favorite:disabled{opacity:.5;cursor:not-allowed}.family-pending__list li>span:nth-child(2){min-width:0;flex:1}.family-cancel-request{padding:4px 0;border:0;color:#8d5a63;background:transparent;font-size:11px;font-weight:800;text-decoration:underline;text-underline-offset:3px;cursor:pointer}.family-cancel-request:disabled{opacity:.5;cursor:not-allowed}.family-divorce:focus-visible,.family-field input:focus-visible,.family-lifer-result__select:focus-visible,.family-favorite:focus-visible,.family-cancel-request:focus-visible{outline:3px solid rgb(111 146 123/45%);outline-offset:3px}
.family-lifer-shortcuts,.family-search-results{display:grid;gap:6px}.family-lifer-shortcuts>span,.family-search-results>span{color:#766678;font-size:11px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}.family-lifer-results--search{box-shadow:0 12px 24px rgb(70 50 78/10%)}.family-search-hint{margin:0;padding:10px 2px}
.family-expected-child{display:flex;min-width:98px;min-height:52px;padding:8px 12px;border-radius:13px;align-self:stretch;align-items:center;gap:8px}.family-expected-child--female{color:#8f505c;background:#f2e0e3}.family-expected-child--male{color:#526f88;background:#e1eaf1}.family-expected-child__symbol{font-size:17px;font-weight:900;line-height:1}.family-expected-child strong{max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.family-visually-hidden{position:absolute;width:1px;height:1px;padding:0;overflow:hidden;border:0;margin:-1px;clip:rect(0,0,0,0);white-space:nowrap}
.family-parent-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.family-parent-card{display:grid;min-height:108px;padding:20px;border-radius:18px;align-content:center;gap:5px}.family-parent-card--female{color:#8f505c;background:#f2e0e3}.family-parent-card--male{color:#526f88;background:#e1eaf1}.family-parent-card>span,.family-parent-card>small{font-size:12px}.family-parent-card>strong{font-family:"Bricolage Grotesque",ui-sans-serif,system-ui,sans-serif;font-size:23px}.family-parent-card a{color:inherit;text-decoration-color:currentColor;text-decoration-thickness:2px;text-underline-offset:4px}.family-parent-card a:focus-visible{outline:3px solid rgb(111 146 123/45%);outline-offset:3px}
@media(max-width:1100px){.family-layout{grid-template-columns:1fr}.family-action-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.family-pending__list{grid-template-columns:repeat(2,minmax(0,1fr))}.family-children-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:760px){.family-page{padding:16px}.family-hero{padding:30px;grid-template-columns:1fr;background:#f8f3ec}.family-hero__summary{min-height:auto}.family-heading--wide{align-items:flex-start;flex-direction:column}.family-request{grid-template-columns:auto 1fr}.family-request__actions{grid-column:1/-1}.family-request__actions .family-button{flex:1}.family-action-grid,.family-pregnancy-grid,.family-children-grid,.family-pending__list,.family-parent-grid{grid-template-columns:1fr}.family-action-card{min-height:0}.family-pregnancy-card__summary{align-items:flex-start}.family-pregnancy-card__number{min-width:54px;height:54px;border-radius:16px}.family-name-form{grid-template-columns:1fr}.family-name-form>strong{align-self:auto}.family-panel{border-radius:19px}}
@media(prefers-reduced-motion:reduce){.family-button{transition:none}.family-button:hover:not(:disabled){transform:none}}
</style>
