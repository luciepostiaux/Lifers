<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    studies: Array,
    currentStudy: Object,
    studyDetails: Object,
    userDiplomas: Array,
});

const enrollForStudy = (studyId) => {
    // Vous pouvez ajouter ici la logique pour vérifier si le personnage a déjà ce diplôme ou si le diplôme requis est présent
    Inertia.post(route("study.enroll", { studyId }));
};
const userCanEnroll = (study) => {
    // Assurez-vous que props.userDiplomas est défini et est un tableau.
    const hasDiplomas = Array.isArray(props.userDiplomas);

    // Si l'étude ne requiert pas de diplôme, alors l'utilisateur peut s'inscrire.
    if (!study.diplomas_required_id) {
        return true;
    }

    // Si l'utilisateur a des diplômes, vérifier si l'un d'eux correspond au diplôme requis par l'étude.
    const hasRequiredDiploma =
        hasDiplomas &&
        props.userDiplomas.some(
            (diploma) => diploma.id === study.diplomas_required_id
        );

    // Vérifie également si l'utilisateur est actuellement inscrit à cette étude.
    const isCurrentlyEnrolled =
        props.currentStudy && props.currentStudy.id === study.id;

    // L'utilisateur peut s'inscrire s'il a le diplôme requis et n'est pas déjà inscrit.
    return hasRequiredDiploma && !isCurrentlyEnrolled;
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

                <!-- <p
                                class="text-sm"
                                v-if="studyDetails && studyDetails.end_date"
                            >
                                Fin prévue : {{ studyDetails.end_date }}
                                <span v-if="daysRemaining"
                                    >{{ daysRemaining }} jours restants.</span
                                >
                            </p> -->
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
                        <Link
                            :href="`/study/${study.id}`"
                            class="text-sm bg-[#9EE5F5] hover:text-white rounded px-4 py-2 hover:bg-[#71A4B0] transition-all mb-2"
                        >
                            Voir plus
                        </Link>
                        <button
                            v-if="currentStudy && currentStudy.id === study.id"
                            disabled
                            class="text-sm bg-gray-500 text-white font-bold py-2 px-4 rounded"
                        >
                            Étude en cours
                        </button>
                        <button
                            v-else-if="userCanEnroll(study)"
                            @click="enrollForStudy(study.id)"
                            class="text-sm bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Postuler
                        </button>
                        <span
                            v-else
                            class="text-sm bg-red-500 text-white font-bold py-2 px-4 rounded"
                        >
                            Diplôme requis
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
