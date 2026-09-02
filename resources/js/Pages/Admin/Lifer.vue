<script setup>
import { computed } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    lifer: { type: Object, required: true },
    sicknessCatalog: { type: Array, required: true },
    diplomaCatalog: { type: Array, required: true },
    money: { type: [String, Number], default: null },
});

const gaugeLabels = {
    hunger: "Faim",
    thirst: "Soif",
    clean: "Hygiène",
    happiness: "Bonheur",
    entertainment: "Divertissement",
    physical_condition: "Condition physique",
    health: "Santé",
};

const moneyForm = useForm({ amount: "", reason: "" });
const gaugeForm = useForm({
    gauges: { ...props.lifer.gauges },
    reason: "",
});
const sicknessForm = useForm({ sickness_id: "", reason: "" });
const diplomaGrantForm = useForm({
    liferId: props.lifer.id,
    diplomaId: "",
});
const diplomaRemoveForm = useForm({
    liferId: props.lifer.id,
    diplomaId: "",
});
const deathForm = useForm({ cause: "", reason: "" });

const availableSicknesses = computed(() =>
    props.sicknessCatalog.filter(
        (sickness) => !props.lifer.sickness_ids.includes(sickness.id),
    ),
);
const ownedDiplomaIds = computed(() => props.lifer.diplomas.map((item) => item.id));
const availableDiplomas = computed(() =>
    props.diplomaCatalog.filter(
        (diploma) => !ownedDiplomaIds.value.includes(diploma.id),
    ),
);

const updateMoney = () => {
    moneyForm.patch(route("admin.lifers.money.update", props.lifer.id), {
        preserveScroll: true,
        onSuccess: () => moneyForm.reset(),
    });
};

const updateGauges = () => {
    gaugeForm.patch(route("admin.lifers.gauges.update", props.lifer.id), {
        preserveScroll: true,
        onSuccess: () => gaugeForm.reset("reason"),
    });
};

const addSickness = () => {
    sicknessForm.post(route("admin.lifers.sicknesses.store", props.lifer.id), {
        preserveScroll: true,
        onSuccess: () => sicknessForm.reset(),
    });
};

const removeSickness = (sickness) => {
    const reason = window.prompt(
        `Pourquoi retirer « ${sickness.name} » de ce Lifer ?`,
    );
    if (!reason?.trim()) return;

    router.delete(
        route("admin.lifers.sicknesses.destroy", [props.lifer.id, sickness.id]),
        { data: { reason }, preserveScroll: true },
    );
};

const grantDiploma = () => {
    diplomaGrantForm.post(route("admin.grantDiploma"), {
        preserveScroll: true,
        onSuccess: () => diplomaGrantForm.reset("diplomaId"),
    });
};

const removeDiploma = () => {
    if (!window.confirm("Retirer ce diplôme du Lifer ?")) return;

    diplomaRemoveForm.post(route("admin.removeDiploma"), {
        preserveScroll: true,
        onSuccess: () => diplomaRemoveForm.reset("diplomaId"),
    });
};

const killLifer = () => {
    if (
        !window.confirm(
            `Confirmer le décès administratif de ${props.lifer.name} ? Cette action met fin à sa vie et ne peut pas être annulée.`,
        )
    ) return;

    deathForm.post(route("admin.lifers.kill", props.lifer.id));
};

const formatDate = (value) => {
    if (!value) return "—";
    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));
};
</script>

