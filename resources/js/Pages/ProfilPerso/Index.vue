<script setup>
import { computed, ref, watch } from "vue";
import { Link, router, useForm } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfileRichContent from "@/Components/Profile/ProfileRichContent.vue";
import ProfileRichEditor from "@/Components/Profile/ProfileRichEditor.vue";
import { emptyProfileDocument } from "@/Components/Profile/profileEditorExtensions";

const props = defineProps({
    profileLifer: { type: Object, required: true },
    relationshipOptions: { type: Array, default: () => [] },
    diplomas: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] },
    isOwner: Boolean,
    viewerLiferId: Number,
    money: [String, Number],
});

const editing = ref(false);
const freshDocument = () => JSON.parse(JSON.stringify(emptyProfileDocument));

const profileForm = useForm({
    content: props.profileLifer.content ?? freshDocument(),
    show_money: Boolean(props.profileLifer.show_money),
    relationship_status: props.profileLifer.relationship_status ?? "",
    public_diploma_ids: props.diplomas.filter(({ is_public }) => is_public).map(({ id }) => id),
});

const commentForm = useForm({ content: "" });

const bodyImageSrc = computed(() => {
    const path = props.profileLifer.body_image_url;
    if (!path) return null;
    return path.startsWith("/") ? path : `/${path}`;
});

const formattedMoney = computed(() => {
    const amount = Number(props.profileLifer.money);
    if (!Number.isFinite(amount)) return null;
    return new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(amount);
});

const hasPresentation = computed(() => {
    const nodes = props.profileLifer.content?.content ?? [];
    return nodes.some((node) => node.type === "image" || JSON.stringify(node).includes('"text"'));
});

const publicDiplomas = computed(() => props.diplomas.filter(({ is_public }) => is_public));
const pendingCount = computed(() => props.comments.filter(({ status }) => status === "pending").length);
const firstName = computed(() => props.profileLifer.name.split(" ")[0]);

watch(
    () => props.profileLifer,
    (profile) => {
        if (editing.value) return;
        profileForm.content = profile.content ?? freshDocument();
        profileForm.show_money = Boolean(profile.show_money);
        profileForm.relationship_status = profile.relationship_status ?? "";
    },
    { deep: true },
);

function openEditor() {
    profileForm.clearErrors();
    profileForm.content = props.profileLifer.content ?? freshDocument();
    profileForm.show_money = Boolean(props.profileLifer.show_money);
    profileForm.relationship_status = props.profileLifer.relationship_status ?? "";
    profileForm.public_diploma_ids = props.diplomas.filter(({ is_public }) => is_public).map(({ id }) => id);
    editing.value = true;
}

function cancelEditor() {
    if (profileForm.processing) return;
    profileForm.reset();
    profileForm.clearErrors();
    editing.value = false;
}

function saveProfile() {
    profileForm.put(route("profil.update"), {
        preserveScroll: true,
        onSuccess: () => { editing.value = false; },
    });
}

function toggleMoneyVisibility() {
    profileForm.show_money = !profileForm.show_money;
}

function toggleDiplomaVisibility(diplomaId) {
    const selectedIds = profileForm.public_diploma_ids.map(Number);

    profileForm.public_diploma_ids = selectedIds.includes(Number(diplomaId))
        ? selectedIds.filter((id) => id !== Number(diplomaId))
        : [...selectedIds, Number(diplomaId)];
}

function diplomaIsSelected(diplomaId) {
    return profileForm.public_diploma_ids.map(Number).includes(Number(diplomaId));
}

function publishComment() {
    commentForm.post(route("lifers.profile.comments.store", props.profileLifer.id), {
        preserveScroll: true,
        onSuccess: () => commentForm.reset(),
    });
}

function approveComment(comment) {
    router.patch(route("profil.comments.approve", comment.id), {}, { preserveScroll: true });
}

