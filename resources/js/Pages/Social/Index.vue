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
        <div class="flex justify-center items-center space-x-2 text-slate-900">
            <p class="font-bold">
                Il existe des bugs, si vous ne vous voyez pas en ligne,
                actualisez la page
            </p>
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
                class="size-6 text-emerald-800"
            >
                <path
                    d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM16.8201 17.0761C18.1628 15.8007 19 13.9981 19 12C19 8.13401 15.866 5 12 5C10.9391 5 9.9334 5.23599 9.03241 5.65834L10.0072 7.41292C10.6177 7.14729 11.2917 7 12 7C14.7614 7 17 9.23858 17 12H14L16.8201 17.0761ZM14.9676 18.3417L13.9928 16.5871C13.3823 16.8527 12.7083 17 12 17C9.23858 17 7 14.7614 7 12H10L7.17993 6.92387C5.83719 8.19929 5 10.0019 5 12C5 15.866 8.13401 19 12 19C13.0609 19 14.0666 18.764 14.9676 18.3417Z"
                ></path>
            </svg>
        </div>
        <div
            class="flex flex-grow bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md h-[70vh] my-auto"
        >
            <!-- Liste des conversations -->
            <!-- <div class="border-r-2 border-gray-400 w-1/5">
                <div class="overflow-auto">
                    <ul>
                        <h2 class="text-lg mb-2">Salon de discussion</h2>
                        <li
                            v-for="conversation in conversations"
                            :key="conversation.id"
                            class="text-sm mb-1"
                        >
                            <Link
                                :href="route('social', { id: conversation.id })"
                                class="text-sm font-semibold"
                            >
                                {{
                                    conversation.name || "Conversation sans nom"
                                }}
                            </Link>
                        </li>
                    </ul>
                </div>
            </div> -->

            <!-- Fenêtre de chat -->
            <div class="w-full h-full">
                <div
                    class="messages overflow-auto h-5/6 custom-scrollbar p-2"
                    ref="messagesContainer"
                >
                    <!-- Affiche les messages de la conversation sélectionnée -->
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="p-2"
                    >
                        <template
                            v-if="message.sender && allPerso[message.sender.id]"
                        >
                            <p
                                :class="
                                    allPerso[message.sender.id].isAdmin
                                        ? 'text-red-500 font-bold'
                                        : 'text-amber-500 font-semibold'
                                "
                            >
                                {{ allPerso[message.sender.id].persoName }}
                            </p>
                            <p>{{ message.content }}</p>
                        </template>
                        <template v-else>
                            <p class="text-gray-500 italic">
                                Message d'un utilisateur supprimé
                            </p>
                        </template>
                    </div>
                </div>

                <!-- Entrée pour un nouveau message -->
                <div class="message-input p-2">
                    <form @submit.prevent="sendMessage" class="flex flex-col">
                        <input
                            v-model="newMessage"
                            type="text"
                            placeholder="Tapez votre message ici..."
                            class="border-2 border-gray-300 w-full p-2 text-slate-800 focus:border-gray-500"
                        />
                        <div class="flex items-end justify-end mt-4">
                            <button
                                type="submit"
                                class="px-4 py-2 bg-gray-300/50 hover:bg-emerald-950 border border-transparent rounded-md font-semibold text-xs percase tracking-widest transition-all duration-300 ease-in-out"
                            >
                                Envoyer
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Liste des utilisateurs en ligne -->
            <div class="border-l-2 border-gray-400 w-1/5 md:pl-4">
                <div class="overflow-auto h-full">
                    <ul>
                        <p class="text-sm md:text-lg mb-2">En ligne</p>
                        <li v-for="user in onlineUsers" :key="user.id">
                            <template v-if="allPerso[user.id]">
                                <!-- Vérifier si l'utilisateur est admin et appliquer une classe conditionnelle -->
                                <p
                                    :class="
                                        allPerso[user.id].isAdmin
                                            ? 'text-red-500 text-sm font-bold'
                                            : 'text-cyan-500 text-sm font-semibold'
                                    "
                                >
                                    {{ allPerso[user.id].persoName }}
                                </p>
                            </template>
                            <template v-else> </template>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
