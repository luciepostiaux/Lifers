<script setup>
import { computed, ref } from "vue";
import { Head, useForm } from "@inertiajs/vue3";
import SiteHeader from "@/Components/SiteHeader.vue";

const props = defineProps({
    bodyTypes: {
        type: Array,
        default: () => [],
    },
    availableFamilyLifers: {
        type: Array,
        default: () => [],
    },
});

const activeMode = ref("new");
const selectedBodyId = ref(null);

const form = useForm({
    creation_mode: "new",
    first_name: "",
    last_name: "",
    family_child_id: null,
    body_type_id: null,
});

const selectedFamilyLifer = computed(() =>
    props.availableFamilyLifers.find(
        (lifer) => lifer.id === form.family_child_id,
    ),
);

const visibleBodyTypes = computed(() => {
    if (activeMode.value === "new" || !selectedFamilyLifer.value) {
        return props.bodyTypes;
    }

    return props.bodyTypes.filter(
        (body) => body.sex === selectedFamilyLifer.value.sex,
    );
});

const selectMode = (mode) => {
    activeMode.value = mode;
    form.creation_mode = mode;
    form.family_child_id = null;
    form.body_type_id = null;
    selectedBodyId.value = null;
    form.clearErrors();
};

const selectBody = (bodyId) => {
    selectedBodyId.value = bodyId;
    form.body_type_id = bodyId;
    form.clearErrors("body_type_id");
};

const selectFamilyLifer = (lifer) => {
    form.family_child_id = lifer.id;
    form.body_type_id = null;
    selectedBodyId.value = null;
    form.clearErrors("family_child_id", "body_type_id");

    const matchingBodies = props.bodyTypes.filter(
        (body) => body.sex === lifer.sex,
    );

    if (matchingBodies.length === 1) {
        selectBody(matchingBodies[0].id);
    }
};

const submitForm = () => {
    form.post(route("character.store"), {
        preserveScroll: true,
    });
};

const sexSymbol = (sex) => (sex === "female" ? "♀" : "♂");
const sexLabel = (sex) => (sex === "female" ? "féminin" : "masculin");
</script>

