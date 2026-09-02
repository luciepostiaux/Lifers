<script setup>
import { computed, ref, watch } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    money: {
        type: [String, Number],
        default: null,
    },
    stats: {
        type: Object,
        required: true,
    },
    users: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    lifers: {
        type: Array,
        required: true,
    },
    diplomas: {
        type: Array,
        required: true,
    },
    bans: {
        type: Array,
        required: true,
    },
    auditLogs: {
        type: Array,
        required: true,
    },
});

const search = ref(props.filters.q ?? "");
const roleFilter = ref(props.filters.role ?? "all");
const selectedLiferId = ref(props.lifers[0]?.id ?? "");

const selectedLifer = computed(() =>
    props.lifers.find((lifer) => Number(lifer.id) === Number(selectedLiferId.value)),
);

const ownedDiplomas = computed(() => {
    const ownedIds = selectedLifer.value?.diploma_ids ?? [];

    return props.diplomas.filter((diploma) => ownedIds.includes(diploma.id));
});

const availableDiplomas = computed(() => {
    const ownedIds = selectedLifer.value?.diploma_ids ?? [];

    return props.diplomas.filter((diploma) => !ownedIds.includes(diploma.id));
});

const grantForm = useForm({
    liferId: selectedLiferId.value,
    diplomaId: "",
});

const removeForm = useForm({
    liferId: selectedLiferId.value,
    diplomaId: "",
});

const banForm = useForm({
    email: "",
    reason: "",
    block_known_ip_addresses: false,
});

watch(selectedLiferId, (liferId) => {
    grantForm.liferId = liferId;
    grantForm.diplomaId = "";
    removeForm.liferId = liferId;
    removeForm.diplomaId = "";
});

const applyFilters = () => {
    router.get(
        route("admin.dashboard"),
        {
            q: search.value || undefined,
            role: roleFilter.value === "all" ? undefined : roleFilter.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = "";
    roleFilter.value = "all";
    applyFilters();
};

const roleLabel = (role) => ({
    admin: "Administratrice",
    moderator: "Modération",
    user: "Utilisateur",
}[role] ?? role);

const updateRole = (user, event) => {
    const role = event.target.value;

    if (role === user.role) return;

    const confirmed = window.confirm(
        `Confirmer le passage de ${user.account_name} au rôle « ${roleLabel(role)} » ?`,
    );

    if (!confirmed) {
        event.target.value = user.role;
        return;
    }

    router.patch(
        route("admin.users.role.update", user.id),
        { role },
        {
            preserveScroll: true,
            onError: () => {
                event.target.value = user.role;
            },
        },
    );
};

const grantDiploma = () => {
    grantForm.post(route("admin.grantDiploma"), {
        preserveScroll: true,
        onSuccess: () => grantForm.reset("diplomaId"),
    });
};

const removeDiploma = () => {
    if (!window.confirm("Retirer ce diplôme du Lifer sélectionné ?")) return;

    removeForm.post(route("admin.removeDiploma"), {
        preserveScroll: true,
        onSuccess: () => removeForm.reset("diplomaId"),
    });
};

const banEmail = () => {
    if (!window.confirm(`Bloquer durablement l’adresse ${banForm.email} ?`)) return;

    banForm.post(route("admin.bans.store"), {
        preserveScroll: true,
        onSuccess: () => banForm.reset(),
    });
};

const banUser = (user) => {
    const reason = window.prompt(
        `Indique la raison du bannissement de ${user.account_name}.`,
    );
    if (!reason?.trim()) return;

    const blockKnownIpAddresses = window.confirm(
        "Bloquer également les adresses IP actuellement connues ? Attention : une IP peut être partagée par plusieurs personnes.",
    );

    router.post(
        route("admin.bans.store"),
        {
            email: user.email,
            reason,
            block_known_ip_addresses: blockKnownIpAddresses,
        },
        { preserveScroll: true },
    );
};

const unban = (ban) => {
    const reason = window.prompt(
        `Pourquoi lever le bannissement de ${ban.email} ?`,
    );
    if (!reason?.trim()) return;

    router.delete(route("admin.bans.destroy", ban.id), {
        data: { reason },
        preserveScroll: true,
    });
};

const formatDate = (value, withTime = false) => {
    if (!value) return "—";

    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "short",
        year: "numeric",
        ...(withTime ? { hour: "2-digit", minute: "2-digit" } : {}),
    }).format(new Date(value));
};

