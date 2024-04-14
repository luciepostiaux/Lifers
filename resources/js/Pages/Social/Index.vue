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
                    },
                }
            )
            .then((response) => {
                newMessage.value = "";
                messages.value.push(response.data);
                scrollToBottom();
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
    })
    .joining((user) => {
        console.log("Un utilisateur a rejoint:", user);
    })
    .leaving((user) => {
        console.log("Un utilisateur a quitté:", user);
    })
    .listen("MessageSent", (e) => {
        console.log("Nouveau message:", e.message);
        messages.value.push(e.message);
        scrollToBottom();
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
        <div class="container flex h-screen mx-auto">
            <div class="w-screen">
                <div class="flex bg-white p-4 rounded-lg h-screen shadow-md">
                    <!-- Liste des conversations -->
                    <div class="border-r-2 border-gray-300 w-1/5">
                        <div class="overflow-auto h-full">
                            <ul>
                                <li
                                    v-for="conversation in conversations"
                                    :key="conversation.id"
                                >
                                    <Link
                                        :href="
                                            route('social', {
                                                id: conversation.id,
                                            })
                                        "
                                    >
                                        {{
                                            conversation.name ||
                                            "Conversation sans nom"
                                        }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Fenêtre de chat -->
                    <div class="w-full">
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
                                <p class="text-cyan-500 font-semibold">
                                    {{ allPerso[message.sender.id].persoName }}
                                </p>
                                <p class=" ">
                                    {{ message.content }}
                                </p>
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
                    <div class="border-l-2 border-gray-300 w-1/5">
                        <div class="overflow-auto h-full">
                            <ul>
                                <p class="font-bold">En ligne</p>
                                <li v-for="user in onlineUsers" :key="user.id">
                                    <p
                                        class="text-cyan-500 text-sm font-semibold"
                                    >
                                        {{ allPerso[user.id].persoName }}
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