<template>
    <Head title="Lifers — Créer un nouveau personnage">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="character-create-page">
        <SiteHeader :can-login="false" />

        <main class="character-create-main">
            <section class="character-create-card">
                <header class="character-create-heading">
                    <div
                        class="character-create-tabs"
                        role="tablist"
                        aria-label="Choisir le type de personnage"
                    >
                        <button
                            id="new-character-tab"
                            type="button"
                            role="tab"
                            class="character-create-tab"
                            :class="{
                                'character-create-tab--active':
                                    activeMode === 'new',
                            }"
                            :aria-selected="activeMode === 'new'"
                            aria-controls="character-create-panel"
                            @click="selectMode('new')"
                        >
                            Créer un nouveau personnage
                        </button>
                        <button
                            id="reincarnation-tab"
                            type="button"
                            role="tab"
                            class="character-create-tab character-create-tab--reincarnation"
                            :class="{
                                'character-create-tab--active':
                                    activeMode === 'reincarnation',
                            }"
                            :aria-selected="activeMode === 'reincarnation'"
                            aria-controls="character-create-panel"
                            @click="selectMode('reincarnation')"
                        >
                            Réincarner un Lifer d’une famille
                        </button>
                    </div>
                </header>

                <form
                    id="character-create-panel"
                    class="character-create-form"
                    role="tabpanel"
                    :aria-labelledby="
                        activeMode === 'new'
                            ? 'new-character-tab'
                            : 'reincarnation-tab'
                    "
                    @submit.prevent="submitForm"
                >
                    <div class="character-create-grid">
                        <div
                            v-if="activeMode === 'new'"
                            class="character-create-identity"
                        >
                            <div class="character-create-field">
                                <label
                                    for="first_name"
                                    class="character-create-label"
                                >
                                    Prénom
                                </label>
                                <input
                                    id="first_name"
                                    v-model="form.first_name"
                                    type="text"
                                    class="character-create-input"
                                    required
                                    autofocus
                                    autocomplete="given-name"
                                    :aria-invalid="Boolean(form.errors.first_name)"
                                    :aria-describedby="
                                        form.errors.first_name
                                            ? 'first-name-error'
                                            : undefined
                                    "
                                />
                                <p
                                    v-if="form.errors.first_name"
                                    id="first-name-error"
                                    class="character-create-error"
                                >
                                    {{ form.errors.first_name }}
                                </p>
                            </div>

                            <div class="character-create-field">
                                <label
                                    for="last_name"
                                    class="character-create-label"
                                >
                                    Nom
                                </label>
                                <input
                                    id="last_name"
                                    v-model="form.last_name"
                                    type="text"
                                    class="character-create-input"
                                    required
                                    autocomplete="family-name"
                                    :aria-invalid="Boolean(form.errors.last_name)"
                                    :aria-describedby="
                                        form.errors.last_name
                                            ? 'last-name-error'
                                            : undefined
                                    "
                                />
                                <p
                                    v-if="form.errors.last_name"
                                    id="last-name-error"
                                    class="character-create-error"
                                >
                                    {{ form.errors.last_name }}
                                </p>
                            </div>
                        </div>

                        <fieldset
                            v-else
                            class="character-create-family"
                            :aria-describedby="
                                form.errors.family_child_id
                                    ? 'family-lifer-error'
                                    : undefined
                            "
                        >
                            <legend class="character-create-legend">
                                Choisir une identité familiale
                            </legend>

                            <div
                                v-if="props.availableFamilyLifers.length"
                                class="character-create-family-list"
                            >
                                <button
                                    v-for="lifer in props.availableFamilyLifers"
                                    :key="lifer.id"
                                    type="button"
                                    class="character-create-family-option"
                                    :class="[
                                        `character-create-family-option--${lifer.sex}`,
                                        {
                                            'character-create-family-option--selected':
                                                lifer.id === form.family_child_id,
                                        },
                                    ]"
                                    :aria-pressed="
                                        lifer.id === form.family_child_id
                                    "
                                    @click="selectFamilyLifer(lifer)"
                                >
                                    <span class="character-create-family-name">
                                        {{ lifer.first_name }}
                                        {{ lifer.last_name }}
                                    </span>
                                    <span
                                        class="character-create-sex-symbol"
                                        :aria-label="`Sexe ${sexLabel(lifer.sex)}`"
                                    >
                                        {{ sexSymbol(lifer.sex) }}
                                    </span>
                                </button>
                            </div>

                            <div v-else class="character-create-empty">
                                <p class="character-create-empty-title">
                                    Aucune réincarnation disponible
                                </p>
                                <p class="character-create-empty-copy">
                                    Les Lifers issus d’une famille apparaîtront
                                    ici lorsqu’ils auront atteint 18 ans.
                                </p>
                            </div>

                            <p
                                v-if="form.errors.family_child_id"
                                id="family-lifer-error"
                                class="character-create-error character-create-body-error"
                            >
                                {{ form.errors.family_child_id }}
                            </p>
                        </fieldset>

                        <fieldset
                            class="character-create-bodies"
                            :aria-describedby="
                                form.errors.body_type_id
                                    ? 'body-error'
                                    : undefined
                            "
                        >
                            <legend class="character-create-legend">
                                Choisir une apparence
                            </legend>

                            <p
                                v-if="
                                    activeMode === 'reincarnation' &&
                                    !selectedFamilyLifer
                                "
                                class="character-create-appearance-hint"
                            >
                                Sélectionne d’abord une identité pour voir les
                                apparences compatibles.
                            </p>

                            <div
                                v-else
                                class="character-create-body-grid"
                                :class="{
                                    'character-create-body-grid--single':
                                        visibleBodyTypes.length === 1,
                                }"
                            >
                                <button
                                    v-for="(body, index) in visibleBodyTypes"
                                    :key="body.id"
                                    type="button"
                                    class="character-create-body-option"
                                    :class="{
                                        'character-create-body-option--selected':
                                            body.id === selectedBodyId,
                                    }"
                                    :aria-pressed="
                                        body.id === selectedBodyId
                                    "
                                    :aria-label="`Sélectionner l’apparence ${index + 1}`"
                                    @click="selectBody(body.id)"
                                >
                                    <span
                                        class="character-create-body-visual"
                                        aria-hidden="true"
                                    >
                                        <img
                                            :src="`/${body.image_path}`"
                                            alt=""
                                            loading="eager"
                                            decoding="async"
                                        />
                                    </span>
                                </button>
                            </div>

                            <p
                                v-if="form.errors.body_type_id"
                                id="body-error"
                                class="character-create-error character-create-body-error"
                            >
                                {{ form.errors.body_type_id }}
                            </p>
                        </fieldset>
                    </div>

                    <p
                        v-if="form.errors.lifer"
                        class="character-create-error character-create-global-error"
                        role="alert"
                    >
                        {{ form.errors.lifer }}
                    </p>

                    <div class="character-create-actions">
                        <button
                            type="submit"
                            class="character-create-submit"
                            :disabled="form.processing"
                        >
                            {{
                                activeMode === "new"
                                    ? "Créer"
                                    : "Réincarner"
                            }}
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>

<style scoped>
.character-create-page {
    min-width: 320px;
    min-height: 100svh;
    overflow-x: clip;
    color: #46324e;
    background: #f4eee5;
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
}