const auditDescription = (log) => {
    if (log.action === "role.updated") {
        return `${roleLabel(log.context?.from)} → ${roleLabel(log.context?.to)}`;
    }

    if (log.action === "diploma.granted") {
        return `Diplôme « ${log.context?.diploma_name} » attribué à ${log.context?.lifer_name}`;
    }

    if (log.action === "diploma.removed") {
        return `Diplôme « ${log.context?.diploma_name} » retiré à ${log.context?.lifer_name}`;
    }

    if (log.action === "account.banned") {
        return `Adresse ${log.context?.email} bannie · ${log.context?.reason}`;
    }

    if (log.action === "account.unbanned") {
        return `Bannissement de ${log.context?.email} levé`;
    }

    if (log.action === "lifer.killed") {
        return `${log.context?.lifer_name} déclaré mort · ${log.context?.death_cause}`;
    }

    if (log.action === "lifer.money.updated") {
        return `Solde de ${log.context?.lifer_name} : ${log.context?.before} → ${log.context?.after}`;
    }

    if (log.action === "lifer.gauges.updated") {
        return `Jauges de ${log.context?.lifer_name} modifiées`;
    }

    if (log.action === "lifer.sickness.added") {
        return `Maladie « ${log.context?.sickness_name} » ajoutée à ${log.context?.lifer_name}`;
    }

    if (log.action === "lifer.sickness.removed") {
        return `Maladie « ${log.context?.sickness_name} » retirée à ${log.context?.lifer_name}`;
    }

    return log.action;
};
</script>

