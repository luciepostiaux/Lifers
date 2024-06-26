<script setup>
import { onMounted, ref, nextTick } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import axios from "axios";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    conversations: Array,
    messages: Array,
    currentConversationId: Number,
    allPerso: Object,
    user: Object,
});

const conversations = ref(props.conversations);
const selectedConversationId = ref(
    props.currentConversationId || conversations.value[0]?.id
);
const messages = ref(props.messages);
const onlineUsers = ref([]);
const messagesContainer = ref(null);

const newMessage = ref("");

function sendMessage() {
    const messageContent = newMessage.value.trim();
    if (messageContent) {
        axios
            .post(
                `/conversations/${selectedConversationId.value}/messages`,
                {
                    content: messageContent,
                },
                {
                    headers: {
                        "Content-Type": "application/json",
                        Accept: "application/json",
                        // 'Authorization': 'Bearer votre_token_jwt_ici', // Si authentification nécessaire
                    },
                }
            )
            .then((response) => {
                newMessage.value = ""; // Réinitialiser le champ de texte après l'envoi
                // Ajouter le message envoyé à la liste des messages
                messages.value.push(response.data);
                scrollToBottom(); // Faire défiler vers le bas
            })
            .catch((error) => {
                console.error(
                    "Il y a eu un problème avec l'opération axios:",
                    error
                );
            });
    }
}

Echo.join(`conversation.${props.currentConversationId}`)
    .here((users) => {
        console.log("Utilisateurs déjà présents:", users);
        onlineUsers.value = users;
        // Mettre à jour l'état avec les utilisateurs déjà présents
    })
    .joining((user) => {
        console.log("Un utilisateur a rejoint:", user);
        // Ajouter cet utilisateur à l'état local
    })
    .leaving((user) => {
        console.log("Un utilisateur a quitté:", user);
        // Retirer cet utilisateur de l'état local
    })
    .listen("MessageSent", (e) => {
        console.log("Nouveau message:", e.message);
        // Ajouter ce nouveau message à l'état des messages
        messages.value.push(e.message);
        scrollToBottom(); // Faire défiler vers le bas
    });

console.log("Echo instance:", window.Echo);

function scrollToBottom() {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTop =
                messagesContainer.value.scrollHeight;
        }
    });
}
</script>

<template>
    <AppLayout title="Chat">
        <div class="chat-container flex h-screen">
            <!-- Liste des conversations -->
            <div class="w-1/6 border-r-2 border-gray-300">
                <div class="overflow-auto h-full">
                    <h3 class="text-center p-2">Channel</h3>

                    <ul>
                        <li
                            v-for="conversation in conversations"
                            :key="conversation.id"
                        >
                            <Link
                                :href="route('social', { id: conversation.id })"
                            >
                                {{
                                    conversation.name || "Conversation sans nom"
                                }}
                            </Link>
                        </li>
                    </ul>
                    <h3 class="text-center p-2">Privé</h3>

                    <ul>
                        <!-- <li
                            v-for="conversation in conversations"
                            :key="conversation.id"
                            @click="selectConversation(conversation.id)"
                        >
                            {{ conversation.name || "Conversation sans nom" }}
                        </li> -->
                    </ul>
                </div>
            </div>

            <!-- Fenêtre de chat -->
            <div class="w-4/6">
                <div
                    class="messages overflow-auto h-5/6 border-b-2 border-gray-300 p-2"
                    ref="messagesContainer"
                >
                    <!-- Affichez ici les messages de la conversation sélectionnée -->
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="p-2"
                    >
                        <p>{{ allPerso[message.sender.id].persoName }}</p>
                        <p>{{ message.content }}</p>
                    </div>
                </div>
                <div class="message-input p-2">
                    <form @submit.prevent="sendMessage">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Tapez votre message ici..."
                            class="border-2 border-gray-300 w-full p-2"
                        />
                        <button type="submit">Envoyer</button>
                    </form>
                </div>
            </div>
            <!-- Liste des utilisateurs en ligne -->
            <div class="w-1/6 border-l-2 border-gray-300">
                <div class="overflow-auto h-full">
                    <h3 class="text-center p-2">Lifers en ligne</h3>
                    <ul>
                        <p class="text-lg font-semibold">En ligne</p>
                        <li v-for="user in onlineUsers" :key="user.id">
                            {{ allPerso[user.id].persoName }}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