.character-create-page ::selection {
    color: #46324e;
    background: #e8ca8a;
}

.character-create-main {
    width: min(100%, 1240px);
    margin-inline: auto;
    padding: clamp(36px, 5vw, 64px) clamp(24px, 4vw, 48px) 64px;
}

.character-create-card {
    padding: clamp(30px, 4vw, 52px);
    border: 1px solid rgb(70 50 78 / 7%);
    border-radius: 22px;
    background: #f8f3ec;
    box-shadow:
        0 1px 0 rgb(70 50 78 / 4%),
        0 10px 28px rgb(70 50 78 / 8%);
}

.character-create-heading {
    width: 100%;
}

.character-create-tabs {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: clamp(18px, 3vw, 38px);
    align-items: stretch;
}

.character-create-tab {
    min-width: 0;
    padding: 0 0 18px;
    border: 0;
    border-bottom: 2px solid rgb(70 50 78 / 11%);
    color: #46324e;
    background: transparent;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(27px, 3vw, 45px);
    font-weight: 700;
    line-height: 1.06;
    letter-spacing: -0.035em;
    text-align: left;
    cursor: pointer;
    opacity: 0.48;
    transition:
        color 180ms ease,
        border-color 180ms ease,
        opacity 180ms ease;
}

.character-create-tab--reincarnation {
    text-align: right;
}

.character-create-tab:hover {
    opacity: 0.76;
}

.character-create-tab:focus-visible {
    border-radius: 5px;
    outline: 3px solid rgb(70 50 78 / 28%);
    outline-offset: 5px;
}

.character-create-tab--active {
    border-bottom-color: #d6a84a;
    opacity: 1;
}

.character-create-form {
    margin-top: clamp(32px, 4vw, 46px);
}

.character-create-grid {
    display: grid;
    grid-template-columns: minmax(250px, 0.72fr) minmax(440px, 1.28fr);
    gap: clamp(34px, 5vw, 64px);
    align-items: start;
}

.character-create-identity {
    display: grid;
    gap: 24px;
}

.character-create-family {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

.character-create-family-list {
    display: grid;
    max-height: 368px;
    gap: 10px;
    padding: 4px;
    overflow-y: auto;
    scrollbar-color: rgb(70 50 78 / 24%) transparent;
}

.character-create-family-option {
    display: flex;
    width: 100%;
    min-height: 58px;
    gap: 14px;
    padding: 13px 16px;
    border: 2px solid transparent;
    border-radius: 13px;
    align-items: center;
    justify-content: space-between;
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.25;
    text-align: left;
    cursor: pointer;
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        transform 180ms ease;
}

.character-create-family-option--female {
    color: #8f505c;
    background: #f2e0e3;
}

.character-create-family-option--male {
    color: #526f88;
    background: #e1eaf1;
}

.character-create-family-option:hover {
    box-shadow: 0 7px 18px rgb(70 50 78 / 10%);
    transform: translateY(-1px);
}

.character-create-family-option:focus-visible {
    outline: 3px solid #46324e;
    outline-offset: 3px;
}

.character-create-family-option--selected {
    border-color: #d6a84a;
    box-shadow: 0 0 0 3px rgb(214 168 74 / 19%);
}

.character-create-family-name {
    overflow-wrap: anywhere;
}

.character-create-sex-symbol {
    flex: 0 0 auto;
    font-size: 17px;
    line-height: 1;
}

.character-create-empty,
.character-create-appearance-hint {
    margin: 0;
    padding: 20px;
    border: 1px dashed rgb(70 50 78 / 20%);
    border-radius: 13px;
    color: rgb(70 50 78 / 72%);
    background: rgb(252 248 242 / 65%);
    line-height: 1.5;
}

.character-create-empty-title {
    margin: 0;
    color: #46324e;
    font-weight: 700;
}

.character-create-empty-copy {
    margin: 6px 0 0;
    font-size: 14px;
}

.character-create-field {
    display: grid;
    gap: 8px;
}

.character-create-label,
.character-create-legend {
    color: #46324e;
    font-size: 16px;
    font-weight: 600;
    line-height: 1.35;
}

.character-create-input {
    width: 100%;
    min-height: 52px;
    padding: 12px 14px;
    border: 1px solid rgb(70 50 78 / 28%);
    border-radius: 11px;
    color: #46324e;
    background: #fcf8f2;
    box-shadow: 0 1px 2px rgb(70 50 78 / 5%);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
    font-size: 16px;
    line-height: 1.4;
    transition:
        border-color 180ms ease,
        outline-color 180ms ease;
}

.character-create-input:hover {
    border-color: rgb(70 50 78 / 48%);
}

.character-create-input:focus {
    border-color: #46324e;
    outline: 3px solid rgb(70 50 78 / 22%);
    outline-offset: 2px;
    box-shadow: none;
    --tw-ring-shadow: 0 0 #0000;
}

.character-create-input[aria-invalid="true"] {
    border-color: #9a334e;
}

.character-create-error {
    margin: 0;
    color: #9a334e;
    font-size: 14px;
    font-weight: 500;
    line-height: 1.4;
}

.character-create-bodies {
    min-width: 0;
    margin: 0;
    padding: 0;
    border: 0;
}

.character-create-legend {
    margin-bottom: 12px;
}

.character-create-body-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px;
}