<template>
    <Head :title="`Administration — ${lifer.name}`" />

    <AppLayout :title="`Administration — ${lifer.name}`" :money="money">
        <div class="lifer-admin-page">
            <Link :href="route('admin.dashboard')" class="back-link">
                ← Retour à l’administration
            </Link>

            <header class="lifer-admin-hero">
                <div>
                    <p class="eyebrow">Contrôle d’un Lifer actif</p>
                    <h1>{{ lifer.name }}</h1>
                    <p>
                        {{ lifer.age }} ans · compte {{ lifer.account.name }}
                        ({{ lifer.account.email }})
                    </p>
                </div>
                <div class="hero-summary">
                    <span>Solde actuel</span>
                    <strong>{{ Number(lifer.money).toLocaleString("fr-FR") }} Lif’coins</strong>
                    <small>Métier : {{ lifer.job || "aucun" }}</small>
                    <small>Étude : {{ lifer.study || "aucune" }}</small>
                </div>
            </header>

            <div v-if="$page.props.flash.success" class="feedback success" role="status">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="Object.keys($page.props.errors || {}).length" class="feedback error" role="alert">
                Une action n’a pas pu être effectuée. Vérifie les champs concernés.
            </div>

            <main class="control-grid">
                <section class="control-card" aria-labelledby="money-title">
                    <div class="section-heading">
                        <p class="eyebrow">Économie</p>
                        <h2 id="money-title">Ajouter ou retirer de l’argent</h2>
                        <p>Utilise un montant positif pour créditer et négatif pour débiter.</p>
                    </div>
                    <form class="admin-form" @submit.prevent="updateMoney">
                        <label>
                            <span>Montant</span>
                            <input
                                v-model="moneyForm.amount"
                                type="number"
                                step="0.01"
                                min="-10000000"
                                max="10000000"
                                placeholder="Ex. 500 ou -100"
                                required
                            />
                            <small v-if="moneyForm.errors.amount">{{ moneyForm.errors.amount }}</small>
                        </label>
                        <label>
                            <span>Raison administrative</span>
                            <textarea v-model="moneyForm.reason" rows="3" maxlength="1000" required />
                            <small v-if="moneyForm.errors.reason">{{ moneyForm.errors.reason }}</small>
                        </label>
                        <button type="submit" class="button primary" :disabled="moneyForm.processing">
                            Modifier le solde
                        </button>
                    </form>
                </section>

                <section class="control-card" aria-labelledby="gauges-title">
                    <div class="section-heading">
                        <p class="eyebrow">État du personnage</p>
                        <h2 id="gauges-title">Modifier les jauges</h2>
                        <p>Chaque valeur doit rester comprise entre 0 et 100.</p>
                    </div>
                    <form class="admin-form" @submit.prevent="updateGauges">
                        <div class="gauge-grid">
                            <label v-for="(label, key) in gaugeLabels" :key="key">
                                <span>{{ label }}</span>
                                <input
                                    v-model.number="gaugeForm.gauges[key]"
                                    type="number"
                                    min="0"
                                    max="100"
                                    required
                                />
                            </label>
                        </div>
                        <label>
                            <span>Raison administrative</span>
                            <textarea v-model="gaugeForm.reason" rows="3" maxlength="1000" required />
                            <small v-if="gaugeForm.errors.reason">{{ gaugeForm.errors.reason }}</small>
                        </label>
                        <button type="submit" class="button primary" :disabled="gaugeForm.processing">
                            Enregistrer les jauges
                        </button>
                    </form>
                </section>

                <section class="control-card" aria-labelledby="health-title">
                    <div class="section-heading">
                        <p class="eyebrow">Santé</p>
                        <h2 id="health-title">Gérer les maladies</h2>
                        <p>Une maladie ajoutée applique immédiatement ses effets habituels.</p>
                    </div>

                    <ul v-if="lifer.sicknesses.length" class="status-list">
                        <li v-for="sickness in lifer.sicknesses" :key="sickness.id">
                            <div>
                                <strong>{{ sickness.name }}</strong>
                                <small>
                                    Contractée le {{ formatDate(sickness.contracted_at) }}
                                    <template v-if="sickness.fatal_at">
                                        · échéance fatale {{ formatDate(sickness.fatal_at) }}
                                    </template>
                                </small>
                            </div>
                            <button type="button" class="button danger compact" @click="removeSickness(sickness)">
                                Retirer
                            </button>
                        </li>
                    </ul>
                    <p v-else class="empty">Aucune maladie active.</p>

                    <form class="admin-form separated" @submit.prevent="addSickness">
                        <label>
                            <span>Maladie à ajouter</span>
                            <select v-model.number="sicknessForm.sickness_id" required>
                                <option value="" disabled>Sélectionner une maladie</option>
                                <option v-for="sickness in availableSicknesses" :key="sickness.id" :value="sickness.id">
                                    {{ sickness.name }}
                                    <template v-if="sickness.fatal_after_days"> — potentiellement fatale</template>
                                </option>
                            </select>
                        </label>
                        <label>
                            <span>Raison administrative</span>
                            <textarea v-model="sicknessForm.reason" rows="3" maxlength="1000" required />
                        </label>
                        <button type="submit" class="button primary" :disabled="sicknessForm.processing || !availableSicknesses.length">
                            Ajouter la maladie
                        </button>
                    </form>
                </section>

                <section class="control-card" aria-labelledby="diploma-title">
                    <div class="section-heading">
                        <p class="eyebrow">Progression</p>
                        <h2 id="diploma-title">Gérer les diplômes</h2>
                        <p>Les modifications contournent le parcours d’étude et restent journalisées.</p>
                    </div>
                    <ul v-if="lifer.diplomas.length" class="simple-list">
                        <li v-for="diploma in lifer.diplomas" :key="diploma.id">{{ diploma.name }}</li>
                    </ul>
                    <p v-else class="empty">Aucun diplôme acquis.</p>

                    <form class="inline-form separated" @submit.prevent="grantDiploma">
                        <select v-model.number="diplomaGrantForm.diplomaId" required>
                            <option value="" disabled>Diplôme à attribuer</option>
                            <option v-for="diploma in availableDiplomas" :key="diploma.id" :value="diploma.id">
                                {{ diploma.name }}
                            </option>
                        </select>
                        <button type="submit" class="button primary" :disabled="!diplomaGrantForm.diplomaId">
                            Attribuer
                        </button>
                    </form>
                    <form class="inline-form" @submit.prevent="removeDiploma">
                        <select v-model.number="diplomaRemoveForm.diplomaId" required>
                            <option value="" disabled>Diplôme à retirer</option>
                            <option v-for="diploma in lifer.diplomas" :key="diploma.id" :value="diploma.id">
                                {{ diploma.name }}
                            </option>
                        </select>
                        <button type="submit" class="button danger" :disabled="!diplomaRemoveForm.diplomaId">
                            Retirer
                        </button>
                    </form>
                </section>

                <section class="control-card danger-zone" aria-labelledby="death-title">
                    <div class="section-heading">
                        <p class="eyebrow">Action irréversible</p>
                        <h2 id="death-title">Enregistrer un décès administratif</h2>
                        <p>
                            Le Lifer mourra immédiatement. Son identité et son histoire seront conservées,
                            mais son état de jeu actif sera supprimé.
                        </p>
                    </div>
                    <form class="admin-form" @submit.prevent="killLifer">
                        <label>
                            <span>Cause publique du décès</span>
                            <input v-model="deathForm.cause" type="text" maxlength="255" required />
                        </label>
                        <label>
                            <span>Justification administrative privée</span>
                            <textarea v-model="deathForm.reason" rows="3" maxlength="1000" required />
                        </label>
                        <button type="submit" class="button danger" :disabled="deathForm.processing">
                            Tuer ce Lifer
                        </button>
                    </form>
                </section>
            </main>
        </div>
    </AppLayout>
