<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from "vue";
import { Link, router } from "@inertiajs/vue3";
import axios from "axios";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    conversations: { type: Array, default: () => [] },
    messages: { type: Array, default: () => [] },
    currentConversationId: Number,
    allPerso: { type: Object, default: () => ({}) },
    currentLifer: { type: Object, required: true },
    money: [String, Number],
});

const conversations = ref([...props.conversations]);
const messages = ref([...props.messages]);
const onlineUsers = ref([]);
const messagesContainer = ref(null);
const newMessage = ref("");
const errorMessage = ref("");
const groupError = ref("");
const sending = ref(false);
const openingPrivateConversationFor = ref(null);
const showCreateGroup = ref(false);
const showAddMembers = ref(false);
const groupName = ref("");
const newGroupMemberIds = ref([]);
const addedMemberIds = ref([]);
const savingGroup = ref(false);
const leavingGroup = ref(false);
let joinedConversationId = null;

const selectedConversationId = computed(() => props.currentConversationId);
const selectedConversation = computed(() =>
    conversations.value.find(({ id }) => id === selectedConversationId.value),
);
const generalConversation = computed(() =>
    conversations.value.find(({ type }) => type === "general"),
);
const privateConversations = computed(() =>
    conversations.value.filter(({ type }) => type === "private"),
);
const groupConversations = computed(() =>
    conversations.value.filter(({ type }) => type === "group"),
);
const allLifers = computed(() => Object.values(props.allPerso ?? {}));
const otherLifers = computed(() => allLifers.value.filter(({ id }) => id !== props.currentLifer.id));
const conversationMembers = computed(() => selectedConversation.value?.lifers ?? []);
const conversationMemberIds = computed(
    () => new Set(conversationMembers.value.map(({ id }) => id)),
);
const availableGroupMembers = computed(() =>
    otherLifers.value.filter(({ id }) => !conversationMemberIds.value.has(id)),
);
const selectedConversationIsGroup = computed(() => selectedConversation.value?.type === "group");
const selectedConversationSubtitle = computed(() => {
    const conversation = selectedConversation.value;
    if (!conversation) return "Conversation indisponible";
    if (conversation.type === "general") return "Le salon de toute la communauté";
    if (conversation.type === "group") {
        const count = Number(conversation.lifers_count ?? conversation.lifers?.length ?? 0);
        return `${count} membre${count > 1 ? "s" : ""}`;
    }

    const other = conversation.lifers?.find(({ id }) => id !== props.currentLifer.id);
    return isLiferOnline(other?.id) ? "En ligne" : "Conversation privée";
});

function fullName(person) {
    const directName = [person?.first_name, person?.last_name].filter(Boolean).join(" ");
    return directName || person?.persoName || props.allPerso?.[person?.id]?.persoName || "Lifer inconnu";
}

function initials(personOrName) {
    const name = typeof personOrName === "string" ? personOrName : fullName(personOrName);
    return name.split(/\s+/).filter(Boolean).slice(0, 2)
        .map((part) => part[0]?.toUpperCase()).join("") || "LI";
}

function messageAuthor(message) {
    return fullName(message.sender ?? { id: message.sender_lifer_id });
}

function staffRole(person) {
    return person?.staff_role || props.allPerso?.[person?.id]?.staff_role || null;
}

function staffRoleLabel(role) {
    if (role === "admin") return "Administratrice";
    if (role === "moderator") return "Modérateur";
    return "";
}

function otherConversationLifer(conversation) {
    return conversation?.lifers?.find(({ id }) => id !== props.currentLifer.id) ?? null;
}

function isOwnMessage(message) {
    return Number(message.sender?.id ?? message.sender_lifer_id) === Number(props.currentLifer.id);
}

function isLiferOnline(liferId) {
    return onlineUsers.value.some(({ id }) => Number(id) === Number(liferId))
        || Boolean(props.allPerso?.[liferId]?.isOnline);
}

function formatMessageTime(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return new Intl.DateTimeFormat("fr-FR", { hour: "2-digit", minute: "2-digit" }).format(date);
}

function conversationPreview(conversation) {
    if (conversation.latest_message?.content) return conversation.latest_message.content;
    if (conversation.type === "general") return "Retrouve tous les Lifers";
    if (conversation.type === "group") {
        const count = Number(conversation.lifers_count ?? conversation.lifers?.length ?? 0);
        return `${count} membre${count > 1 ? "s" : ""}`;
    }
    return "Commencez à discuter";
}

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight;
    });
}