function deleteComment(comment) {
    const prompt = comment.status === "pending" && props.isOwner
        ? "Refuser et supprimer ce commentaire ?"
        : "Supprimer définitivement ce commentaire ?";
    if (window.confirm(prompt)) {
        router.delete(route("profil.comments.destroy", comment.id), { preserveScroll: true });
    }
}

function initials(name) {
    return name.split(/\s+/).filter(Boolean).slice(0, 2).map((part) => part[0]).join("");
}

function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "long",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
}
</script>

<template>
    <AppLayout :title="isOwner ? 'Mon profil' : `Profil de ${profileLifer.name}`" :money="money">
        <div class="lifer-profile-page">
            <section class="lifer-profile-hero" aria-labelledby="profile-name">
                <div class="lifer-profile-hero__portrait">
                    <img v-if="bodyImageSrc" :src="bodyImageSrc" :alt="`Lifer ${profileLifer.name}`" decoding="async" />
                    <span v-else>Visuel indisponible</span>
                </div>

                <div class="lifer-profile-hero__identity">
                    <span class="lifer-profile-kicker">{{ isOwner ? "Ton profil public" : "Profil public" }}</span>
                    <h1 id="profile-name">{{ profileLifer.name }}</h1>
                    <p v-if="profileLifer.job" class="lifer-profile-hero__job">{{ profileLifer.job }}</p>
                    <p v-else class="lifer-profile-hero__job lifer-profile-hero__job--muted">Aucun métier actuellement</p>

                    <div class="lifer-profile-hero__facts" aria-label="Informations du Lifer">
                        <span>{{ profileLifer.age }} ans</span>
                        <span v-if="profileLifer.relationship" class="lifer-profile-relationship">
                            {{ profileLifer.relationship.label }}
                            <Link
                                v-if="profileLifer.relationship.spouse"
                                :href="route('lifers.profile.show', profileLifer.relationship.spouse.id)"
                            >
                                {{ profileLifer.relationship.spouse.name }}
                            </Link>
                        </span>
                        <span v-if="formattedMoney !== null">
                            {{ formattedMoney }} Lif’coins
                            <small v-if="isOwner && !profileLifer.show_money">Privé</small>
                        </span>
                    </div>

                    <button v-if="isOwner && !editing" type="button" class="lifer-profile-button lifer-profile-button--primary" @click="openEditor">
                        Personnaliser mon profil
                    </button>
                </div>
            </section>

            <div class="lifer-profile-grid">
                <main class="lifer-profile-main">
                    <section class="lifer-profile-card" aria-labelledby="presentation-title">
                        <div class="lifer-profile-section-heading">
                            <div>
                                <span class="lifer-profile-kicker">Présentation</span>
                                <h2 id="presentation-title">{{ isOwner ? "Mon espace" : `À propos de ${firstName}` }}</h2>
                            </div>
                            <button v-if="isOwner && !editing" type="button" class="lifer-profile-text-button" @click="openEditor">Modifier</button>
                        </div>

                        <form v-if="isOwner && editing" class="lifer-profile-editor-form" @submit.prevent="saveProfile">
                            <ProfileRichEditor v-model="profileForm.content" />
                            <p v-if="profileForm.errors.content" class="lifer-profile-error" role="alert">{{ profileForm.errors.content }}</p>

                            <fieldset class="lifer-profile-visibility">
                                <legend>Informations visibles</legend>
                                <label class="lifer-profile-relationship-field" for="relationship-status">
                                    <span>Situation sentimentale</span>
                                    <select id="relationship-status" v-model="profileForm.relationship_status">
                                        <option value="">Ne pas afficher</option>
                                        <option
                                            v-for="option in relationshipOptions"
                                            :key="option.value"
                                            :value="option.value"
                                        >
                                            {{ option.label }}
                                        </option>
                                    </select>
                                    <small>Ce choix sera visible dans l’en-tête de ton profil public.</small>
                                </label>
                                <p v-if="profileForm.errors.relationship_status" class="lifer-profile-error" role="alert">
                                    {{ profileForm.errors.relationship_status }}
                                </p>

                                <button
                                    type="button"
                                    class="lifer-profile-toggle"
                                    :class="{ 'is-selected': profileForm.show_money }"
                                    role="switch"
                                    :aria-checked="profileForm.show_money"
                                    @click="toggleMoneyVisibility"
                                >
                                    <span class="lifer-profile-choice-box" aria-hidden="true">{{ profileForm.show_money ? "✓" : "" }}</span>
                                    <span><strong>Afficher mes Lif’coins</strong><small>Ton montant sera visible par les autres Lifers.</small></span>
                                </button>

                                <div v-if="diplomas.length" class="lifer-profile-diploma-options">
                                    <span>Diplômes affichés</span>
                                    <button
                                        v-for="diploma in diplomas"
                                        :key="diploma.id"
                                        type="button"
                                        class="lifer-profile-diploma-option"
                                        :class="{ 'is-selected': diplomaIsSelected(diploma.id) }"
                                        role="checkbox"
                                        :aria-checked="diplomaIsSelected(diploma.id)"
                                        @click="toggleDiplomaVisibility(diploma.id)"
                                    >
                                        <span class="lifer-profile-choice-box" aria-hidden="true">{{ diplomaIsSelected(diploma.id) ? "✓" : "" }}</span>
                                        <span>{{ diploma.name }}</span>
                                    </button>
                                </div>
                            </fieldset>

                            <div class="lifer-profile-form-actions">
                                <button type="button" class="lifer-profile-button lifer-profile-button--secondary" :disabled="profileForm.processing" @click="cancelEditor">Annuler</button>
                                <button type="submit" class="lifer-profile-button lifer-profile-button--primary" :disabled="profileForm.processing">{{ profileForm.processing ? "Enregistrement…" : "Enregistrer" }}</button>
                            </div>
                        </form>

                        <ProfileRichContent v-else-if="hasPresentation" :content="profileLifer.content" />
                        <div v-else class="lifer-profile-empty">
                            <p>{{ isOwner ? "Présente ton Lifer, raconte son histoire ou partage ce qui lui tient à cœur." : "Ce Lifer n’a pas encore rédigé sa présentation." }}</p>
                            <button v-if="isOwner" type="button" class="lifer-profile-text-button" @click="openEditor">Écrire ma présentation</button>
                        </div>
                    </section>

                    <section class="lifer-profile-card" aria-labelledby="comments-title">
                        <div class="lifer-profile-section-heading">
                            <div><span class="lifer-profile-kicker">Mur</span><h2 id="comments-title">Commentaires</h2></div>
                            <span v-if="isOwner && pendingCount" class="lifer-profile-pending-count">{{ pendingCount }} à valider</span>
                        </div>

                        <form class="lifer-profile-comment-form" @submit.prevent="publishComment">
                            <label for="profile-comment">{{ isOwner ? "Écrire sur mon profil" : `Écrire à ${firstName}` }}</label>
                            <textarea id="profile-comment" v-model="commentForm.content" rows="3" maxlength="1000" placeholder="Ton commentaire…"></textarea>
                            <div class="lifer-profile-comment-form__footer">
                                <p>{{ isOwner ? "Ton commentaire devra aussi être validé avant publication." : "Le propriétaire le validera avant sa publication." }}</p>
                                <button type="submit" class="lifer-profile-button lifer-profile-button--primary" :disabled="commentForm.processing || !commentForm.content.trim()">{{ commentForm.processing ? "Envoi…" : "Envoyer" }}</button>
                            </div>
                            <p v-if="commentForm.errors.content" class="lifer-profile-error" role="alert">{{ commentForm.errors.content }}</p>
                        </form>

                        <div v-if="comments.length" class="lifer-profile-comment-list">
                            <article v-for="comment in comments" :key="comment.id" class="lifer-profile-comment" :class="{ 'lifer-profile-comment--pending': comment.status === 'pending' }">
                                <div class="lifer-profile-comment__avatar" aria-hidden="true">{{ initials(comment.author.name) }}</div>
                                <div class="lifer-profile-comment__body">
                                    <div class="lifer-profile-comment__heading">
                                        <div>
                                            <Link :href="route('lifers.profile.show', comment.author.id)">{{ comment.author.name }}</Link>
                                            <time :datetime="comment.created_at">{{ formatDate(comment.created_at) }}</time>
                                        </div>
                                        <span v-if="comment.status === 'pending'">En attente</span>
                                    </div>
                                    <p>{{ comment.content }}</p>
                                    <div v-if="comment.can_moderate || comment.can_delete" class="lifer-profile-comment__actions">
                                        <button v-if="comment.can_moderate" type="button" @click="approveComment(comment)">Valider</button>
                                        <button v-if="comment.can_delete" type="button" class="is-danger" @click="deleteComment(comment)">{{ comment.can_moderate ? "Refuser" : "Supprimer" }}</button>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <p v-else class="lifer-profile-empty lifer-profile-empty--comments">Aucun commentaire visible pour le moment.</p>
                    </section>
                </main>

                <aside class="lifer-profile-sidebar" aria-label="Informations complémentaires">
                    <section class="lifer-profile-card lifer-profile-diplomas">
                        <span class="lifer-profile-kicker">Parcours</span>
                        <h2>Diplômes</h2>
                        <ul v-if="publicDiplomas.length">
                            <li v-for="diploma in publicDiplomas" :key="diploma.id"><span aria-hidden="true">✓</span>{{ diploma.name }}</li>
                        </ul>
                        <p v-else>{{ isOwner && diplomas.length ? "Tu n’affiches encore aucun diplôme." : "Aucun diplôme affiché." }}</p>
                        <button v-if="isOwner && !editing" type="button" class="lifer-profile-text-button" @click="openEditor">Gérer la visibilité</button>
                    </section>

                    <section v-if="!isOwner" class="lifer-profile-card lifer-profile-contact">
                        <span class="lifer-profile-kicker">Communauté</span>
                        <h2>Discuter</h2>
                        <p>Retrouve ce Lifer dans la communauté pour démarrer une conversation privée.</p>
                        <Link :href="route('social')" class="lifer-profile-button lifer-profile-button--secondary">Ouvrir la communauté</Link>
                    </section>
                </aside>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.lifer-profile-page { display: grid; width: min(100%, 1420px); margin-inline: auto; padding: clamp(22px, 2.5vw, 36px) clamp(20px, 3vw, 42px) 52px; gap: 20px; }