.character-create-body-grid--single {
    grid-template-columns: minmax(0, 1fr);
}

.character-create-body-grid--single .character-create-body-option {
    width: min(100%, 310px);
}

.character-create-body-option {
    display: grid;
    min-width: 0;
    padding: 16px 16px 14px;
    border: 2px solid rgb(70 50 78 / 10%);
    border-radius: 16px;
    color: #46324e;
    background: #fcf8f2;
    box-shadow: 0 4px 14px rgb(70 50 78 / 5%);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
    cursor: pointer;
    transition:
        border-color 180ms ease,
        box-shadow 180ms ease,
        transform 180ms ease;
}

.character-create-body-option:hover {
    border-color: rgb(70 50 78 / 30%);
    box-shadow: 0 8px 20px rgb(70 50 78 / 9%);
    transform: translateY(-2px);
}

.character-create-body-option:focus-visible {
    outline: 3px solid #46324e;
    outline-offset: 4px;
}

.character-create-body-option--selected {
    border-color: #d6a84a;
    box-shadow:
        0 0 0 4px rgb(214 168 74 / 22%),
        0 8px 20px rgb(70 50 78 / 9%);
}

.character-create-body-visual {
    display: flex;
    height: clamp(260px, 28vw, 340px);
    align-items: flex-end;
    justify-content: center;
    overflow: hidden;
}

.character-create-body-visual img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    object-position: center bottom;
}

.character-create-body-error {
    margin-top: 10px;
}

.character-create-actions {
    display: flex;
    margin-top: clamp(30px, 4vw, 44px);
    justify-content: flex-end;
}

.character-create-global-error {
    margin-top: 22px;
    text-align: right;
}

.character-create-submit {
    display: inline-flex;
    min-width: 180px;
    min-height: 54px;
    padding: 13px 26px;
    border: 0;
    border-radius: 13px;
    align-items: center;
    justify-content: center;
    color: #46324e;
    background: #d6a84a;
    box-shadow: 0 8px 20px rgb(70 50 78 / 12%);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
    font-size: 17px;
    font-weight: 700;
    line-height: 1.2;
    cursor: pointer;
    transition:
        box-shadow 180ms ease,
        transform 180ms ease;
}

.character-create-submit:hover {
    box-shadow: 0 12px 26px rgb(70 50 78 / 20%);
    transform: translateY(-1px);
}

.character-create-submit:active {
    box-shadow: 0 5px 14px rgb(70 50 78 / 14%);
    transform: translateY(0);
}

.character-create-submit:focus-visible {
    outline: 3px solid #46324e;
    outline-offset: 4px;
    box-shadow: 0 0 0 7px rgb(244 238 229 / 90%);
}

.character-create-submit:disabled {
    cursor: wait;
    opacity: 0.62;
    transform: none;
}

@media (max-width: 899px) {
    .character-create-tabs {
        gap: 22px;
    }

    .character-create-tab {
        font-size: clamp(25px, 5vw, 36px);
    }

    .character-create-grid {
        grid-template-columns: 1fr;
    }

    .character-create-body-visual {
        height: clamp(250px, 48vw, 340px);
    }
}

@media (max-width: 639px) {
    .character-create-main {
        padding: 28px 16px 40px;
    }

    .character-create-card {
        padding: 26px 22px;
        border-radius: 16px;
    }

    .character-create-tabs {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    .character-create-tab,
    .character-create-tab--reincarnation {
        padding-bottom: 12px;
        font-size: 28px;
        text-align: left;
    }

    .character-create-body-grid {
        gap: 12px;
    }

    .character-create-body-option {
        padding: 12px 10px;
        border-radius: 13px;
    }

    .character-create-body-visual {
        height: clamp(210px, 72vw, 270px);
    }

    .character-create-actions {
        margin-top: 30px;
    }

    .character-create-submit {
        width: 100%;
    }
}

@media (prefers-reduced-motion: reduce) {
    .character-create-tab,
    .character-create-input,
    .character-create-family-option,
    .character-create-body-option,
    .character-create-submit {
        transition: none;
    }

    .character-create-family-option:hover,
    .character-create-body-option:hover,
    .character-create-submit:hover,
    .character-create-submit:active {
        transform: none;
    }
}
</style>
