<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import Modal from "@/Components/Modal.vue";

const props = defineProps({
    suggestions: Array,
    bugs: Array,
});

const tab = ref("suggestions");
const showSuggestionModal = ref(false);
const suggestionContent = ref("");

const openSuggestionModal = () => {
    showSuggestionModal.value = true;
};

const closeSuggestionModal = () => {
    showSuggestionModal.value = false;
};

const submitSuggestion = () => {
    // Logique pour envoyer la suggestion à l'API
    console.log("Submitting suggestion:", suggestionContent.value);
    // Ici, tu peux ajouter l'appel API
    closeSuggestionModal();
};

function navigateToTopic(id, type) {
    if (type === "suggestion") {
        router.push(`/suggestions/${id}`);
    } else if (type === "bug") {
        router.push(`/bugs/${id}`);
    }
}

function navigateToAdd(type) {
    // Adjust the path as needed
    router.push(`/add/${type}`);
}
</script>

<template>
    <AppLayout title="Bug and Idea Management">
        <div
            class="container mx-auto bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
        >
            <div class="tabs">
                <button
                    @click="tab = 'suggestions'"
                    :class="{ active: tab === 'suggestions' }"
                >
                    Suggestions
                </button>
                <button
                    @click="tab = 'bugs'"
                    :class="{ active: tab === 'bugs' }"
                >
                    Bugs
                </button>
            </div>

            <div v-if="tab === 'suggestions'" class="suggestions">
                <h2 class="text-lg font-bold">Suggestions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="suggestion in suggestions"
                        :key="suggestion.id"
                        class="p-4 border rounded"
                    >
                        <h3 class="font-semibold">{{ suggestion.title }}</h3>
                        <p>{{ suggestion.content }}</p>
                        <!-- Utilisation du composant Link -->
                        <Link
                            :href="`/suggestions/${suggestion.id}`"
                            class="text-blue-500 underline"
                        >
                            View Topic
                        </Link>
                    </div>
                </div>
                <button @click="openSuggestionModal">Add Suggestion</button>
            </div>

            <div v-if="tab === 'bugs'" class="bugs">
                <h2 class="text-lg font-bold">Reported Bugs</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div
                        v-for="bug in bugs"
                        :key="bug.id"
                        class="p-4 border rounded"
                    >
                        <h3 class="font-semibold">{{ bug.title }}</h3>
                        <p>{{ bug.description }}</p>
                        <!-- Utilisation du composant Link -->
                        <Link
                            :href="`/bugs/${bug.id}`"
                            class="text-blue-500 underline"
                        >
                            View Bug
                        </Link>
                    </div>
                </div>
                <button
                    @click="navigateToAdd('bug')"
                    class="mt-4 px-4 py-2 bg-red-500 text-white rounded"
                >
                    Report Bug
                </button>
            </div>
        </div>
    </AppLayout>

    <!-- Modal -->
    <Modal
        :show="showSuggestionModal"
        @close="closeSuggestionModal"
        maxWidth="md"
    >
        <template #title>
            <h2 class="text-lg">Add New Suggestion</h2>
        </template>
        <template #content>
            <textarea
                v-model="suggestionContent"
                class="w-full border rounded"
                placeholder="Describe your suggestion"
            ></textarea>
        </template>
        <template #footer>
            <button
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                @click="submitSuggestion"
            >
                Submit
            </button>
        </template>
    </Modal>
</template>

<style scoped>
.active {
    background-color: #000;
    color: #fff;
}
</style>
