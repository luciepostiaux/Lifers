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
        <div class="md:pt-24">
            <div class="flex flex-col md:flex-row mb-4 w-full h-full">
                <div
                    class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-white p-4 mr-4 rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <h2 class="text-3xl font-bold mb-4">
                            Métier actuel : {{ jobDetails.name }}
                        </h2>
                        <p>{{ jobDetails.description_1 }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="flex flex-col">
                            <h3 class="text-lg font-semibold">
                                Détails du métier
                            </h3>
                            <p class="text-sm">
                                Salaire : {{ jobDetails.salary }}€
                            </p>
                        </div>
                        <!-- Bouton de démission -->
                        <button
                            @click="showModal = true"
                            class="mt-4 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-all"
                        >
                            Démissionner
                        </button>
                    </div>
                </div>
                <div
                    class="flex-1 md:flex-auto md:w-2/5 lg:w-2/5 rounded-lg shadow-md"
                >
                    <img
                        src="/images/places/poleemploi_4-6.webp"
                        alt="University Image"
                        class="object-cover h-full rounded-lg shadow-lg"
                    />
                </div>
            </div>
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
