<script setup>
import { toRefs } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3"; 

const props = defineProps({
    currentSicknesses: Array,
    allSicknesses: Array,
});

const { currentSicknesses, allSicknesses } = toRefs(props);

const treatSickness = (sicknessId) => {
    router.post("/treat-sickness", { sicknessId });
};

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("fr-FR", options);
};

const visitDoctor = () => {
    router.post("/visit-doctor");
};
</script>

<template>
    <AppLayout title="Soin des maladies">
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">
                Centre de Traitement des Maladies
            </h2>
            <p class="mb-4">
                Prenez soin de votre personnage en traitant ses maladies.
                Sélectionnez une maladie et payez le traitement nécessaire pour
                améliorer sa santé.
            </p>
            <div class="mb-8">
                <button
                    @click="visitDoctor"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                    Visiter le Médecin (150€)
                </button>
            </div>
            <h2 class="text-xl font-bold mb-4">Maladies Actuelles</h2>
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8"
            >
                <div
                    v-for="sickness in currentSicknesses"
                    :key="sickness.id"
                    class="bg-white p-4 rounded-lg shadow-md flex flex-col"
                >
                    <div class="flex-grow">
                        <h4 class="text-md font-semibold">
                            {{ sickness.name }}
                        </h4>
                        <p class="text-gray-600 mt-1">
                            Contracté le:
                            {{ formatDate(sickness.pivot.created_at) }}
                        </p>
                        <p class="text-gray-600">
                            Coût du traitement: {{ sickness.treatment_cost }} €
                        </p>
                    </div>
                    <div class="mt-4">
                        <button
                            @click="treatSickness(sickness.id)"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Se soigner
                        </button>
                    </div>
                </div>
            </div>

            <!-- <h2 class="text-xl font-bold">Liste des Maladies Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="sickness in allSicknesses"
                    :key="sickness.id"
                    class="bg-white p-4 rounded-lg shadow-md flex flex-col"
                >
                    <div class="flex-grow">
                        <h4 class="text-md font-semibold">
                            {{ sickness.name }}
                        </h4>
                        <p class="text-gray-600 mt-1">
                            {{ sickness.description }}
                        </p>
                    </div>
                    <div class="mt-4">
                        <div class="text-lg font-bold">
                            Coût du traitement: {{ sickness.treatment_cost }} €
                        </div>
                    </div>
                </div>
            </div> -->
        </div>
    </AppLayout>
</template>
