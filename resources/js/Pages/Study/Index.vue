<script setup>
import { ref, defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

const props = defineProps({
    studies: Array,
    userDiplomas: Array,
    currentStudy: Object,
});
const selectedStudy = ref(null);
const isConfirmationModalOpen = ref(false);
const studyToEnroll = ref(null);

const userCanEnroll = (study) => {
    return (
        props.userDiplomas &&
        props.userDiplomas.some(
            (diploma) => diploma.id === study.diplomas_required_id
        )
    );
};

const requestEnrollForStudy = (studyId) => {
    studyToEnroll.value = studyId;
    isConfirmationModalOpen.value = true;
};

const confirmAndEnrollForStudy = () => {
    if (studyToEnroll.value) {
        Inertia.post(route("study.enroll", { studyId: studyToEnroll.value }));
        isConfirmationModalOpen.value = false;
    }
};
</script>
<template>
    <AppLayout title="Study">
        <template #header></template>
        <div class="container mx-auto p-4">
            <div class="flex">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold mb-4">Cours disponibles</h2>

                    <p>
                        Lance-toi dans l'exploration de nos divers programmes
                        d'études, où chaque cours est une étape vers ta future
                        carrière. Notre univers t'offre des domaines aussi
                        variés que stimulants. Que tu sois attiré par les
                        mystères de la biologie, la précision de l'ingénierie,
                        la profondeur de la psychologie, ou l'expression de
                        l'art, chaque discipline est une porte ouverte sur un
                        monde de possibilités.
                    </p>
                    <p>
                        Plonge dans un voyage éducatif riche et captivant, où
                        chaque leçon te rapproche de ton but. Nos programmes
                        sont conçus pour te préparer au monde professionnel,
                        affûtant tes compétences et élargissant ton horizon.
                        Chaque étude que tu choisis est un pas de plus vers ta
                        réalisation personnelle et professionnelle. Embrasse ton
                        ambition, engage-toi dans le parcours qui résonne avec
                        tes passions et construis l'avenir que tu désires dans
                        notre monde plein d'opportunités et de découvertes.
                    </p>
                </div>
                <img
                    src="/images/places/university.webp"
                    alt="University Image"
                    class="size-64"
                />
            </div>
            <!-- Section Études en cours -->
            <div v-if="currentStudy" class="mb-8">
                <h2 class="text-2xl font-bold mb-2">Étude en cours</h2>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold">
                        {{ currentStudy.name }}
                    </h3>
                    <p class="text-sm text-gray-600">
                        {{ currentStudy.description }}
                    </p>
                    <Link
                        :href="`/study/current/${currentStudy.id}`"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                        >Etude en cours</Link
                    >
                </div>
            </div>

            <div v-if="studyDetails" class="flex flex-col space-y-4">
                <p class="text-sm">{{ studyDetails.name }}</p>

                <p class="text-sm" v-if="studyDetails.end_date">
                    Fin prévue : {{ studyDetails.end_date }}
                </p>

                <Link
                    :href="route('study')"
                    class="px-4 py-2 text-sm bg-[#9EE5F5] hover:text-white rounded hover:bg-[#71A4B0] transition-all"
                    >Aller aux études</Link
                >
            </div>

            <div class="divide-y divide-gray-200">
                <div
                    v-for="study in studies"
                    :key="study.id"
                    class="flex items-stretch py-4"
                >
                    <div
                        class="flex-none w-24 h-24 mr-4 bg-gray-100 rounded-lg overflow-hidden self-center"
                    >
                        <img
                            :src="study.img_study"
                            alt="Image du cours"
                            class="w-full h-full object-contain"
                        />
                    </div>
                    <div class="flex-grow self-center">
                        <h3 class="text-lg font-semibold">{{ study.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ study.description_1 }}
                        </p>
                        <div class="flex space-x-4 mt-2">
                            <p class="font-bold">Prix: {{ study.price }}€</p>
                            <p class="">Durée: {{ study.duration }} jours</p>
                        </div>
                    </div>
                    <div
                        class="flex-none self-center w-24 flex flex-col justify-start"
                    >
                        <!-- Bouton pour voir plus d'informations sur l'étude -->
                        <Link
                            :href="`/study/${study.id}`"
                            class="text-sm bg-[#9EE5F5] hover:text-white rounded px-4 py-2 hover:bg-[#71A4B0] transition-all mb-2"
                            >Voir plus</Link
                        >
                        <!-- Bouton Postuler ajouté ici -->
                        <button
                            v-if="userCanEnroll(study)"
                            @click="requestEnrollForStudy(study.id)"
                            class="text-sm bg-green-500 hover:text-white rounded px-4 py-2 hover:bg-green-700 transition-all"
                        >
                            Postuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <ConfirmationModal
            :show="isConfirmationModalOpen"
            @close="isConfirmationModalOpen = false"
            @confirm="confirmAndEnrollForStudy"
        >
            <template #title>Confirmation d'inscription</template>
            <template #content
                >Êtes-vous sûr de vouloir vous inscrire à cette étude ? Cela
                remplacera votre étude actuelle si vous en avez une.</template
            >
            <template #footer>
                <button
                    @click="isConfirmationModalOpen = false"
                    class="px-4 py-2 bg-gray-200 text-black rounded"
                >
                    Annuler
                </button>
                <button
                    @click="confirmAndEnrollForStudy"
                    class="px-4 py-2 bg-red-600 text-white rounded"
                >
                    Confirmer et s'inscrire
                </button>
            </template>
        </ConfirmationModal>
    </AppLayout>
</template>