</template>

<style scoped>
.lifer-admin-page { display: grid; width: min(1400px, 100%); margin: 0 auto; gap: 24px; }
.back-link { width: fit-content; color: #46324e; font-weight: 800; text-decoration: none; }
.back-link:hover { text-decoration: underline; }
.lifer-admin-hero, .control-card { border: 1px solid rgb(70 50 78 / 10%); border-radius: 28px; background: #faf6f0; box-shadow: 0 18px 45px rgb(64 45 70 / 7%); }
.lifer-admin-hero { display: flex; padding: clamp(28px, 5vw, 56px); align-items: center; justify-content: space-between; gap: 28px; background: radial-gradient(circle at 88% 18%, rgb(214 168 74 / 23%), transparent 32%), #faf6f0; }
.eyebrow { margin: 0 0 9px; color: #6f927b; font-size: 12px; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }
h1, h2 { margin: 0; color: #46324e; font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif; font-weight: 800; letter-spacing: -.04em; }
h1 { font-size: clamp(42px, 6vw, 74px); line-height: 1; }
h2 { font-size: clamp(29px, 3vw, 42px); line-height: 1.05; }
.lifer-admin-hero p:not(.eyebrow), .section-heading > p { color: #847588; line-height: 1.55; }
.hero-summary { display: grid; min-width: 250px; padding: 22px; border-radius: 20px; gap: 5px; color: #46324e; background: #fffaf3; }
.hero-summary span { color: #8d7f90; font-size: 12px; font-weight: 800; text-transform: uppercase; }
.hero-summary strong { font-size: 22px; }
.hero-summary small { color: #806f83; }
.feedback { padding: 15px 18px; border-radius: 15px; font-weight: 700; }
.feedback.success { color: #355b46; background: #e7eee7; }
.feedback.error { color: #8d3946; background: #f5e4e5; }
.control-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 24px; }
.control-card { padding: clamp(24px, 4vw, 40px); }
.section-heading { display: grid; margin-bottom: 24px; gap: 7px; }
.section-heading > p { margin: 4px 0 0; }
.admin-form { display: grid; gap: 16px; }
.admin-form label, .gauge-grid label { display: grid; gap: 7px; }
label > span { color: #736577; font-size: 12px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; }
input, select, textarea { width: 100%; border: 1px solid rgb(70 50 78 / 20%); border-radius: 13px; color: #46324e; background: #fffaf3; font: inherit; }
input, select { min-height: 48px; padding: 10px 13px; }
textarea { padding: 12px 13px; resize: vertical; }
input:focus, select:focus, textarea:focus, .button:focus-visible, .back-link:focus-visible { border-color: #d6a84a; outline: 3px solid rgb(214 168 74 / 42%); outline-offset: 2px; }
label small { color: #9a3e4b; font-weight: 700; }
.gauge-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px; }
.button { display: inline-flex; min-height: 47px; padding: 10px 17px; border: 1px solid transparent; border-radius: 13px; align-items: center; justify-content: center; color: #46324e; font: inherit; font-weight: 800; cursor: pointer; }
.button.primary { background: #d6a84a; }
.button.danger { color: #813643; background: #efdadd; }
.button.compact { min-height: 39px; padding: 7px 12px; }
.button:disabled { opacity: .48; cursor: not-allowed; }
.status-list, .simple-list { display: grid; margin: 0; padding: 0; list-style: none; gap: 9px; }
.status-list li { display: flex; padding: 14px; border: 1px solid rgb(70 50 78 / 8%); border-radius: 14px; align-items: center; justify-content: space-between; gap: 14px; background: #fffaf3; }
.status-list div { display: grid; gap: 4px; }
.status-list strong, .simple-list li { color: #46324e; }
.status-list small { color: #8d7f90; line-height: 1.4; }
.simple-list li { padding: 11px 13px; border-radius: 12px; background: #fffaf3; font-weight: 700; }
.empty { margin: 0; padding: 18px; border-radius: 14px; color: #8d7f90; background: #f3ede6; text-align: center; }
.separated { margin-top: 20px; padding-top: 20px; border-top: 1px solid rgb(70 50 78 / 10%); }
.inline-form { display: grid; grid-template-columns: minmax(0, 1fr) auto; margin-top: 14px; align-items: center; gap: 10px; }
.danger-zone { grid-column: 1 / -1; border-color: rgb(156 60 76 / 22%); background: #fbf2f1; }
@media (max-width: 900px) { .control-grid { grid-template-columns: 1fr; } .danger-zone { grid-column: auto; } .lifer-admin-hero { align-items: stretch; flex-direction: column; } .hero-summary { min-width: 0; } }
@media (max-width: 560px) { .gauge-grid, .inline-form { grid-template-columns: 1fr; } }
@media (prefers-reduced-motion: reduce) { * { scroll-behavior: auto !important; } }
</style>
