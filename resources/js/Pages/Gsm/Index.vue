<script setup>
import { defineProps, ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import Echo from "laravel-echo";

const props = defineProps({
    perso: Object,
        user: Object,
    personnages: Array,
});
const isModalOpen = ref(false);
const receiver = ref("");
const message = ref("");
const search = ref("");
const selectedPersonnage = ref(null);

const openModal = () => {
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};
console.log(props.personnages);
const filteredPersonnages = computed(() => {
    if (!search.value) return [];
    return props.personnages
        .filter((perso) =>
            perso.fullName.toLowerCase().includes(search.value.toLowerCase())
        )
        .slice(0, 5);
});

function selectPersonnage(perso) {
    selectedPersonnage.value = perso;
    search.value = perso.fullName;
}

function sendSms() {
    if (message.value.trim() && selectedPersonnage.value) {
        router.post("/sms/send", {
            receiver_id: selectedPersonnage.value.id,
            content: message.value,
        });

        message.value = "";
        receiver.value = "";
        closeModal();
    }
}

// Echo.private(`sms-channel.${userId}`).listen(".SmsMessageSent", (e) => {
//     console.log("Nouveau SMS:", e.smsMessage);
// });
</script>

<template>
    <AppLayout title="GSM">
        <div class="flex justify-center">
            <div class="container max-w-2xl p-4">
                <button
                    @click="openModal"
                    class="mb-4 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Nouvelle Discussion
                </button>
                <div
                    v-if="isModalOpen"
                    class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4"
                >
                    <div class="bg-white p-6 rounded-lg max-w-sm w-full">
                        <form @submit.prevent="sendSms" class="space-y-4">
                            <div class="mb-4 relative">
                                <label
                                    for="search"
                                    class="block text-sm font-medium text-gray-700"
                                    >Destinataire:</label
                                >
                                <input
                                    id="search"
                                    type="text"
                                    v-model="search"
                                    placeholder="Commencez à taper..."
                                    @input="selectedPersonnage = null"
                                    class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                />
                                <button
                                    v-if="selectedPersonnage"
                                    @click="resetSelection"
                                    class="absolute right-3 top-3 text-gray-500 hover:text-gray-700"
                                >
                                    <svg
                                        class="h-5 w-5 fill-current"
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20"
                                    >
                                        <path
                                            d="M10 2C5.589 2 2 5.589 2 10s3.589 8 8 8 8-3.589 8-8-3.589-8-8-8zm3.707 11.293a1 1 0 00-1.414 0L10 13.586l-2.293-2.293a1 1 0 00-1.414 1.414L8.586 15l-2.293 2.293a1 1 0 101.414 1.414L10 16.414l2.293 2.293a1 1 0 001.414-1.414L11.414 15l2.293-2.293a1 1 0 000-1.414z"
                                        />
                                    </svg>
                                </button>
                                <ul
                                    v-if="
                                        !selectedPersonnage &&
                                        filteredPersonnages.length
                                    "
                                    class="absolute mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm"
                                >
                                    <li
                                        v-for="perso in filteredPersonnages"
                                        :key="perso.id"
                                        @click="selectPersonnage(perso)"
                                        class="text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100"
                                    >
                                        {{ perso.fullName }}
                                    </li>
                                </ul>
                            </div>
                            <div class="mb-6">
                                <label
                                    for="message"
                                    class="block text-sm font-medium text-gray-700"
                                    >Message:</label
                                >
                                <textarea
                                    id="message"
                                    v-model="message"
                                    required
                                    rows="4"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    placeholder="Écrivez votre message ici..."
                                ></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Envoyer
                                </button>
                                <button
                                    @click="closeModal"
                                    class="ml-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Fermer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- <div class="discussion-list">
                <div
                    v-for="conversation in conversations"
                    :key="conversation.id"
                    class="discussion-preview"
                    @click="goToConversation(conversation.id)"
                >
                    <div class="sender-name">
                        {{ conversation.lastMessage.senderName }}
                    </div>
                    <div class="message-preview">
                        {{ trimMessage(conversation.lastMessage.content) }}
                    </div>
                </div>
            </div> -->
        </div>
    </AppLayout>
</template>
