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
        <template #header></template>

        <div class="md:pt-24">
            <!-- Card principale pour l'étude en cours -->

            <div class="flex flex-col md:flex-row mb-4 w-full h-full">
                <div
                    class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-white p-4 mr-4 rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <h2 class="text-3xl font-bold mb-4">
                            {{ studyDetails.name }}
                        </h2>
                        <p>{{ studyDetails.description_1 }}</p>
                    </div>
                    <div class="flex justify-between items-center">
                        <p
                            v-if="
                                enrollmentDetails && enrollmentDetails.end_date
                            "
                            class="text-sm"
                        >
                            Fin de l'étude :
                            {{
                                new Date(
                                    enrollmentDetails.end_date
                                ).toLocaleDateString()
                            }}
                        </p>
                        <button
                            @click="showModal = true"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors"
                        >
                            Abandonner les études
                        </button>
                        <button
                            v-if="
                                new Date(enrollmentDetails.end_date) <
                                new Date()
                            "
                            @click="claimDiploma"
                            class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                        >
                            Récupérer le diplôme
                        </button>
                    </div>
                </div>

                <div
                    class="flex-1 md:flex-auto md:w-2/5 lg:w-2/5 rounded-lg shadow-md"
                >
                    <img
                        src="/images/places/university_4-6.webp"
                        alt="University Image"
                        class="object-cover h-full rounded-lg shadow-lg"
                    />
                </div>
            </div>

            <!-- Études associées -->
            <!-- <div
                v-if="associatedStudies > 0"
                class="bg-white p-6 rounded-lg shadow-lg mt-4"
            >
                <h3 class="text-lg font-semibold mb-4">Études associées</h3>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="study in associatedStudies"
                        :key="study.id"
                        class="py-4"
                    >
                        <div class="flex justify-between items-center">
                            <h4 class="text-md font-semibold">
                                {{ study.name }}
                            </h4>
                            <Link
                                :href="`/study/${study.id}`"
                                class="bg-blue-500 hover:bg-blue-700 text-white rounded px-4 py-2 transition-colors"
                                >Explorer</Link
                            >
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ study.description }}
                        </p>
                    </div>
                </div>
            </div> -->

            <!-- Modal de confirmation pour l'abandon des études -->
            <ConfirmationModal
                :show="showModal"
                @close="showModal = false"
                @confirm="resignFromStudy"
            >
                <template #title>Abandonner les études en cours</template>
                <template #content
                    >Êtes-vous sûr de vouloir abandonner vos études actuelles
                    ?</template
                >
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
        </div>
    </AppLayout>
</template>