function leaveCurrentConversation() {
    if (window.Echo && joinedConversationId) window.Echo.leave(`conversation.${joinedConversationId}`);
    joinedConversationId = null;
    onlineUsers.value = [];
}

function joinConversation(conversationId) {
    leaveCurrentConversation();
    if (!window.Echo || !conversationId) return;
    joinedConversationId = conversationId;
    window.Echo.join(`conversation.${conversationId}`)
        .here((users) => { onlineUsers.value = users; })
        .joining((user) => {
            if (!onlineUsers.value.some(({ id }) => id === user.id)) onlineUsers.value.push(user);
        })
        .leaving((user) => {
            onlineUsers.value = onlineUsers.value.filter(({ id }) => id !== user.id);
        })
        .listen("MessageSent", ({ message }) => {
            if (!messages.value.some(({ id }) => id === message.id)) {
                messages.value.push(message);
                scrollToBottom();
            }

            if (!isOwnMessage(message) && selectedConversation.value?.type === "private") {
                axios.post(`/conversations/${conversationId}/read`).catch(() => {});
            }
        })
        .listen("MessageDeleted", ({ message_id: messageId }) => {
            messages.value = messages.value.filter(({ id }) => Number(id) !== Number(messageId));
        });
}

async function sendMessage() {
    const content = newMessage.value.trim();
    if (!content || !selectedConversationId.value || sending.value) return;
    sending.value = true;
    errorMessage.value = "";
    try {
        const { data } = await axios.post(
            `/conversations/${selectedConversationId.value}/messages`,
            { content },
            { headers: { Accept: "application/json" } },
        );
        newMessage.value = "";
        if (!messages.value.some(({ id }) => id === data.id)) messages.value.push(data);
        if (selectedConversation.value) selectedConversation.value.latest_message = data;
        scrollToBottom();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Le message n’a pas pu être envoyé.";
    } finally {
        sending.value = false;
    }
}

async function startPrivateConversation(lifer) {
    if (!lifer?.id || lifer.id === props.currentLifer.id || openingPrivateConversationFor.value) return;
    openingPrivateConversationFor.value = lifer.id;
    errorMessage.value = "";
    try {
        const { data } = await axios.post(
            "/conversations", { lifer_id: lifer.id }, { headers: { Accept: "application/json" } },
        );
        router.visit(route("social", { id: data.id }));
    } catch (error) {
        errorMessage.value = error.response?.data?.errors?.lifer_id?.[0]
            || error.response?.data?.message || "La conversation privée n’a pas pu être ouverte.";
    } finally {
        openingPrivateConversationFor.value = null;
    }
}

function openCreateGroup() {
    groupName.value = "";
    newGroupMemberIds.value = [];
    groupError.value = "";
    showCreateGroup.value = true;
}
function closeCreateGroup() { if (!savingGroup.value) showCreateGroup.value = false; }
function openAddMembers() {
    addedMemberIds.value = [];
    groupError.value = "";
    showAddMembers.value = true;
}
function closeAddMembers() { if (!savingGroup.value) showAddMembers.value = false; }

function validationMessage(error, fallback) {
    const errors = error.response?.data?.errors;
    return errors?.name?.[0] || errors?.member_ids?.[0] || errors?.["member_ids.0"]?.[0]
        || error.response?.data?.message || fallback;
}

async function createGroup() {
    if (savingGroup.value) return;
    savingGroup.value = true;
    groupError.value = "";
    try {
        const { data } = await axios.post(
            "/conversations/groups",
            { name: groupName.value, member_ids: newGroupMemberIds.value },
            { headers: { Accept: "application/json" } },
        );
        showCreateGroup.value = false;
        router.visit(route("social", { id: data.id }));
    } catch (error) {
        groupError.value = validationMessage(error, "Le groupe n’a pas pu être créé.");
    } finally {
        savingGroup.value = false;
    }
}

async function addMembers() {
    if (!selectedConversationIsGroup.value || savingGroup.value) return;
    savingGroup.value = true;
    groupError.value = "";
    try {
        await axios.post(
            `/conversations/${selectedConversationId.value}/members`,
            { member_ids: addedMemberIds.value },
            { headers: { Accept: "application/json" } },
        );
        showAddMembers.value = false;
        router.reload({ only: ["conversations"], preserveScroll: true });
    } catch (error) {
        groupError.value = validationMessage(error, "Les membres n’ont pas pu être ajoutés.");
    } finally {
        savingGroup.value = false;
    }
}

