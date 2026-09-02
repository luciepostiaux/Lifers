<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref } from "vue";
import { router } from "@inertiajs/vue3";
import axios from "axios";
import AppLayout from "@/Layouts/AppLayout.vue";
import ProfileRichContent from "@/Components/Profile/ProfileRichContent.vue";
import ProfileRichEditor from "@/Components/Profile/ProfileRichEditor.vue";
import { emptyProfileDocument } from "@/Components/Profile/profileEditorExtensions";

const props = defineProps({
    lifer: { type: Object, required: true },
    money: [String, Number],
    filters: { type: Object, default: () => ({}) },
    profiles: { type: Array, default: () => [] },
    comments: { type: Array, default: () => [] },
    communityMessages: { type: Array, default: () => [] },
    staffConversation: { type: Object, required: true },
    recentActions: { type: Array, default: () => [] },
});

const activeTab = ref("chat");
const search = ref(props.filters.q ?? "");
const staffMessages = ref([...props.staffConversation.messages]);
const onlineMembers = ref([]);
const newMessage = ref("");
const chatError = ref("");
const sending = ref(false);
const messagesElement = ref(null);
const editedProfile = ref(null);
const editedContent = ref(null);
const editReason = ref("");
const editError = ref("");
const savingProfile = ref(false);
let joinedConversationId = null;

const tabs = computed(() => [
    { id: "chat", label: "Salon de l’équipe", count: null },
    { id: "profiles", label: "Profils", count: props.profiles.length },
    { id: "comments", label: "Commentaires", count: props.comments.length },
    { id: "messages", label: "Salon général", count: props.communityMessages.length },
    { id: "history", label: "Historique", count: props.recentActions.length },
]);

function staffLabel(role) {
    if (role === "admin") return "Administratrice";
    return "Modérateur";
}

function initials(name) {
    return String(name ?? "LI").split(/\s+/).filter(Boolean).slice(0, 2)
        .map((part) => part[0]?.toUpperCase()).join("");
}

function fullName(sender) {
    return [sender?.first_name, sender?.last_name].filter(Boolean).join(" ") || "Lifer de l’équipe";
}

function isOwnMessage(message) {
    return Number(message.sender_lifer_id) === Number(props.lifer.id);
}

function formatDate(value) {
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return new Intl.DateTimeFormat("fr-FR", {
        day: "numeric",
        month: "short",
        hour: "2-digit",
        minute: "2-digit",
    }).format(date);
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesElement.value) messagesElement.value.scrollTop = messagesElement.value.scrollHeight;
    });
}

function joinStaffChat() {
    const conversationId = props.staffConversation.id;
    if (!window.Echo || !conversationId) return;
    joinedConversationId = conversationId;
    window.Echo.join(`conversation.${conversationId}`)
        .here((members) => { onlineMembers.value = members; })
        .joining((member) => {
            if (!onlineMembers.value.some(({ id }) => Number(id) === Number(member.id))) {
                onlineMembers.value.push(member);
            }
        })
        .leaving((member) => {
            onlineMembers.value = onlineMembers.value.filter(({ id }) => Number(id) !== Number(member.id));
        })
        .listen("MessageSent", ({ message }) => {
            if (!staffMessages.value.some(({ id }) => Number(id) === Number(message.id))) {
                staffMessages.value.push(message);
                scrollToBottom();
            }
        })
        .listen("MessageDeleted", ({ message_id: messageId }) => {
            staffMessages.value = staffMessages.value.filter(({ id }) => Number(id) !== Number(messageId));
        });
}

async function sendMessage() {
    const content = newMessage.value.trim();
    if (!content || sending.value) return;
    sending.value = true;
    chatError.value = "";
    try {
        const { data } = await axios.post(
            `/conversations/${props.staffConversation.id}/messages`,
            { content },
            { headers: { Accept: "application/json" } },
        );
        newMessage.value = "";
        if (!staffMessages.value.some(({ id }) => Number(id) === Number(data.id))) {
            staffMessages.value.push(data);
        }
        scrollToBottom();
    } catch (error) {
        chatError.value = error.response?.data?.message || "Le message n’a pas pu être envoyé.";
    } finally {
        sending.value = false;
    }
}

