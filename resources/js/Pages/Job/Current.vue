<script setup>
import { ref, defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import { router } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    jobDetails: Object, // Modification pour utiliser jobDetails au lieu de studyDetails
});

const showModal = ref(false); // État pour contrôler la visibilité du modal

const resign = () => {
    router.post(
        route("job.resign"),
        {},
        {
            onFinish: () => (showModal.value = false),
        }
    );
};
</script>

<template>
    <AppLayout title="Suivi du métier">
        <div class="container mx-auto p-4">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-3/4 space-y-2">
                    <h2 class="text-2xl font-bold mb-4">
                        Métier actuel : {{ jobDetails.name }}
                    </h2>
                    <p>{{ jobDetails.description_1 }}</p>
                    <!-- Supposant que votre job a des descriptions similaires aux études -->
                </div>
            </div>

            <div class="my-6">
                <h3 class="text-lg font-semibold">Détails du métier</h3>
                <p class="text-sm">Salaire : {{ jobDetails.salary }}€</p>
                <!-- Ajout d'un détail sur le salaire -->
            </div>
            <!-- Bouton de démission -->
            <button
                @click="showModal = true"
                class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-all"
            >
                Démissionner
            </button>

            <!-- Modal de confirmation -->
            <ConfirmationModal
                :show="showModal"
                @close="showModal = false"
                @confirm="resign"
            >
                <template #title>
                    <span>Démissionner du poste</span>
                </template>
                <template #content>
                    <p>
                        Êtes-vous sûr de vouloir démissionner de votre poste
                        actuel ? Cette action est irréversible.
                    </p>
                </template>
                <template #footer>
                    <button
                        @click="showModal = false"
                        class="px-4 py-2 mr-3 bg-gray-200 text-black rounded hover:bg-gray-300 transition-all"
                    >
                        Annuler
                    </button>
                    <button
                        @click="resign"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-all"
                    >
                        Confirmer
                    </button>
                </template>
            </ConfirmationModal>
        </div>
    </AppLayout>
</template>