async function leaveGroup() {
    if (!selectedConversationIsGroup.value || leavingGroup.value) return;
    if (!window.confirm(`Quitter le groupe « ${selectedConversation.value.display_name} » ?`)) return;
    leavingGroup.value = true;
    errorMessage.value = "";
    try {
        const { data } = await axios.delete(
            `/conversations/${selectedConversationId.value}/members/me`,
            { headers: { Accept: "application/json" } },
        );
        router.visit(data.redirect_to);
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Le groupe n’a pas pu être quitté.";
    } finally {
        leavingGroup.value = false;
    }
}

function closeDialogsWithEscape(event) {
    if (event.key === "Escape") {
        closeCreateGroup();
        closeAddMembers();
    }
}

watch(() => props.conversations, (value) => { conversations.value = [...value]; });
watch(() => props.messages, (value) => { messages.value = [...value]; scrollToBottom(); });
watch(() => props.currentConversationId, joinConversation);
onMounted(() => {
    joinConversation(props.currentConversationId);
    scrollToBottom();
    window.addEventListener("keydown", closeDialogsWithEscape);
});
onBeforeUnmount(() => {
    leaveCurrentConversation();
    window.removeEventListener("keydown", closeDialogsWithEscape);
});
</script>

<template>
    <AppLayout title="Communauté" :money="money">
        <main class="community-page">
            <section class="community-shell" aria-label="Messagerie de la communauté">
                <aside class="community-conversations" aria-label="Conversations">
                    <header class="community-conversations__header">
                        <div><span class="community-kicker">Communauté</span><h1>Messages</h1></div>
                        <button type="button" class="community-icon-button" aria-label="Créer un groupe" title="Créer un groupe" @click="openCreateGroup"><span aria-hidden="true">+</span></button>
                    </header>

                    <div class="community-conversations__scroll">
                        <section v-if="generalConversation" class="conversation-section">
                            <h2>Salon général</h2>
                            <Link :href="route('social', { id: generalConversation.id })" class="conversation-entry conversation-entry--general" :class="{ 'conversation-entry--active': selectedConversationId === generalConversation.id }" :aria-current="selectedConversationId === generalConversation.id ? 'page' : undefined">
                                <span class="conversation-avatar conversation-avatar--general">G</span>
                                <span class="conversation-entry__content"><strong>Général</strong><small>{{ conversationPreview(generalConversation) }}</small></span>
                            </Link>
                        </section>

                        <section class="conversation-section">
                            <h2>Messages privés</h2>
                            <p v-if="privateConversations.length === 0" class="conversation-empty">Aucun message privé pour le moment.</p>
                            <Link v-for="conversation in privateConversations" :key="conversation.id" :href="route('social', { id: conversation.id })" class="conversation-entry" :class="{ 'conversation-entry--active': selectedConversationId === conversation.id }" :aria-current="selectedConversationId === conversation.id ? 'page' : undefined">
                                <span class="conversation-avatar">
                                    {{ initials(conversation.display_name) }}
                                    <i v-if="isLiferOnline(conversation.lifers?.find((member) => member.id !== currentLifer.id)?.id)" aria-label="En ligne"></i>
                                </span>
                                <span class="conversation-entry__content"><strong :class="{ 'is-staff': staffRole(otherConversationLifer(conversation)) }">{{ conversation.display_name }}</strong><small>{{ conversationPreview(conversation) }}</small></span>
                            </Link>
                        </section>

                        <section class="conversation-section">
                            <div class="conversation-section__heading"><h2>Mes groupes</h2><button type="button" @click="openCreateGroup">Créer</button></div>
                            <p v-if="groupConversations.length === 0" class="conversation-empty">Crée ton premier groupe avec les Lifers de ton choix.</p>
                            <Link v-for="conversation in groupConversations" :key="conversation.id" :href="route('social', { id: conversation.id })" class="conversation-entry" :class="{ 'conversation-entry--active': selectedConversationId === conversation.id }" :aria-current="selectedConversationId === conversation.id ? 'page' : undefined">
                                <span class="conversation-avatar conversation-avatar--group">{{ initials(conversation.display_name) }}</span>
                                <span class="conversation-entry__content"><strong>{{ conversation.display_name }}</strong><small>{{ conversationPreview(conversation) }}</small></span>
                            </Link>
                        </section>
                    </div>
                </aside>

                <section class="community-chat" aria-label="Messages">
                    <header class="community-chat__header">
                        <div class="community-chat__identity">
                            <span class="conversation-avatar" :class="{ 'conversation-avatar--general': selectedConversation?.type === 'general', 'conversation-avatar--group': selectedConversation?.type === 'group' }">
                                {{ selectedConversation?.type === "general" ? "G" : initials(selectedConversation?.display_name) }}
                            </span>
                            <div><h2>{{ selectedConversation?.display_name }}</h2><p>{{ selectedConversationSubtitle }}</p></div>
                        </div>
                        <div v-if="selectedConversationIsGroup" class="community-chat__group-actions">
                            <button type="button" class="community-action-button" :disabled="availableGroupMembers.length === 0" @click="openAddMembers">Ajouter</button>
                            <button type="button" class="community-action-button community-action-button--quiet" :disabled="leavingGroup" @click="leaveGroup">{{ leavingGroup ? "Départ…" : "Quitter" }}</button>
                        </div>
                    </header>

                    <div ref="messagesContainer" class="community-messages" aria-live="polite">
                        <div v-if="messages.length === 0" class="community-messages__empty">
                            <span class="conversation-avatar conversation-avatar--empty">{{ selectedConversation?.type === "general" ? "G" : initials(selectedConversation?.display_name) }}</span>
                            <h3>La conversation commence ici</h3><p>Envoie le premier message à cet espace.</p>
                        </div>
                        <article v-for="message in messages" :key="message.id" class="community-message" :class="{ 'community-message--own': isOwnMessage(message) }">
                            <span class="community-message__avatar" aria-hidden="true">{{ initials(messageAuthor(message)) }}</span>
                            <div class="community-message__body">
                                <div class="community-message__meta">
                                    <Link :href="isOwnMessage(message) ? route('profil') : route('lifers.profile.show', message.sender?.id ?? message.sender_lifer_id)" :class="{ 'is-staff': staffRole(message.sender) }">{{ isOwnMessage(message) ? "Toi" : messageAuthor(message) }}</Link>
                                    <span v-if="staffRole(message.sender)" class="community-staff-badge">{{ staffRoleLabel(staffRole(message.sender)) }}</span>
                                    <time :datetime="message.created_at">{{ formatMessageTime(message.created_at) }}</time>
                                </div>
                                <p>{{ message.content }}</p>
                            </div>
                        </article>
                    </div>

                    <form class="community-composer" @submit.prevent="sendMessage">
                        <label for="new-chat-message" class="sr-only">Nouveau message</label>
                        <textarea id="new-chat-message" v-model="newMessage" rows="1" maxlength="2000" autocomplete="off" :placeholder="`Écrire dans ${selectedConversation?.display_name ?? 'la conversation'}…`" @keydown.enter.exact.prevent="sendMessage"></textarea>
                        <button type="submit" :disabled="sending || !newMessage.trim()">{{ sending ? "Envoi…" : "Envoyer" }}</button>
                        <p v-if="errorMessage" role="alert">{{ errorMessage }}</p>
                    </form>
                </section>

                <aside class="community-directory" aria-label="Lifers">
                    <section v-if="selectedConversationIsGroup" class="community-directory__section">
                        <div class="community-directory__heading"><div><span class="community-kicker">Ce groupe</span><h2>Participants</h2></div><span>{{ conversationMembers.length }}</span></div>
                        <ul class="community-lifer-list">
                            <li v-for="member in conversationMembers" :key="member.id">
                                <span class="community-lifer-avatar">{{ initials(member) }}</span>
                                <Link :href="member.id === currentLifer.id ? route('profil') : route('lifers.profile.show', member.id)" class="community-lifer-name"><strong :class="{ 'is-staff': staffRole(member) }">{{ member.id === currentLifer.id ? "Toi" : fullName(member) }}</strong><small>{{ staffRole(member) ? staffRoleLabel(staffRole(member)) : isLiferOnline(member.id) ? "En ligne" : "Membre" }}</small></Link>
                            </li>
                        </ul>
                    </section>

                    <section class="community-directory__section">
                        <div class="community-directory__heading"><div><span class="community-kicker">Annuaire</span><h2>Les Lifers</h2></div><span>{{ otherLifers.length }}</span></div>
                        <p class="community-directory__hint">Choisis un Lifer pour ouvrir une conversation privée.</p>
                        <ul class="community-lifer-list">
                            <li v-for="lifer in otherLifers" :key="lifer.id" @contextmenu.prevent="startPrivateConversation(lifer)">
                                <span class="community-lifer-avatar">{{ initials(lifer.persoName) }}<i v-if="isLiferOnline(lifer.id)" aria-label="En ligne"></i></span>
                                <Link :href="route('lifers.profile.show', lifer.id)" class="community-lifer-name"><strong :class="{ 'is-staff': lifer.staff_role }">{{ lifer.persoName }}</strong><small>{{ lifer.staff_role ? staffRoleLabel(lifer.staff_role) : isLiferOnline(lifer.id) ? "En ligne" : "Hors ligne" }}</small></Link>
                                <button type="button" :disabled="openingPrivateConversationFor === lifer.id" :aria-label="`Écrire à ${lifer.persoName}`" @click="startPrivateConversation(lifer)">{{ openingPrivateConversationFor === lifer.id ? "…" : "Écrire" }}</button>
                            </li>
                        </ul>
                    </section>
                </aside>
            </section>
        </main>

        <div v-if="showCreateGroup" class="community-modal-backdrop" @click.self="closeCreateGroup">
            <section class="community-modal" role="dialog" aria-modal="true" aria-labelledby="create-group-title">
                <header><div><span class="community-kicker">Nouvelle conversation</span><h2 id="create-group-title">Créer un groupe</h2></div><button type="button" aria-label="Fermer" @click="closeCreateGroup">×</button></header>
                <form @submit.prevent="createGroup">
                    <label class="community-field"><span>Nom du groupe</span><input v-model="groupName" type="text" maxlength="80" required placeholder="Ex. Les voisins" autofocus /></label>
                    <fieldset class="community-member-picker">
                        <legend>Choisir les membres</legend><p>Sélectionne au moins un autre Lifer.</p>
                        <label v-for="lifer in otherLifers" :key="lifer.id">
                            <input v-model="newGroupMemberIds" type="checkbox" :value="lifer.id" />
                            <span class="community-lifer-avatar">{{ initials(lifer.persoName) }}</span>
                            <span><strong>{{ lifer.persoName }}</strong><small>{{ isLiferOnline(lifer.id) ? "En ligne" : "Hors ligne" }}</small></span>
                        </label>
                    </fieldset>
                    <p v-if="groupError" class="community-modal__error" role="alert">{{ groupError }}</p>
                    <footer><button type="button" class="community-modal__cancel" @click="closeCreateGroup">Annuler</button><button type="submit" class="community-modal__submit" :disabled="savingGroup || !groupName.trim() || newGroupMemberIds.length === 0">{{ savingGroup ? "Création…" : "Créer le groupe" }}</button></footer>
                </form>
            </section>
        </div>

        <div v-if="showAddMembers" class="community-modal-backdrop" @click.self="closeAddMembers">
            <section class="community-modal" role="dialog" aria-modal="true" aria-labelledby="add-members-title">
                <header><div><span class="community-kicker">{{ selectedConversation?.display_name }}</span><h2 id="add-members-title">Ajouter des Lifers</h2></div><button type="button" aria-label="Fermer" @click="closeAddMembers">×</button></header>
                <form @submit.prevent="addMembers">
                    <fieldset class="community-member-picker">
                        <legend class="sr-only">Lifers disponibles</legend>
                        <p v-if="availableGroupMembers.length === 0">Tous les Lifers disponibles font déjà partie du groupe.</p>
                        <label v-for="lifer in availableGroupMembers" :key="lifer.id">
                            <input v-model="addedMemberIds" type="checkbox" :value="lifer.id" />
                            <span class="community-lifer-avatar">{{ initials(lifer.persoName) }}</span>
                            <span><strong>{{ lifer.persoName }}</strong><small>{{ isLiferOnline(lifer.id) ? "En ligne" : "Hors ligne" }}</small></span>
                        </label>
                    </fieldset>
                    <p v-if="groupError" class="community-modal__error" role="alert">{{ groupError }}</p>
                    <footer><button type="button" class="community-modal__cancel" @click="closeAddMembers">Annuler</button><button type="submit" class="community-modal__submit" :disabled="savingGroup || addedMemberIds.length === 0">{{ savingGroup ? "Ajout…" : "Ajouter au groupe" }}</button></footer>
                </form>
            </section>
        </div>
    </AppLayout>