.lifer-profile-card, .lifer-profile-hero { border: 1px solid rgb(70 50 78 / 7%); background: #f8f3ec; box-shadow: 0 1px 0 rgb(70 50 78 / 4%), 0 10px 28px rgb(70 50 78 / 8%); }
.lifer-profile-hero { display: grid; min-height: 330px; border-radius: 22px; grid-template-columns: minmax(240px, 0.42fr) minmax(300px, 0.58fr); overflow: hidden; background: radial-gradient(circle at 12% 16%, rgb(214 168 74 / 18%), transparent 38%), linear-gradient(145deg, rgb(111 146 123 / 14%), transparent 66%), #f8f3ec; }
.lifer-profile-hero__portrait { display: flex; min-height: 330px; padding: 20px 30px 0; align-items: flex-end; justify-content: center; }
.lifer-profile-hero__portrait img { display: block; width: 100%; height: 310px; object-fit: contain; object-position: center bottom; }
.lifer-profile-hero__portrait span { margin: auto; color: rgb(70 50 78 / 55%); font-size: 14px; }
.lifer-profile-hero__identity { display: flex; min-width: 0; padding: clamp(34px, 4vw, 62px); flex-direction: column; align-items: flex-start; justify-content: center; }
.lifer-profile-kicker { color: #6f927b; font-size: 11px; font-weight: 700; line-height: 1.2; letter-spacing: 0.11em; text-transform: uppercase; }
.lifer-profile-hero h1, .lifer-profile-section-heading h2, .lifer-profile-sidebar h2 { color: #46324e; font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif; font-weight: 700; letter-spacing: -0.035em; }
.lifer-profile-hero h1 { max-width: 800px; margin: 9px 0 0; font-size: clamp(42px, 5vw, 72px); line-height: 0.94; overflow-wrap: anywhere; }
.lifer-profile-hero__job { margin: 16px 0 0; color: #6f5d74; font-size: 18px; font-weight: 600; }
.lifer-profile-hero__job--muted { color: rgb(70 50 78 / 54%); }
.lifer-profile-hero__facts { display: flex; margin-top: 18px; flex-wrap: wrap; gap: 8px; }
.lifer-profile-hero__facts > span { display: inline-flex; min-height: 34px; padding: 7px 12px; border-radius: 10px; align-items: center; gap: 7px; color: #46324e; background: rgb(244 238 229 / 88%); font-size: 13px; font-weight: 700; }
.lifer-profile-hero__facts small { padding: 2px 6px; border-radius: 999px; color: #6f5d74; background: rgb(70 50 78 / 8%); font-size: 10px; text-transform: uppercase; }
.lifer-profile-relationship a { color: #5d7e68; font-weight: 800; text-decoration-color: rgb(111 146 123 / 48%); text-decoration-thickness: 2px; text-underline-offset: 3px; }
.lifer-profile-hero .lifer-profile-button { margin-top: 24px; }
.lifer-profile-grid { display: grid; grid-template-columns: minmax(0, 1fr) minmax(250px, 340px); align-items: start; gap: 20px; }
.lifer-profile-main, .lifer-profile-sidebar { display: grid; gap: 20px; }
.lifer-profile-card { padding: clamp(24px, 3vw, 38px); border-radius: 20px; }
.lifer-profile-section-heading { display: flex; margin-bottom: 24px; align-items: flex-start; justify-content: space-between; gap: 18px; }
.lifer-profile-section-heading h2, .lifer-profile-sidebar h2 { margin: 6px 0 0; font-size: clamp(28px, 3vw, 38px); line-height: 1; }
.lifer-profile-text-button, .lifer-profile-comment__actions button { min-height: 40px; padding: 6px 2px; border: 0; border-bottom: 3px solid #d6a84a; color: #46324e; background: transparent; font: inherit; font-size: 14px; font-weight: 700; cursor: pointer; }
.lifer-profile-text-button:hover, .lifer-profile-comment__actions button:hover { color: #765926; }
.lifer-profile-button { display: inline-flex; min-height: 46px; padding: 11px 18px; border: 1px solid transparent; border-radius: 11px; align-items: center; justify-content: center; font: inherit; font-size: 14px; font-weight: 700; text-decoration: none; cursor: pointer; transition: transform 160ms ease, box-shadow 160ms ease, background-color 160ms ease; }
.lifer-profile-button:hover:not(:disabled) { transform: translateY(-1px); }
.lifer-profile-button--primary { color: #46324e; background: #d6a84a; box-shadow: 0 8px 18px rgb(133 94 29 / 14%); }
.lifer-profile-button--primary:hover:not(:disabled) { background: #dfb45b; }
.lifer-profile-button--secondary { border-color: rgb(70 50 78 / 18%); color: #46324e; background: #fcf8f2; }
.lifer-profile-button:focus-visible, .lifer-profile-text-button:focus-visible, .lifer-profile-comment__actions button:focus-visible, .lifer-profile-relationship a:focus-visible, .lifer-profile-relationship-field select:focus-visible { outline: 3px solid rgb(214 168 74 / 35%); outline-offset: 3px; }
.lifer-profile-button:disabled { opacity: 0.6; cursor: wait; }
.lifer-profile-empty { padding: 24px; border: 1px dashed rgb(70 50 78 / 18%); border-radius: 14px; color: #78697e; background: rgb(252 248 242 / 70%); line-height: 1.65; }
.lifer-profile-empty p { margin: 0; }
.lifer-profile-empty .lifer-profile-text-button { margin-top: 12px; }
.lifer-profile-editor-form { display: grid; gap: 18px; }
.lifer-profile-visibility { display: grid; margin: 0; padding: 18px; border: 1px solid rgb(70 50 78 / 12%); border-radius: 14px; gap: 16px; background: #fcf8f2; }
.lifer-profile-visibility legend { padding-inline: 5px; color: #46324e; font-size: 14px; font-weight: 700; }
.lifer-profile-relationship-field { display: grid; padding-bottom: 15px; border-bottom: 1px solid rgb(70 50 78 / 10%); gap: 7px; color: #46324e; font-size: 14px; font-weight: 700; }
.lifer-profile-relationship-field select { width: 100%; min-height: 44px; padding: 0 40px 0 12px; border: 1px solid rgb(70 50 78 / 20%); border-radius: 10px; color: #46324e; background: #fffaf4; font: inherit; }
.lifer-profile-relationship-field small { color: #817286; font-size: 12px; font-weight: 400; }
.lifer-profile-toggle { display: flex; width: 100%; padding: 10px; border: 1px solid transparent; border-radius: 10px; align-items: flex-start; gap: 11px; color: #46324e; background: transparent; font: inherit; text-align: left; cursor: pointer; }
.lifer-profile-toggle:hover, .lifer-profile-toggle.is-selected { border-color: rgb(111 146 123 / 24%); background: rgb(111 146 123 / 8%); }
.lifer-profile-toggle > span:last-child { display: grid; gap: 2px; }
.lifer-profile-choice-box { display: inline-grid; width: 21px; height: 21px; flex: 0 0 21px; margin-top: 1px; border: 1px solid rgb(70 50 78 / 35%); border-radius: 6px; place-items: center; color: #f8f3ec; background: #fffaf4; font-size: 13px; font-weight: 800; line-height: 1; }
.is-selected > .lifer-profile-choice-box { border-color: #6f927b; background: #6f927b; }
.lifer-profile-toggle strong, .lifer-profile-diploma-options > span { color: #46324e; font-size: 14px; }
.lifer-profile-toggle small { color: #817286; font-size: 12px; }
.lifer-profile-diploma-options { display: grid; padding-top: 15px; border-top: 1px solid rgb(70 50 78 / 10%); gap: 9px; }
.lifer-profile-diploma-option { display: flex; width: 100%; min-height: 42px; padding: 9px 10px; border: 1px solid transparent; border-radius: 10px; align-items: center; gap: 9px; color: #5b4962; background: transparent; font: inherit; font-size: 14px; text-align: left; cursor: pointer; }
.lifer-profile-diploma-option:hover, .lifer-profile-diploma-option.is-selected { border-color: rgb(111 146 123 / 24%); background: rgb(111 146 123 / 8%); }
.lifer-profile-toggle:focus-visible, .lifer-profile-diploma-option:focus-visible { outline: 3px solid rgb(214 168 74 / 35%); outline-offset: 2px; }
.lifer-profile-form-actions { display: flex; justify-content: flex-end; gap: 10px; }
.lifer-profile-error { margin: 0; color: #8e344b; font-size: 13px; font-weight: 600; }
.lifer-profile-pending-count { padding: 7px 10px; border-radius: 999px; color: #765926; background: rgb(214 168 74 / 18%); font-size: 12px; font-weight: 700; }
.lifer-profile-comment-form { display: grid; padding: 18px; border-radius: 14px; gap: 9px; background: #f4eee5; }
.lifer-profile-comment-form label { color: #46324e; font-size: 14px; font-weight: 700; }
.lifer-profile-comment-form textarea { width: 100%; resize: vertical; padding: 13px 14px; border: 1px solid rgb(70 50 78 / 22%); border-radius: 11px; color: #46324e; background: #fcf8f2; font: inherit; line-height: 1.5; }
.lifer-profile-comment-form textarea:focus { border-color: #46324e; outline: 3px solid rgb(70 50 78 / 17%); outline-offset: 2px; box-shadow: none; }
.lifer-profile-comment-form__footer { display: flex; align-items: center; justify-content: space-between; gap: 15px; }
.lifer-profile-comment-form__footer p { margin: 0; color: #817286; font-size: 12px; line-height: 1.45; }
.lifer-profile-comment-list { display: grid; margin-top: 24px; gap: 12px; }
.lifer-profile-comment { display: grid; padding: 17px; border: 1px solid rgb(70 50 78 / 9%); border-radius: 14px; grid-template-columns: 42px minmax(0, 1fr); gap: 13px; background: #fcf8f2; }
.lifer-profile-comment--pending { border-color: rgb(214 168 74 / 30%); background: rgb(214 168 74 / 7%); }
.lifer-profile-comment__avatar { display: flex; width: 42px; height: 42px; border-radius: 50%; align-items: center; justify-content: center; color: #f8f3ec; background: #6f927b; font-size: 12px; font-weight: 800; text-transform: uppercase; }
.lifer-profile-comment__heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.lifer-profile-comment__heading > div { display: grid; gap: 2px; }
.lifer-profile-comment__heading a { color: #46324e; font-size: 14px; font-weight: 750; text-decoration: none; }
.lifer-profile-comment__heading a:hover { text-decoration: underline; }
.lifer-profile-comment__heading time { color: #8a7b8f; font-size: 11px; }
.lifer-profile-comment__heading > span { padding: 4px 8px; border-radius: 999px; color: #765926; background: rgb(214 168 74 / 18%); font-size: 10px; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; }
.lifer-profile-comment__body > p { margin: 11px 0 0; color: #5b4962; font-size: 14px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
.lifer-profile-comment__actions { display: flex; margin-top: 10px; gap: 14px; }
.lifer-profile-comment__actions button { min-height: 32px; padding-block: 3px; font-size: 12px; }
.lifer-profile-comment__actions button.is-danger { border-color: rgb(142 52 75 / 55%); color: #8e344b; }
.lifer-profile-empty--comments { margin-top: 22px; text-align: center; }
.lifer-profile-sidebar h2 { font-size: 30px; }
.lifer-profile-diplomas ul { display: grid; margin: 22px 0 0; padding: 0; gap: 10px; list-style: none; }
.lifer-profile-diplomas li { display: flex; padding: 11px 12px; border-radius: 10px; align-items: flex-start; gap: 9px; color: #55435c; background: #fcf8f2; font-size: 14px; line-height: 1.45; }
.lifer-profile-diplomas li span { color: #6f927b; font-weight: 800; }
.lifer-profile-diplomas > p, .lifer-profile-contact p { margin: 18px 0 0; color: #78697e; font-size: 14px; line-height: 1.6; }
.lifer-profile-diplomas .lifer-profile-text-button, .lifer-profile-contact .lifer-profile-button { margin-top: 17px; }

@media (max-width: 980px) {
    .lifer-profile-grid { grid-template-columns: 1fr; }
    .lifer-profile-sidebar { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}

@media (max-width: 720px) {
    .lifer-profile-page { padding-inline: 14px; }
    .lifer-profile-hero { grid-template-columns: 1fr; }
    .lifer-profile-hero__portrait { min-height: 280px; padding-top: 14px; }
    .lifer-profile-hero__portrait img { height: 270px; }
    .lifer-profile-hero__identity { padding: 28px 22px 32px; }
    .lifer-profile-card { padding: 22px 18px; }
    .lifer-profile-sidebar { grid-template-columns: 1fr; }
    .lifer-profile-comment-form__footer, .lifer-profile-form-actions { align-items: stretch; flex-direction: column; }
    .lifer-profile-comment-form__footer .lifer-profile-button { width: 100%; }
    .lifer-profile-comment { grid-template-columns: 36px minmax(0, 1fr); }
    .lifer-profile-comment__avatar { width: 36px; height: 36px; }
}

@media (prefers-reduced-motion: reduce) {
    .lifer-profile-button { transition: none; }
}
</style>