function runSearch() {
    router.get(route("moderation.dashboard"), { q: search.value }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function askReason(label) {
    const reason = window.prompt(`${label}\n\nIndique la raison de cette intervention :`);
    if (reason === null) return null;
    const cleanReason = reason.trim();
    if (cleanReason.length < 3) {
        window.alert("La raison doit contenir au moins 3 caractères.");
        return null;
    }
    return cleanReason;
}

function deleteComment(comment) {
    const reason = askReason(`Supprimer le commentaire de ${comment.author.name} ?`);
    if (!reason) return;
    router.delete(route("moderation.comments.destroy", comment.id), {
        data: { reason }, preserveScroll: true,
    });
}

function deleteMessage(message) {
    const reason = askReason(`Supprimer le message de ${fullName(message.sender)} ?`);
    if (!reason) return;
    router.delete(route("moderation.messages.destroy", message.id), {
        data: { reason }, preserveScroll: true,
    });
}

function deleteProfileImage(profile, image) {
    const reason = askReason(`Retirer cette image du profil de ${profile.name} ?`);
    if (!reason) return;
    router.delete(route("moderation.profile-images.destroy", image.id), {
        data: { reason }, preserveScroll: true,
    });
}

function openProfileEditor(profile) {
    editedProfile.value = profile;
    editedContent.value = JSON.parse(JSON.stringify(profile.content ?? emptyProfileDocument));
    editReason.value = "";
    editError.value = "";
}

function closeProfileEditor() {
    if (savingProfile.value) return;
    resetProfileEditor();
}

function resetProfileEditor() {
    editedProfile.value = null;
    editedContent.value = null;
    editReason.value = "";
    editError.value = "";
}

function saveProfile() {
    if (!editedProfile.value || savingProfile.value) return;
    if (editReason.value.trim().length < 3) {
        editError.value = "Indique une raison d’au moins 3 caractères.";
        return;
    }
    savingProfile.value = true;
    editError.value = "";
    router.patch(route("moderation.profiles.update", editedProfile.value.id), {
        content: editedContent.value,
        reason: editReason.value,
    }, {
        preserveScroll: true,
        onSuccess: resetProfileEditor,
        onError: (errors) => { editError.value = errors.content || errors.reason || "La présentation n’a pas pu être enregistrée."; },
        onFinish: () => { savingProfile.value = false; },
    });
}

onMounted(() => {
    joinStaffChat();
    scrollToBottom();
});

onBeforeUnmount(() => {
    if (window.Echo && joinedConversationId) window.Echo.leave(`conversation.${joinedConversationId}`);
});
</script>

<template>
    <AppLayout title="Modération" :money="money">
        <main class="moderation-page">
            <header class="moderation-hero">
                <div>
                    <span class="moderation-kicker">Équipe Lifers</span>
                    <h1>Espace de modération</h1>
                    <p>Surveille les contenus publics et échange dans le salon privé de l’équipe.</p>
                </div>
                <span class="moderation-role">{{ staffLabel(lifer.staff_role) }}</span>
            </header>

            <nav class="moderation-tabs" aria-label="Sections de modération">
                <button v-for="tab in tabs" :key="tab.id" type="button" :class="{ 'is-active': activeTab === tab.id }" @click="activeTab = tab.id">
                    {{ tab.label }} <span v-if="tab.count !== null">{{ tab.count }}</span>
                </button>
            </nav>

            <section v-if="activeTab === 'chat'" class="moderation-chat" aria-labelledby="staff-chat-title">
                <aside class="moderation-chat__members">
                    <span class="moderation-kicker">Accès réservé</span>
                    <h2 id="staff-chat-title">{{ staffConversation.name }}</h2>
                    <p>Ce salon n’est visible que par l’administration et la modération.</p>
                    <ul>
                        <li v-for="member in staffConversation.members" :key="member.id">
                            <span>{{ initials(member.name) }}</span>
                            <div><strong>{{ member.name }}</strong><small>{{ staffLabel(member.staff_role) }}</small></div>
                            <i :class="{ 'is-online': onlineMembers.some(({ id }) => Number(id) === Number(member.id)) }" :aria-label="onlineMembers.some(({ id }) => Number(id) === Number(member.id)) ? 'En ligne' : 'Hors ligne'"></i>
                        </li>
                    </ul>
                </aside>

                <div class="moderation-chat__main">
                    <div ref="messagesElement" class="moderation-chat__messages" aria-live="polite">
                        <p v-if="staffMessages.length === 0" class="moderation-empty">Le salon de l’équipe est prêt. Écris le premier message.</p>
                        <article v-for="message in staffMessages" :key="message.id" :class="['moderation-chat__message', { 'is-own': isOwnMessage(message) }]">
                            <span>{{ initials(fullName(message.sender)) }}</span>
                            <div>
                                <header><strong>{{ isOwnMessage(message) ? 'Toi' : fullName(message.sender) }}</strong><small>{{ staffLabel(message.sender?.staff_role) }}</small><time :datetime="message.created_at">{{ formatDate(message.created_at) }}</time></header>
                                <p>{{ message.content }}</p>
                            </div>
                        </article>
                    </div>
                    <form class="moderation-chat__composer" @submit.prevent="sendMessage">
                        <label for="staff-message" class="sr-only">Message à l’équipe</label>
                        <textarea id="staff-message" v-model="newMessage" rows="2" maxlength="2000" placeholder="Écrire à l’équipe…" @keydown.enter.exact.prevent="sendMessage"></textarea>
                        <button type="submit" :disabled="sending || !newMessage.trim()">{{ sending ? "Envoi…" : "Envoyer" }}</button>
                        <p v-if="chatError" role="alert">{{ chatError }}</p>
                    </form>
                </div>
            </section>

            <template v-else>
                <form v-if="activeTab !== 'history'" class="moderation-search" @submit.prevent="runSearch">
                    <label for="moderation-search">Rechercher un Lifer ou un contenu</label>
                    <div><input id="moderation-search" v-model="search" type="search" maxlength="100" placeholder="Nom ou texte…" /><button type="submit">Rechercher</button></div>
                </form>

                <section v-if="activeTab === 'profiles'" class="moderation-grid" aria-label="Profils à modérer">
                    <article v-for="profile in profiles" :key="profile.id" class="moderation-card">
                        <header><div><span class="moderation-kicker">Profil public</span><h2 :class="{ 'is-staff': profile.staff_role }">{{ profile.name }}</h2></div><button type="button" @click="openProfileEditor(profile)">Modifier</button></header>
                        <ProfileRichContent v-if="profile.content" :content="profile.content" />
                        <p v-else class="moderation-empty">Aucune présentation rédigée.</p>
                        <div v-if="profile.images.length" class="moderation-images">
                            <figure v-for="image in profile.images" :key="image.id"><img :src="image.url" alt="Image publiée dans la présentation" /><button type="button" @click="deleteProfileImage(profile, image)">Retirer</button></figure>
                        </div>
                    </article>
                    <p v-if="profiles.length === 0" class="moderation-empty moderation-empty--wide">Aucun profil correspondant.</p>
                </section>

                <section v-if="activeTab === 'comments'" class="moderation-list" aria-label="Commentaires à modérer">
                    <article v-for="comment in comments" :key="comment.id" class="moderation-list-item">
                        <div><span class="moderation-kicker">Sur le profil de {{ comment.receiver.name }}</span><h2 :class="{ 'is-staff': comment.author.staff_role }">{{ comment.author.name }}</h2><time :datetime="comment.created_at">{{ formatDate(comment.created_at) }} · {{ comment.status === 'pending' ? 'En attente' : 'Publié' }}</time></div>
                        <p>{{ comment.content }}</p>
                        <button type="button" @click="deleteComment(comment)">Supprimer</button>
                    </article>
                    <p v-if="comments.length === 0" class="moderation-empty">Aucun commentaire correspondant.</p>
                </section>

                <section v-if="activeTab === 'messages'" class="moderation-list" aria-label="Messages publics du salon général à modérer">
                    <article v-for="message in communityMessages" :key="message.id" class="moderation-list-item">
                        <div><span class="moderation-kicker">{{ message.conversation_name }}</span><h2 :class="{ 'is-staff': message.sender?.staff_role }">{{ fullName(message.sender) }}</h2><time :datetime="message.created_at">{{ formatDate(message.created_at) }}</time></div>
                        <p>{{ message.content }}</p>
                        <button type="button" @click="deleteMessage(message)">Supprimer</button>
                    </article>
                    <p v-if="communityMessages.length === 0" class="moderation-empty">Aucun message public correspondant dans le salon général.</p>
                </section>

                <section v-if="activeTab === 'history'" class="moderation-list" aria-label="Historique des interventions">
                    <article v-for="action in recentActions" :key="action.id" class="moderation-list-item moderation-list-item--history">
                        <div class="moderation-history__identity">
                            <span class="moderation-kicker">{{ action.label }}</span>
                            <h2>{{ action.target || "Contenu public" }}</h2>
                            <p>Par {{ action.actor }}</p>
                            <time :datetime="action.created_at">{{ formatDate(action.created_at) }}</time>
                        </div>
                        <div class="moderation-history__details">
                            <p class="moderation-history__reason"><strong>Motif rédigé</strong>{{ action.reason || "Raison non disponible" }}</p>
                            <p v-if="action.source"><strong>Auteur du contenu</strong>{{ action.source }}</p>
                            <p v-if="action.removed_text"><strong>Contenu retiré ou supprimé</strong><q>{{ action.removed_text }}</q></p>
                            <template v-if="action.action === 'moderation.profile.updated'">
                                <p><strong>Texte avant l’intervention</strong><q>{{ action.before_text || "Présentation vide" }}</q></p>
                                <p><strong>Texte après l’intervention</strong><q>{{ action.after_text || "Présentation vidée" }}</q></p>
                                <p v-if="action.removed_images?.length">
                                    <strong>Images supprimées</strong>
                                    {{ action.removed_images.map((image) => image.path || `image n°${image.id}`).join(", ") }}
                                </p>
                            </template>
                        </div>
                    </article>
                    <p v-if="recentActions.length === 0" class="moderation-empty">Aucune intervention enregistrée.</p>
                </section>
            </template>
        </main>

        <div v-if="editedProfile" class="moderation-modal-backdrop" @click.self="closeProfileEditor">
            <section class="moderation-modal" role="dialog" aria-modal="true" aria-labelledby="moderation-editor-title">
                <header><div><span class="moderation-kicker">Modération du profil</span><h2 id="moderation-editor-title">{{ editedProfile.name }}</h2></div><button type="button" aria-label="Fermer" @click="closeProfileEditor">×</button></header>
                <ProfileRichEditor v-model="editedContent" :allow-images="false" />
                <label>Raison de l’intervention<textarea v-model="editReason" rows="3" maxlength="1000" placeholder="Contenu abusif, information personnelle…"></textarea></label>
                <p v-if="editError" class="moderation-error" role="alert">{{ editError }}</p>
                <footer><button type="button" @click="closeProfileEditor">Annuler</button><button type="button" class="is-primary" :disabled="savingProfile" @click="saveProfile">{{ savingProfile ? "Enregistrement…" : "Enregistrer la modération" }}</button></footer>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.moderation-page{display:grid;width:min(100%,1480px);margin-inline:auto;padding:clamp(20px,3vw,40px);gap:18px;color:#46324e}.moderation-hero,.moderation-chat,.moderation-card,.moderation-search,.moderation-list-item{border:1px solid rgb(70 50 78/8%);background:#f8f3ec;box-shadow:0 10px 28px rgb(70 50 78/7%)}.moderation-hero{display:flex;padding:clamp(26px,4vw,52px);border-radius:22px;align-items:flex-start;justify-content:space-between;gap:20px}.moderation-kicker{color:#6f927b;font-size:10px;font-weight:800;letter-spacing:.1em;text-transform:uppercase}.moderation-hero h1{margin:7px 0 0;font-family:"Bricolage Grotesque",sans-serif;font-size:clamp(36px,5vw,66px);line-height:.95;letter-spacing:-.04em}.moderation-hero p{max-width:680px;margin:14px 0 0;color:#796a7e;line-height:1.6}.moderation-role,.moderation-chat__message header small{padding:6px 10px;border-radius:999px;color:#8e344b;background:rgb(142 52 75/9%);font-size:9px;font-weight:800;letter-spacing:.05em;text-transform:uppercase}.moderation-tabs{display:flex;padding:6px;overflow-x:auto;border-radius:15px;gap:5px;background:rgb(70 50 78/6%)}.moderation-tabs button{min-height:43px;padding:8px 14px;border:0;border-radius:11px;color:#6c5b72;background:transparent;font:inherit;font-size:12px;font-weight:800;white-space:nowrap}.moderation-tabs button.is-active{color:#fffaf4;background:#46324e}.moderation-tabs span{display:inline-grid;min-width:20px;height:20px;margin-left:5px;border-radius:999px;place-items:center;background:rgb(255 255 255/16%);font-size:9px}.moderation-chat{display:grid;min-height:650px;border-radius:20px;grid-template-columns:minmax(230px,.38fr) minmax(0,1fr);overflow:hidden}.moderation-chat__members{padding:24px;border-right:1px solid rgb(70 50 78/9%);background:#f4eee5}.moderation-chat__members h2,.moderation-card h2,.moderation-list-item h2,.moderation-modal h2{margin:5px 0 0;font-family:"Bricolage Grotesque",sans-serif;font-size:27px;letter-spacing:-.03em}.moderation-chat__members>p{color:#7c6d81;font-size:12px;line-height:1.55}.moderation-chat__members ul{display:grid;margin:22px 0 0;padding:0;gap:7px;list-style:none}.moderation-chat__members li{display:grid;min-height:56px;padding:8px;border-radius:12px;grid-template-columns:38px minmax(0,1fr) 10px;align-items:center;gap:9px;background:rgb(252 248 242/70%)}.moderation-chat__members li>span,.moderation-chat__message>span{display:grid;width:38px;height:38px;border-radius:50%;place-items:center;color:#fffaf4;background:#8e344b;font-size:10px;font-weight:800}.moderation-chat__members li div{display:grid;min-width:0}.moderation-chat__members strong{overflow:hidden;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.moderation-chat__members small{color:#8e344b;font-size:8px;text-transform:uppercase}.moderation-chat__members i{width:9px;height:9px;border-radius:50%;background:#b9aebc}.moderation-chat__members i.is-online{background:#6f927b}.moderation-chat__main{display:grid;min-width:0;grid-template-rows:minmax(0,1fr) auto}.moderation-chat__messages{display:flex;max-height:680px;padding:24px;overflow-y:auto;flex-direction:column;gap:13px;background:#fcf8f2}.moderation-chat__message{display:flex;max-width:78%;align-items:flex-end;gap:9px}.moderation-chat__message.is-own{align-self:flex-end;flex-direction:row-reverse}.moderation-chat__message>div{padding:10px 13px;border-radius:14px 14px 14px 4px;background:#f4eee5}.moderation-chat__message.is-own>div{border-radius:14px 14px 4px;background:rgb(214 168 74/22%)}.moderation-chat__message header{display:flex;align-items:center;flex-wrap:wrap;gap:7px}.moderation-chat__message header strong{color:#8e344b;font-size:10px}.moderation-chat__message header small{padding:2px 5px;font-size:7px}.moderation-chat__message time{color:#8a7b8f;font-size:8px}.moderation-chat__message p{margin:6px 0 0;overflow-wrap:anywhere;font-size:13px;line-height:1.5;white-space:pre-wrap}.moderation-chat__message button{margin-top:7px;padding:0;border:0;color:#8e344b;background:transparent;font:inherit;font-size:9px;font-weight:800}.moderation-chat__composer{display:grid;padding:14px;border-top:1px solid rgb(70 50 78/9%);grid-template-columns:minmax(0,1fr) auto;gap:9px}.moderation-chat__composer textarea,.moderation-modal textarea,.moderation-search input{padding:12px 14px;border:1px solid rgb(70 50 78/18%);border-radius:11px;color:#46324e;background:#fffaf4;font:inherit}.moderation-chat__composer button,.moderation-search button,.moderation-modal .is-primary{min-height:46px;padding:10px 17px;border:0;border-radius:11px;color:#46324e;background:#d6a84a;font:inherit;font-size:12px;font-weight:800}.moderation-chat__composer>p{margin:0;grid-column:1/-1;color:#8e344b;font-size:11px}.moderation-search{display:grid;padding:20px;border-radius:16px;gap:8px}.moderation-search label{font-size:12px;font-weight:800}.moderation-search>div{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:8px}.moderation-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.moderation-card{padding:24px;border-radius:18px}.moderation-card>header{display:flex;margin-bottom:20px;align-items:flex-start;justify-content:space-between;gap:12px}.moderation-card h2.is-staff,.moderation-list-item h2.is-staff{color:#8e344b}.moderation-card>header button,.moderation-list-item>button,.moderation-images button{min-height:38px;padding:7px 11px;border:1px solid rgb(142 52 75/20%);border-radius:9px;color:#8e344b;background:rgb(142 52 75/7%);font:inherit;font-size:10px;font-weight:800}.moderation-images{display:grid;margin-top:18px;grid-template-columns:repeat(3,minmax(0,1fr));gap:8px}.moderation-images figure{position:relative;margin:0;overflow:hidden;border-radius:10px;aspect-ratio:1;background:#f4eee5}.moderation-images img{width:100%;height:100%;object-fit:cover}.moderation-images button{position:absolute;right:5px;bottom:5px;min-height:30px;background:#fffaf4}.moderation-list{display:grid;gap:10px}.moderation-list-item{display:grid;padding:20px;border-radius:15px;grid-template-columns:minmax(180px,.36fr) minmax(0,1fr) auto;align-items:center;gap:20px}.moderation-list-item h2{font-size:20px}.moderation-list-item time{color:#8a7b8f;font-size:10px}.moderation-list-item>p{margin:0;overflow-wrap:anywhere;color:#65556b;font-size:13px;line-height:1.55;white-space:pre-wrap}.moderation-list-item--history{grid-template-columns:minmax(180px,.36fr) minmax(0,1fr)}.moderation-empty{margin:auto;color:#817286;text-align:center}.moderation-empty--wide{grid-column:1/-1}.moderation-modal-backdrop{position:fixed;z-index:90;inset:0;display:grid;padding:20px;overflow-y:auto;place-items:center;background:rgb(42 29 46/52%);backdrop-filter:blur(4px)}.moderation-modal{display:grid;width:min(100%,900px);max-height:calc(100vh - 40px);padding:24px;overflow-y:auto;border-radius:20px;gap:16px;background:#f8f3ec;box-shadow:0 25px 70px rgb(42 29 46/28%)}.moderation-modal>header{display:flex;align-items:flex-start;justify-content:space-between}.moderation-modal>header>button{width:40px;height:40px;border:0;border-radius:10px;color:#46324e;background:rgb(70 50 78/7%);font-size:22px}.moderation-modal>label{display:grid;gap:7px;font-size:12px;font-weight:800}.moderation-modal footer{display:flex;justify-content:flex-end;gap:9px}.moderation-modal footer button{min-height:45px;padding:9px 15px;border:1px solid rgb(70 50 78/13%);border-radius:10px;color:#46324e;background:transparent;font:inherit;font-size:12px;font-weight:800}.moderation-error{margin:0;color:#8e344b;font-size:12px}button:disabled{cursor:not-allowed;opacity:.55}button:not(:disabled){cursor:pointer}button:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid rgb(214 168 74/35%);outline-offset:2px}
@media(max-width:900px){.moderation-chat{grid-template-columns:1fr}.moderation-chat__members{border-right:0;border-bottom:1px solid rgb(70 50 78/9%)}.moderation-chat__members ul{grid-template-columns:repeat(2,minmax(0,1fr))}.moderation-grid{grid-template-columns:1fr}.moderation-list-item{grid-template-columns:1fr}.moderation-list-item>button{justify-self:start}}
@media(max-width:600px){.moderation-page{padding:14px}.moderation-hero{align-items:flex-start;flex-direction:column}.moderation-chat__members ul{grid-template-columns:1fr}.moderation-chat__messages{min-height:460px;padding:14px}.moderation-chat__message{max-width:92%}.moderation-chat__message>span{display:none}.moderation-chat__composer,.moderation-search>div{grid-template-columns:1fr}.moderation-images{grid-template-columns:repeat(2,minmax(0,1fr))}.moderation-modal-backdrop{padding:8px}.moderation-modal{max-height:calc(100vh - 16px);padding:17px}}
.moderation-list-item--history{grid-template-columns:minmax(220px,.34fr) minmax(0,1fr);align-items:start}.moderation-history__identity>p{margin:8px 0 1px;color:#65556b;font-size:11px;font-weight:700}.moderation-history__details{display:grid;gap:9px}.moderation-history__details>p{display:grid;margin:0;padding:11px 13px;border-radius:10px;gap:4px;color:#65556b;background:rgb(70 50 78/4%);font-size:12px;line-height:1.55;white-space:pre-wrap}.moderation-history__details strong{color:#46324e;font-size:9px;letter-spacing:.08em;text-transform:uppercase}.moderation-history__details q{quotes:"« " " »";overflow-wrap:anywhere}.moderation-history__reason{border-left:3px solid #d6a84a}@media(max-width:900px){.moderation-list-item--history{grid-template-columns:1fr}}
</style>