<template>
    <AppLayout title="Administration" :money="money">
        <div class="admin-page">
            <section class="admin-hero" aria-labelledby="admin-title">
                <div>
                    <p class="admin-eyebrow">Espace protégé</p>
                    <h1 id="admin-title">Administration de Lifers</h1>
                    <p>
                        Gère les accès sensibles et la progression sans utiliser
                        d’identifiants techniques.
                    </p>
                </div>
                <span class="admin-hero__badge">Administratrice principale</span>
            </section>

            <div
                v-if="$page.props.flash.success"
                class="admin-feedback admin-feedback--success"
                role="status"
            >
                {{ $page.props.flash.success }}
            </div>
            <div
                v-if="$page.props.flash.error"
                class="admin-feedback admin-feedback--error"
                role="alert"
            >
                {{ $page.props.flash.error }}
            </div>
            <div
                v-if="$page.props.flash.warning"
                class="admin-feedback admin-feedback--warning"
                role="status"
            >
                {{ $page.props.flash.warning }}
            </div>
            <div
                v-if="$page.props.errors?.role"
                class="admin-feedback admin-feedback--error"
                role="alert"
            >
                {{ $page.props.errors.role }}
            </div>

            <section class="admin-stats" aria-label="Vue d’ensemble">
                <article>
                    <span>Comptes</span>
                    <strong>{{ stats.users }}</strong>
                    <small>inscrits au total</small>
                </article>
                <article>
                    <span>Modération</span>
                    <strong>{{ stats.moderators }}</strong>
                    <small>compte(s) désigné(s)</small>
                </article>
                <article>
                    <span>Lifers actifs</span>
                    <strong>{{ stats.active_lifers }}</strong>
                    <small>vies actuellement jouées</small>
                </article>
                <article>
                    <span>Commentaires</span>
                    <strong>{{ stats.pending_comments }}</strong>
                    <small>en attente de leur propriétaire</small>
                </article>
                <article>
                    <span>Bannissements</span>
                    <strong>{{ stats.banned_accounts }}</strong>
                    <small>adresse(s) actuellement bloquée(s)</small>
                </article>
            </section>

            <section class="admin-panel" aria-labelledby="accounts-title">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-eyebrow">Accès et responsabilités</p>
                        <h2 id="accounts-title">Comptes et rôles</h2>
                    </div>
                    <p>
                        Le compte principal est verrouillé. Les autres comptes
                        peuvent être utilisateurs ou membres de la modération.
                    </p>
                </div>

                <form class="admin-filters" @submit.prevent="applyFilters">
                    <label>
                        <span>Rechercher</span>
                        <input
                            v-model="search"
                            type="search"
                            placeholder="Compte, e-mail ou nom du Lifer"
                        />
                    </label>
                    <label>
                        <span>Rôle</span>
                        <select v-model="roleFilter">
                            <option value="all">Tous les rôles</option>
                            <option value="admin">Administration</option>
                            <option value="moderator">Modération</option>
                            <option value="user">Utilisateurs</option>
                        </select>
                    </label>
                    <button type="submit" class="admin-button admin-button--primary">
                        Filtrer
                    </button>
                    <button
                        v-if="filters.q || filters.role !== 'all'"
                        type="button"
                        class="admin-button admin-button--quiet"
                        @click="resetFilters"
                    >
                        Effacer
                    </button>
                </form>

                <div v-if="users.data.length" class="admin-user-list">
                    <article
                        v-for="user in users.data"
                        :key="user.id"
                        class="admin-user"
                    >
                        <div class="admin-user__identity">
                            <span aria-hidden="true">
                                {{ user.account_name.slice(0, 1).toUpperCase() }}
                            </span>
                            <div>
                                <strong>{{ user.account_name }}</strong>
                                <small>{{ user.email }}</small>
                            </div>
                        </div>
                        <div class="admin-user__lifer">
                            <span>Lifer actif</span>
                            <strong>{{ user.lifer_name || "Aucun" }}</strong>
                        </div>
                        <div class="admin-user__date">
                            <span>Inscription</span>
                            <strong>{{ formatDate(user.created_at) }}</strong>
                        </div>
                        <div class="admin-user__role">
                            <span>Rôle</span>
                            <span
                                v-if="user.is_protected"
                                class="admin-role-badge admin-role-badge--admin"
                            >
                                Administratrice
                            </span>
                            <select
                                v-else
                                :value="user.role"
                                :aria-label="`Rôle de ${user.account_name}`"
                                @change="updateRole(user, $event)"
                            >
                                <option value="user">Utilisateur</option>
                                <option value="moderator">Modération</option>
                            </select>
                        </div>
                        <div class="admin-user__actions">
                            <Link
                                v-if="user.active_lifer_id"
                                :href="route('admin.lifers.show', user.active_lifer_id)"
                                class="admin-button admin-button--quiet admin-button--small"
                            >
                                Gérer le Lifer
                            </Link>
                            <span
                                v-if="user.is_banned"
                                class="admin-role-badge admin-role-badge--banned"
                            >
                                Banni
                            </span>
                            <button
                                v-else-if="!user.is_protected"
                                type="button"
                                class="admin-button admin-button--danger admin-button--small"
                                @click="banUser(user)"
                            >
                                Bannir
                            </button>
                        </div>
                    </article>
                </div>
                <p v-else class="admin-empty">
                    Aucun compte ne correspond à cette recherche.
                </p>

                <nav
                    v-if="users.last_page > 1"
                    class="admin-pagination"
                    aria-label="Pages des comptes"
                >
                    <Link
                        v-for="link in users.links"
                        :key="link.label"
                        :href="link.url || '#accounts-title'"
                        :class="{
                            'is-active': link.active,
                            'is-disabled': !link.url,
                        }"
                        preserve-scroll
                        v-html="link.label"
                    />
                </nav>
            </section>

            <section class="admin-panel" aria-labelledby="bans-title">
                <div class="admin-section-heading">
                    <div>
                        <p class="admin-eyebrow">Sécurité et exclusion</p>
                        <h2 id="bans-title">Bannissements</h2>
                    </div>
                    <p>
                        Le blocage d’une adresse empêche toute connexion et réinscription.
                        Le blocage IP est facultatif car une adresse peut être partagée.
                    </p>
                </div>

                <form class="admin-ban-form" @submit.prevent="banEmail">
                    <label class="admin-field">
                        <span>Adresse e-mail à bloquer</span>
                        <input v-model="banForm.email" type="email" required />
                        <small v-if="banForm.errors.email">{{ banForm.errors.email }}</small>
                    </label>
                    <label class="admin-field admin-field--wide">
                        <span>Raison</span>
                        <textarea v-model="banForm.reason" rows="3" maxlength="1000" required />
                        <small v-if="banForm.errors.reason">{{ banForm.errors.reason }}</small>
                    </label>
                    <label class="admin-checkbox-line">
                        <input v-model="banForm.block_known_ip_addresses" type="checkbox" />
                        <span>Bloquer aussi les IP connues si ce compte existe déjà</span>
                    </label>
                    <button type="submit" class="admin-button admin-button--danger" :disabled="banForm.processing">
                        Bloquer cette adresse
                    </button>
                </form>

                <div v-if="bans.length" class="admin-ban-list">
                    <article v-for="ban in bans" :key="ban.id">
                        <div>
                            <strong>{{ ban.email }}</strong>
                            <small>
                                {{ ban.account_name || "Aucun compte associé" }} ·
                                bloqué par {{ ban.banned_by }} le {{ formatDate(ban.banned_at, true) }}
                            </small>
                            <p>{{ ban.reason }}</p>
                            <small v-if="ban.masked_ip_addresses.length">
                                IP bloquée(s) : {{ ban.masked_ip_addresses.join(", ") }}
                            </small>
                        </div>
                        <button type="button" class="admin-button admin-button--quiet admin-button--small" @click="unban(ban)">
                            Lever le bannissement
                        </button>
                    </article>
                </div>
                <p v-else class="admin-empty">Aucun bannissement actif.</p>
            </section>

            <div class="admin-columns">
                <section class="admin-panel" aria-labelledby="diplomas-title">
                    <div class="admin-section-heading admin-section-heading--stacked">
                        <div>
                            <p class="admin-eyebrow">Progression exceptionnelle</p>
                            <h2 id="diplomas-title">Gestion des diplômes</h2>
                        </div>
                        <p>
                            Ces actions contournent le parcours d’étude normal et
                            sont donc consignées dans l’historique.
                        </p>
                    </div>

                    <label class="admin-field">
                        <span>Choisir un Lifer actif</span>
                        <select v-model.number="selectedLiferId">
                            <option v-if="!lifers.length" value="">
                                Aucun Lifer actif
                            </option>
                            <option
                                v-for="lifer in lifers"
                                :key="lifer.id"
                                :value="lifer.id"
                            >
                                {{ lifer.name }}
                            </option>
                        </select>
                    </label>

                    <form class="admin-diploma-action" @submit.prevent="grantDiploma">
                        <label class="admin-field">
                            <span>Diplôme à attribuer</span>
                            <select
                                v-model.number="grantForm.diplomaId"
                                :disabled="!availableDiplomas.length"
                                required
                            >
                                <option value="" disabled>
                                    {{
                                        availableDiplomas.length
                                            ? "Sélectionner un diplôme"
                                            : "Tous les diplômes sont acquis"
                                    }}
                                </option>
                                <option
                                    v-for="diploma in availableDiplomas"
                                    :key="diploma.id"
                                    :value="diploma.id"
                                >
                                    {{ diploma.name }}
                                </option>
                            </select>
                        </label>
                        <button
                            type="submit"
                            class="admin-button admin-button--primary"
                            :disabled="grantForm.processing || !grantForm.diplomaId"
                        >
                            Attribuer
                        </button>
                    </form>

                    <form class="admin-diploma-action" @submit.prevent="removeDiploma">
                        <label class="admin-field">
                            <span>Diplôme à retirer</span>
                            <select
                                v-model.number="removeForm.diplomaId"
                                :disabled="!ownedDiplomas.length"
                                required
                            >
                                <option value="" disabled>
                                    {{
                                        ownedDiplomas.length
                                            ? "Sélectionner un diplôme acquis"
                                            : "Aucun diplôme acquis"
                                    }}
                                </option>
                                <option
                                    v-for="diploma in ownedDiplomas"
                                    :key="diploma.id"
                                    :value="diploma.id"
                                >
                                    {{ diploma.name }}
                                </option>
                            </select>
                        </label>
                        <button
                            type="submit"
                            class="admin-button admin-button--danger"
                            :disabled="removeForm.processing || !removeForm.diplomaId"
                        >
                            Retirer
                        </button>
                    </form>
                </section>

                <section class="admin-panel" aria-labelledby="audit-title">
                    <div class="admin-section-heading admin-section-heading--stacked">
                        <div>
                            <p class="admin-eyebrow">Traçabilité</p>
                            <h2 id="audit-title">Dernières actions sensibles</h2>
                        </div>
                        <p>Les dix opérations administratives les plus récentes.</p>
                    </div>

                    <ol v-if="auditLogs.length" class="admin-audit-list">
                        <li v-for="log in auditLogs" :key="log.id">
                            <span class="admin-audit-list__marker" aria-hidden="true"></span>
                            <div>
                                <strong>{{ auditDescription(log) }}</strong>
                                <small>
                                    Par {{ log.actor }}
                                    <template v-if="log.target">
                                        · compte {{ log.target }}
                                    </template>
                                    · {{ formatDate(log.created_at, true) }}
                                </small>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="admin-empty">
                        Aucune action sensible n’a encore été enregistrée.
                    </p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.admin-page {
    display: grid;
    width: min(1480px, 100%);
    margin: 0 auto;
    gap: 24px;
}

