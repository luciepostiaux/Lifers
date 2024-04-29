<script setup>
import { ref, computed, defineProps } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const props = defineProps({
    isOpen: Boolean,
    personnages: Array,
});

const receiver = ref("");
const message = ref("");
const search = ref("");
const selectedPersonnage = ref(null);

function sendSms() {
    if (message.value.trim() && selectedPersonnage.value) {
        Inertia.post("/sms/send", {
            receiver_id: selectedPersonnage.value.id,
            content: message.value,
        });

        message.value = "";
        search.value = "";
        emit("close");
    }
}
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
    search.value = ""; 
}
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4"
    >
        <div class="bg-white p-6 rounded-lg max-w-sm w-full">
            <h2 class="text-lg font-semibold mb-4">Nouveau Message</h2>
            <form @submit.prevent="sendSms">
                <div class="mb-4">
                    <label
                        for="receiver"
                        class="block text-sm font-medium text-gray-700"
                        >Destinataire:</label
                    >
                    <input
                        type="text"
                        v-model="search"
                        placeholder="Rechercher un personnage..."
                        class="input"
                    />
                    <ul v-if="filteredPersonnages">
                        <li
                            v-for="perso in filteredPersonnages"
                            :key="perso.id"
                            @click="selectPersonnage(perso)"
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
                        rows="4"
                        required
                        class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                    ></textarea>
                </div>
                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
