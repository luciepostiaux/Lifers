<script setup>
import { ref, defineProps } from "vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    studyDetails: Object,
    associatedStudies: Array,
    enrollmentDetails: Object,
});

const showModal = ref(false); // État pour contrôler la visibilité du modal

const resignFromStudy = () => {
    router.post(
        // Utilisation de router.post
        route("study.resign"),
        {},
        {
            onFinish: () => {
                // Code à exécuter après la fin de l'action
                showModal.value = false;
            },
        }
    );
};

const claimDiploma = () => {
    if (props.studyDetails && props.studyDetails.id) {
        router.post(
            // Utilisation de router.post
            route("study.claimDiploma", { study: props.studyDetails.id }),
            {},
            {
                onSuccess: () => {
                    // Logique de succès ici
                },
                onError: () => {
                    // Logique d'erreur ici
                },
            }
        );
    } else {
        console.error("studyDetails is not defined or missing ID");
    }
};
</script>

<template>
    <AppLayout title="Suivi des études">
        <div class="container mx-auto p-4">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-3/4 space-y-2">
                    <h2 class="text-2xl font-bold mb-4">
                        Étude en cours : {{ studyDetails.name }}
                    </h2>
                    <!-- Affichage de la première description -->
                    <p>{{ studyDetails.description_1 }}</p>
                    <!-- Ajouté pour afficher la description plus détaillée si elle existe -->
                    <p
                        class="text-sm"
                        v-if="enrollmentDetails && enrollmentDetails.end_date"
                    >
                        Fin prévue :
                        {{
                            new Date(
                                enrollmentDetails.end_date
                            ).toLocaleDateString()
                        }}
                    </p>
                </div>
                <img
                    src="/images/places/university.webp"
                    alt="University Image"
                    class="size-64"
                />
            </div>

            <div class="my-6">
                <h3 class="text-lg font-semibold">Détails de l'étude</h3>
                <!-- Suivi de l'étude : Affichage de la date de fin prévue -->
                <p class="text-sm" v-if="studyDetails && studyDetails.end_date">
                    Fin prévue :
                    {{ new Date(studyDetails.end_date).toLocaleDateString() }}
                </p>

                <!-- Ajouter d'autres informations utiles ici si nécessaire -->
            </div>
            <button
                @click="showModal = true"
                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
            >
                Abandonner les études
            </button>

            <div v-if="associatedStudies">
                <h3 class="text-lg font-semibold mb-4">Études associées</h3>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="study in associatedStudies"
                        :key="study.id"
                        class="py-4"
                    >
                        <div class="flex items-center justify-between">
                            <h4 class="text-md font-semibold">
                                {{ study.name }}
                            </h4>
                            <Link
                                :href="`/study/${study.id}`"
                                class="text-sm bg-[#9EE5F5] hover:text-white rounded px-4 py-2 hover:bg-[#71A4B0] transition-all"
                                >Explorer</Link
                            >
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ study.description }}
                        </p>
                    </div>
                </div>
            </div>
            <button
                v-if="new Date(enrollmentDetails.end_date) < new Date()"
                @click="claimDiploma"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
            >
                Récupérer le diplôme
            </button>
        </div>
        <ConfirmationModal
            :show="showModal"
            @close="showModal = false"
            @confirm="resignFromStudy"
        >
            <template #title> Abandonner les études en cours </template>
            <template #content>
                Êtes-vous sûr de vouloir abandonner vos études actuelles ?
            </template>
            <template #footer>
                <button
                    @click="showModal = false"
                    class="px-4 py-2 bg-gray-200 text-black rounded"
                >
                    Annuler
                </button>
                <button
                    @click="resignFromStudy"
                    class="px-4 py-2 bg-red-600 text-white rounded"
                >
                    Confirmer et abandonner
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