.admin-hero,
.admin-panel,
.admin-stats article {
    border: 1px solid rgb(70 50 78 / 9%);
    background: #faf6f0;
    box-shadow: 0 18px 45px rgb(64 45 70 / 7%);
}

.admin-hero {
    display: flex;
    min-height: 230px;
    padding: clamp(30px, 5vw, 64px);
    border-radius: 30px;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    background:
        radial-gradient(circle at 87% 14%, rgb(214 168 74 / 24%), transparent 31%),
        linear-gradient(135deg, #faf6f0 0%, #f4eee5 100%);
}

.admin-eyebrow {
    margin: 0 0 10px;
    color: #6f927b;
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.16em;
    text-transform: uppercase;
}

.admin-hero h1,
.admin-section-heading h2 {
    margin: 0;
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-weight: 800;
    letter-spacing: -0.045em;
}

.admin-hero h1 {
    max-width: 760px;
    font-size: clamp(42px, 6vw, 78px);
    line-height: 0.98;
}

.admin-hero p:not(.admin-eyebrow) {
    max-width: 650px;
    margin: 22px 0 0;
    color: #806f83;
    font-size: 18px;
    line-height: 1.6;
}

.admin-hero__badge,
.admin-role-badge {
    display: inline-flex;
    min-height: 42px;
    padding: 10px 16px;
    border-radius: 999px;
    align-items: center;
    color: #46324e;
    background: #ead6a8;
    font-size: 14px;
    font-weight: 800;
    white-space: nowrap;
}

.admin-feedback {
    padding: 15px 18px;
    border: 1px solid transparent;
    border-radius: 16px;
    font-weight: 700;
}

.admin-feedback--success {
    border-color: rgb(111 146 123 / 30%);
    color: #355b46;
    background: #e7eee7;
}

.admin-feedback--error {
    border-color: rgb(166 70 84 / 26%);
    color: #8d3946;
    background: #f5e4e5;
}

.admin-feedback--warning {
    border-color: rgb(214 168 74 / 35%);
    color: #795b1f;
    background: #f7ecd2;
}

.admin-stats {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 16px;
}

.admin-stats article {
    display: grid;
    min-height: 154px;
    padding: 24px;
    border-radius: 22px;
    align-content: center;
    gap: 5px;
}

.admin-stats span,
.admin-user__lifer span,
.admin-user__date span,
.admin-user__role > span:first-child,
.admin-field > span,
.admin-filters label > span {
    color: #8d7f90;
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.admin-stats strong {
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: 38px;
    line-height: 1;
}

.admin-stats small {
    color: #8d7f90;
    font-size: 13px;
}

.admin-panel {
    padding: clamp(24px, 4vw, 42px);
    border-radius: 28px;
}

.admin-section-heading {
    display: flex;
    margin-bottom: 28px;
    align-items: end;
    justify-content: space-between;
    gap: 24px;
}

.admin-section-heading--stacked {
    display: grid;
    align-items: start;
    gap: 10px;
}

.admin-section-heading h2 {
    font-size: clamp(30px, 4vw, 46px);
    line-height: 1;
}

.admin-section-heading > p {
    max-width: 540px;
    margin: 0;
    color: #8d7f90;
    line-height: 1.55;
}

.admin-filters {
    display: grid;
    grid-template-columns: minmax(240px, 1fr) minmax(180px, 250px) auto auto;
    margin-bottom: 24px;
    align-items: end;
    gap: 12px;
}

.admin-filters label,
.admin-field {
    display: grid;
    gap: 8px;
}

.admin-filters input,
.admin-filters select,
.admin-field select,
.admin-field input,
.admin-field textarea,
.admin-user__role select {
    width: 100%;
    min-height: 48px;
    border: 1px solid rgb(70 50 78 / 19%);
    border-radius: 13px;
    color: #46324e;
    background: #fffaf3;
    font: inherit;
    font-weight: 600;
}

.admin-filters input,
.admin-filters select,
.admin-field select,
.admin-field input,
.admin-field textarea {
    padding: 10px 14px;
}

.admin-field textarea {
    resize: vertical;
}

.admin-user__role select {
    min-width: 154px;
    padding: 8px 12px;
}

.admin-filters input:focus,
.admin-filters select:focus,
.admin-field select:focus,
.admin-field input:focus,
.admin-field textarea:focus,
.admin-user__role select:focus,
.admin-button:focus-visible,
.admin-pagination a:focus-visible {
    outline: 3px solid rgb(214 168 74 / 45%);
    outline-offset: 2px;
    border-color: #d6a84a;
}

.admin-button {
    display: inline-flex;
    min-height: 48px;
    padding: 11px 18px;
    border: 1px solid transparent;
    border-radius: 13px;
    align-items: center;
    justify-content: center;
    color: #46324e;
    font: inherit;
    font-weight: 800;
    cursor: pointer;
    transition: transform 160ms ease, box-shadow 160ms ease, opacity 160ms ease;
}

.admin-button:hover:not(:disabled) {
    transform: translateY(-1px);
}

.admin-button:disabled {
    opacity: 0.48;
    cursor: not-allowed;
}

.admin-button--primary {
    background: #d6a84a;
    box-shadow: 0 9px 22px rgb(170 125 36 / 18%);
}

.admin-button--quiet {
    border-color: rgb(70 50 78 / 15%);
    background: #f1ebe4;
}

.admin-button--danger {
    color: #813643;
    background: #efdadd;
}

.admin-button--small {
    min-height: 38px;
    padding: 8px 12px;
    font-size: 13px;
    text-decoration: none;
}

.admin-user-list {
    display: grid;
    gap: 10px;
}

.admin-user {
    display: grid;
    grid-template-columns: minmax(220px, 1.4fr) minmax(145px, .8fr) minmax(125px, .65fr) minmax(155px, .75fr) minmax(145px, .75fr);
    min-height: 88px;
    padding: 15px 16px;
    border: 1px solid rgb(70 50 78 / 8%);
    border-radius: 17px;
    align-items: center;
    gap: 18px;
    background: #fffaf3;
}

.admin-user__identity {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 12px;
}

.admin-user__identity > span {
    display: inline-flex;
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    border-radius: 50%;
    align-items: center;
    justify-content: center;
    color: #46324e;
    background: #e5d5e4;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: 19px;
    font-weight: 800;
}

.admin-user__identity div,
.admin-user__lifer,
.admin-user__date,
.admin-user__role {
    display: grid;
    min-width: 0;
    gap: 4px;
}

.admin-user__actions {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-end;
    gap: 7px;
}

.admin-role-badge--banned {
    color: #813643;
    background: #efdadd;
}

.admin-ban-form {
    display: grid;
    grid-template-columns: minmax(220px, .8fr) minmax(300px, 1.3fr) auto;
    margin-bottom: 26px;
    align-items: end;
    gap: 14px;
}

.admin-field--wide {
    grid-row: span 2;
}

.admin-field small {
    color: #9a3e4b;
    font-weight: 700;
}

.admin-checkbox-line {
    display: flex;
    max-width: 320px;
    align-items: flex-start;
    gap: 9px;
    color: #806f83;
    font-size: 13px;
    line-height: 1.4;
}

.admin-checkbox-line input {
    margin-top: 2px;
}

.admin-ban-list {
    display: grid;
    gap: 10px;
}

.admin-ban-list article {
    display: flex;
    padding: 16px;
    border: 1px solid rgb(151 56 72 / 12%);
    border-radius: 17px;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    background: #fff8f4;
}

.admin-ban-list article > div {
    display: grid;
    gap: 4px;
}

.admin-ban-list strong { color: #46324e; }
.admin-ban-list small { color: #8d7f90; }
.admin-ban-list p { margin: 3px 0; color: #6f5d72; }

.admin-user__identity strong,
.admin-user__identity small,
.admin-user__lifer strong {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.admin-user__identity strong,
.admin-user__lifer strong,
.admin-user__date strong {
    color: #46324e;
    font-size: 14px;
}

.admin-user__identity small {
    color: #8d7f90;
    font-size: 13px;
}

.admin-role-badge {
    min-height: 38px;
    width: fit-content;
    padding: 8px 13px;
    font-size: 13px;
}

.admin-pagination {
    display: flex;
    margin-top: 22px;
    flex-wrap: wrap;
    justify-content: center;
    gap: 7px;
}

.admin-pagination a {
    display: inline-flex;
    min-width: 40px;
    min-height: 40px;
    padding: 8px 12px;
    border-radius: 11px;
    align-items: center;
    justify-content: center;
    color: #46324e;
    background: #f1ebe4;
    font-size: 13px;
    font-weight: 800;
    text-decoration: none;
}

.admin-pagination a.is-active {
    background: #d6a84a;
}

.admin-pagination a.is-disabled {
    opacity: 0.38;
    pointer-events: none;
}

.admin-columns {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.admin-diploma-action {
    display: grid;
    grid-template-columns: minmax(0, 1fr) auto;
    margin-top: 18px;
    align-items: end;
    gap: 12px;
}

.admin-diploma-action + .admin-diploma-action {
    padding-top: 18px;
    border-top: 1px solid rgb(70 50 78 / 9%);
}

.admin-audit-list {
    display: grid;
    margin: 0;
    padding: 0;
    list-style: none;
    gap: 5px;
}

.admin-audit-list li {
    display: grid;
    grid-template-columns: 12px minmax(0, 1fr);
    padding: 13px 0;
    align-items: start;
    gap: 12px;
}

.admin-audit-list li + li {
    border-top: 1px solid rgb(70 50 78 / 8%);
}

.admin-audit-list__marker {
    width: 9px;
    height: 9px;
    margin-top: 5px;
    border-radius: 50%;
    background: #6f927b;
}

.admin-audit-list div {
    display: grid;
    gap: 5px;
}

.admin-audit-list strong {
    color: #46324e;
    font-size: 14px;
    line-height: 1.45;
}

.admin-audit-list small {
    color: #8d7f90;
    line-height: 1.5;
}

.admin-empty {
    margin: 0;
    padding: 24px;
    border-radius: 16px;
    color: #8d7f90;
    background: #f3ede6;
    text-align: center;
}

@media (max-width: 1180px) {
    .admin-stats {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .admin-user {
        grid-template-columns: minmax(220px, 1fr) minmax(150px, 0.8fr) minmax(170px, 0.8fr);
    }

    .admin-user__date {
        display: none;
    }

    .admin-columns {
        grid-template-columns: 1fr;
    }

    .admin-ban-form {
        grid-template-columns: minmax(220px, 0.8fr) minmax(300px, 1.2fr);
    }

    .admin-field--wide {
        grid-row: auto;
    }
}

@media (max-width: 820px) {
    .admin-hero,
    .admin-section-heading {
        display: grid;
        align-items: start;
    }

    .admin-hero__badge {
        width: fit-content;
    }

    .admin-filters {
        grid-template-columns: 1fr 1fr;
    }

    .admin-user {
        grid-template-columns: 1fr 1fr;
    }

    .admin-user__identity {
        grid-column: 1 / -1;
    }

    .admin-ban-form {
        grid-template-columns: 1fr;
    }

    .admin-checkbox-line {
        max-width: none;
    }
}

@media (max-width: 560px) {
    .admin-page {
        gap: 16px;
    }

    .admin-hero,
    .admin-panel {
        padding: 24px 18px;
        border-radius: 22px;
    }

    .admin-stats,
    .admin-filters,
    .admin-user,
    .admin-diploma-action {
        grid-template-columns: 1fr;
    }

    .admin-stats article {
        min-height: 125px;
    }

    .admin-user__identity,
    .admin-user__lifer,
    .admin-user__role {
        grid-column: auto;
    }

    .admin-button,
    .admin-role-badge {
        width: 100%;
    }

    .admin-ban-list article {
        align-items: stretch;
        flex-direction: column;
    }
}

@media (prefers-reduced-motion: reduce) {
    .admin-button {
        transition: none;
    }
}
</style>