</template>

<style scoped>
.community-page{width:min(100%,1540px);margin-inline:auto;padding:clamp(18px,2.2vw,32px)}
.community-shell{display:grid;min-height:680px;height:calc(100vh - 122px);max-height:940px;overflow:hidden;border:1px solid rgb(70 50 78/8%);border-radius:22px;grid-template-columns:minmax(230px,.72fr) minmax(440px,1.75fr) minmax(240px,.78fr);color:#46324e;background:#f8f3ec;box-shadow:0 14px 36px rgb(70 50 78/10%)}
.community-kicker{color:#6f927b;font-size:10px;font-weight:800;letter-spacing:.11em;text-transform:uppercase}
.community-conversations,.community-directory{min-width:0;background:#f4eee5}.community-conversations{display:flex;border-right:1px solid rgb(70 50 78/9%);flex-direction:column}
.community-conversations__header{display:flex;min-height:88px;padding:20px;border-bottom:1px solid rgb(70 50 78/8%);align-items:center;justify-content:space-between;gap:16px}
.community-conversations__header h1,.community-chat__header h2,.community-directory__heading h2,.community-modal h2,.community-messages__empty h3{margin:3px 0 0;font-family:"Bricolage Grotesque",ui-sans-serif,system-ui,sans-serif;font-weight:700;letter-spacing:-.025em}.community-conversations__header h1{font-size:27px}
.community-icon-button{display:inline-grid;width:42px;height:42px;border:0;border-radius:13px;place-items:center;color:#46324e;background:#d6a84a;box-shadow:0 7px 16px rgb(70 50 78/11%);font-size:25px;font-weight:600;line-height:1}
.community-conversations__scroll,.community-directory,.community-messages{min-height:0;overflow-y:auto;scrollbar-color:rgb(70 50 78/24%) transparent;scrollbar-width:thin}.community-conversations__scroll{padding:10px}
.conversation-section{padding:10px 0 8px}.conversation-section>h2,.conversation-section__heading h2{margin:0;color:rgb(70 50 78/57%);font-size:10px;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.conversation-section>h2,.conversation-section__heading{padding:0 9px 8px}.conversation-section__heading{display:flex;align-items:center;justify-content:space-between}.conversation-section__heading button{min-height:32px;border:0;color:#6a4d18;background:transparent;font-size:11px;font-weight:800}
.conversation-entry{display:flex;min-height:62px;padding:8px 9px;border-radius:13px;align-items:center;gap:10px;color:inherit;text-decoration:none;transition:background 160ms ease}.conversation-entry:hover{background:rgb(255 252 247/78%)}.conversation-entry--active,.conversation-entry--active:hover{background:#fcf8f2;box-shadow:0 5px 14px rgb(70 50 78/7%)}
.conversation-avatar,.community-lifer-avatar,.community-message__avatar{position:relative;display:inline-grid;flex:0 0 auto;border-radius:50%;place-items:center;color:#46324e;background:rgb(111 146 123/22%);font-weight:800}.conversation-avatar{width:42px;height:42px;font-size:12px}.conversation-avatar--general{color:#5e4514;background:rgb(214 168 74/34%)}.conversation-avatar--group{color:#73394a;background:rgb(217 142 155/28%)}
.conversation-avatar i,.community-lifer-avatar i{position:absolute;right:0;bottom:1px;width:10px;height:10px;border:2px solid #f4eee5;border-radius:50%;background:#6f927b}.conversation-entry__content{display:grid;min-width:0;gap:3px}.conversation-entry__content strong{overflow:hidden;font-size:13px;text-overflow:ellipsis;white-space:nowrap}.conversation-entry__content small,.community-lifer-name small{overflow:hidden;color:rgb(70 50 78/58%);font-size:10px;text-overflow:ellipsis;white-space:nowrap}.conversation-empty{margin:2px 9px 4px;color:rgb(70 50 78/57%);font-size:11px;line-height:1.45}
.community-chat{display:grid;min-width:0;min-height:0;grid-template-rows:auto minmax(0,1fr) auto;background:#fcf8f2}.community-chat__header{display:flex;min-height:88px;padding:15px 20px;border-bottom:1px solid rgb(70 50 78/8%);align-items:center;justify-content:space-between;gap:18px}.community-chat__identity{display:flex;min-width:0;align-items:center;gap:12px}.community-chat__identity>div{min-width:0}.community-chat__header h2{overflow:hidden;font-size:20px;text-overflow:ellipsis;white-space:nowrap}.community-chat__header p{margin:3px 0 0;color:rgb(70 50 78/60%);font-size:11px}.community-chat__group-actions{display:flex;flex:0 0 auto;gap:7px}
.community-action-button{min-height:38px;padding:7px 12px;border:1px solid rgb(70 50 78/9%);border-radius:10px;color:#46324e;background:rgb(214 168 74/24%);font-size:11px;font-weight:800}.community-action-button--quiet{color:#743344;background:rgb(217 142 155/12%)}
.community-messages{display:flex;padding:24px clamp(16px,3vw,34px);flex-direction:column;gap:14px;background:radial-gradient(circle at 90% 10%,rgb(214 168 74/8%),transparent 30%),#fcf8f2}.community-messages__empty{display:grid;max-width:320px;margin:auto;justify-items:center;color:rgb(70 50 78/66%);text-align:center}.conversation-avatar--empty{width:58px;height:58px;margin-bottom:10px}.community-messages__empty h3{color:#46324e;font-size:19px}.community-messages__empty p{margin:6px 0 0;font-size:12px}
.community-message{display:flex;max-width:min(78%,620px);align-self:flex-start;align-items:flex-end;gap:9px}.community-message--own{align-self:flex-end;flex-direction:row-reverse}.community-message__avatar{width:30px;height:30px;margin-bottom:2px;font-size:9px}.community-message--own .community-message__avatar{color:#5c4211;background:rgb(214 168 74/32%)}.community-message__body{min-width:0;padding:10px 13px;border:1px solid rgb(70 50 78/7%);border-radius:14px 14px 14px 4px;background:#f4eee5;box-shadow:0 4px 11px rgb(70 50 78/5%)}.community-message--own .community-message__body{border-radius:14px 14px 4px;background:rgb(214 168 74/25%)}.community-message__meta{display:flex;margin-bottom:4px;align-items:baseline;gap:8px}.community-message__meta a{color:#46324e;font-size:10px;font-weight:700;text-decoration:none}.community-message__meta a:hover{text-decoration:underline}.community-message__meta time{color:rgb(70 50 78/48%);font-size:9px}.community-message__body p{margin:0;overflow-wrap:anywhere;font-size:13px;line-height:1.45;white-space:pre-wrap}
.conversation-entry__content strong.is-staff,.community-message__meta a.is-staff,.community-lifer-name strong.is-staff{color:#8e344b}.community-staff-badge{padding:2px 5px;border-radius:999px;color:#8e344b;background:rgb(142 52 75/9%);font-size:7px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
.community-composer{display:grid;padding:14px 18px 16px;border-top:1px solid rgb(70 50 78/8%);grid-template-columns:minmax(0,1fr) auto;gap:10px;background:#fcf8f2}.community-composer textarea{min-height:48px;max-height:120px;padding:13px 15px;resize:vertical;border:1px solid rgb(70 50 78/16%);border-radius:13px;color:#46324e;background:#fffaf4;font:inherit;font-size:13px;line-height:1.45}.community-composer button,.community-modal__submit{min-height:48px;padding:10px 18px;border:0;border-radius:13px;color:#46324e;background:#d6a84a;box-shadow:0 7px 16px rgb(70 50 78/10%);font-size:12px;font-weight:800}.community-composer p{margin:0;grid-column:1/-1;color:#8b3f51;font-size:11px}
.community-directory{border-left:1px solid rgb(70 50 78/9%);padding:18px 14px}.community-directory__section+.community-directory__section{margin-top:24px;padding-top:22px;border-top:1px solid rgb(70 50 78/9%)}.community-directory__heading{display:flex;padding:0 4px;align-items:center;justify-content:space-between;gap:10px}.community-directory__heading h2{font-size:18px}.community-directory__heading>span{display:inline-grid;min-width:28px;height:28px;padding:0 7px;border-radius:999px;place-items:center;color:#385443;background:rgb(111 146 123/18%);font-size:10px;font-weight:800}.community-directory__hint{margin:12px 4px;color:rgb(70 50 78/58%);font-size:10px;line-height:1.45}
.community-lifer-list{display:grid;margin:12px 0 0;padding:0;gap:4px;list-style:none}.community-lifer-list li{display:flex;min-width:0;min-height:50px;padding:6px 5px;border-radius:11px;align-items:center;gap:9px}.community-lifer-list li:hover{background:rgb(252 248 242/75%)}.community-lifer-avatar{width:34px;height:34px;font-size:9px}.community-lifer-name{display:grid;min-width:0;flex:1 1 auto;gap:2px;color:#46324e;text-decoration:none}.community-lifer-name:hover strong{text-decoration:underline}.community-lifer-name strong{overflow:hidden;font-size:11px;text-overflow:ellipsis;white-space:nowrap}.community-lifer-list button{min-height:34px;padding:6px 8px;border:0;border-radius:9px;color:#46324e;background:rgb(214 168 74/22%);font-size:9px;font-weight:800}
.community-modal-backdrop{position:fixed;z-index:80;inset:0;display:grid;padding:20px;overflow-y:auto;place-items:center;background:rgb(42 29 46/46%);backdrop-filter:blur(3px)}.community-modal{width:min(100%,520px);max-height:min(720px,calc(100vh - 40px));overflow:hidden;border:1px solid rgb(70 50 78/9%);border-radius:20px;color:#46324e;background:#f8f3ec;box-shadow:0 22px 60px rgb(42 29 46/24%)}.community-modal>header{display:flex;padding:22px 24px 16px;align-items:flex-start;justify-content:space-between;gap:16px}.community-modal h2{font-size:25px}.community-modal>header>button{display:inline-grid;width:40px;height:40px;border:0;border-radius:11px;place-items:center;color:#46324e;background:rgb(70 50 78/7%);font-size:22px}.community-modal form{display:grid;max-height:calc(100vh - 150px);padding:0 24px 22px;grid-template-rows:auto minmax(0,1fr) auto auto;gap:16px}.community-field{display:grid;gap:7px;font-size:12px;font-weight:800}.community-field input{min-height:50px;padding:11px 13px;border:1px solid rgb(70 50 78/17%);border-radius:12px;color:#46324e;background:#fcf8f2;font:inherit;font-weight:500}
.community-member-picker{min-height:0;margin:0;padding:0;overflow-y:auto;border:0}.community-member-picker legend{padding:0;font-size:12px;font-weight:800}.community-member-picker>p{margin:4px 0 10px;color:rgb(70 50 78/60%);font-size:10px}.community-member-picker>label{display:flex;min-height:54px;padding:7px 9px;border-radius:11px;align-items:center;gap:10px;cursor:pointer}.community-member-picker>label:hover{background:#fcf8f2}.community-member-picker input[type=checkbox]{width:18px;height:18px;accent-color:#6f927b}.community-member-picker label>span:last-child{display:grid;gap:2px}.community-member-picker strong{font-size:12px}.community-member-picker small{color:rgb(70 50 78/56%);font-size:10px}.community-modal__error{margin:0;color:#8b3f51;font-size:11px}.community-modal footer{display:flex;justify-content:flex-end;gap:9px}.community-modal__cancel{min-height:48px;padding:10px 16px;border:1px solid rgb(70 50 78/12%);border-radius:13px;color:#46324e;background:transparent;font-size:12px;font-weight:800}
button:disabled{cursor:not-allowed;opacity:.48}button:not(:disabled),.conversation-entry{cursor:pointer}button:focus-visible,a:focus-visible,textarea:focus-visible,input:focus-visible{outline:3px solid #46324e;outline-offset:3px}
@media(max-width:1199px){.community-shell{height:auto;max-height:none;grid-template-columns:minmax(220px,.65fr) minmax(420px,1.35fr)}.community-conversations,.community-chat{height:min(760px,calc(100vh - 122px));min-height:650px}.community-directory{display:grid;border-top:1px solid rgb(70 50 78/9%);border-left:0;grid-column:1/-1;grid-template-columns:repeat(2,minmax(0,1fr));gap:24px}.community-directory__section+.community-directory__section{margin:0;padding:0 0 0 24px;border-top:0;border-left:1px solid rgb(70 50 78/9%)}}
@media(max-width:759px){.community-page{padding:14px}.community-shell{display:flex;border-radius:16px;flex-direction:column;overflow:visible}.community-conversations,.community-chat{height:auto;min-height:0}.community-conversations{border-right:0}.community-conversations__header{min-height:78px}.community-conversations__scroll{display:flex;padding:6px 10px 12px;overflow-x:auto;gap:10px}.conversation-section{display:flex;flex:0 0 auto;padding:0;align-items:flex-end;gap:6px}.conversation-section>h2,.conversation-section__heading,.conversation-empty{display:none}.conversation-entry{width:64px;min-height:70px;padding:6px;flex-direction:column;justify-content:center;gap:5px}.conversation-entry__content{width:100%}.conversation-entry__content strong{font-size:9px;text-align:center}.conversation-entry__content small{display:none}.community-chat{min-height:620px;border-top:1px solid rgb(70 50 78/9%);grid-template-rows:auto minmax(420px,1fr) auto}.community-chat__header{min-height:78px;padding:12px 14px}.community-chat__header .conversation-avatar{display:none}.community-chat__group-actions{gap:4px}.community-action-button{min-height:36px;padding:6px 8px;font-size:9px}.community-messages{padding:18px 12px}.community-message{max-width:90%}.community-message__avatar{display:none}.community-composer{padding:10px}.community-composer button{padding-inline:12px}.community-directory{display:block;grid-column:auto}.community-directory__section+.community-directory__section{margin-top:24px;padding:22px 0 0;border-top:1px solid rgb(70 50 78/9%);border-left:0}.community-modal-backdrop{padding:10px;align-items:end}.community-modal{max-height:calc(100vh - 20px);border-radius:18px 18px 12px 12px}.community-modal>header,.community-modal form{padding-right:18px;padding-left:18px}}
@media(prefers-reduced-motion:reduce){.conversation-entry{transition:none}}
</style>
