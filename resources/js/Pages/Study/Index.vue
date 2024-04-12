<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    studies: Array,
    currentStudy: Object,
    studyDetails: Object,
    persoDiplomas: Array,
});

const enrollForStudy = (studyId) => {
    Inertia.post(route("study.enroll", { studyId }));
};

const hasDiploma = (study) => {
    // Assurez-vous que props.persoDiplomas est défini et est un tableau.
    if (
        props.persoDiplomas.some((diploma) => diploma.id === study.diplomas_id)
    ) {
        return true;
    }
};

const userCanEnroll = (study) => {
    const hasDiplomas = Array.isArray(props.persoDiplomas);
    if (
        !hasDiplomas ||
        props.persoDiplomas.some((diploma) => diploma.id === study.diplomas_id)
    ) {
        return false;
    }
    if (
        !study.diplomas_required_id ||
        props.persoDiplomas.some(
            (diploma) => diploma.id === study.diplomas_required_id
        )
    ) {
        return true;
    }
};
</script>
<template>
    <AppLayout title="Study">
        <template #header></template>
        <div class="container flex flex-col gap-4 mx-auto p-4 md:pt-24">
            <div class="flex bg-white p-4 rounded-lg shadow-md">
                <div class="space-y-2">
                    <h2 class="text-xl font-bold mb-4">Lif'Université</h2>

                    <p>
                        Explore nos divers programmes d'études, chaque cours est
                        une étape vers ta future carrière. Notre univers offre
                        des domaines variés et stimulants : biologie,
                        ingénierie, psychologie, art. Chaque discipline est une
                        porte ouverte sur un monde de possibilités.
                    </p>
                    <p>
                        Plonge dans un voyage éducatif captivant, où chaque
                        leçon te rapproche de ton but. Nos programmes te
                        préparent au monde professionnel, affûtent tes
                        compétences et élargissent ton horizon. Engage-toi dans
                        le parcours qui résonne avec tes passions et construis
                        l'avenir que tu désires dans notre monde plein
                        d'opportunités.
                    </p>
                </div>
                <img
                    src="/images/places/university.webp"
                    alt="University Image"
                    class="size-64"
                />
            </div>

            <!-- Section Études en cours -->
            <div v-if="currentStudy" class="">
                <div class="flex flex-col bg-white p-4 rounded-lg shadow-md">
                    <h2 class="text-xl font-bold mb-2">Étude active</h2>

                    <div class="flex">
                        <div class="flex-none w-16 h-16 mr-4 self-center">
                            <img
                                :src="currentStudy.img"
                                alt="Image du cours"
                                class="w-full h-full object-contain"
                            />
                        </div>

                        <div>
                            <h3 class="text-lg font-semibold">
                                {{ currentStudy.name }}
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ currentStudy.description }}
                            </p>
                        </div>
                        <div>
                            <Link
                                :href="`/study/current/${currentStudy.id}`"
                                class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                                >Accéder à l'étude</Link
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Études choix -->
            <div class="flex bg-white p-4 rounded-lg shadow-md">
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="study in studies"
                        :key="study.id"
                        class="flex items-stretch py-4"
                    >
                        <div class="flex-none w-16 h-16 mr-4 self-center">
                            <img
                                :src="study.img_study"
                                alt="Image du cours"
                                class="w-full h-full object-contain"
                            />
                        </div>

                        <div class="flex-grow self-center">
                            <h3 class="text-lg font-semibold">
                                {{ study.name }}
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ study.description_1 }}
                            </p>
                            <div class="flex space-x-4 mt-2">
                                <p class="font-bold">
                                    Prix: {{ study.price }}€
                                </p>
                                <p class="">
                                    Durée: {{ study.duration }} jours
                                </p>
                            </div>
                        </div>

                        <div
                            class="flex-none self-center w-24 flex flex-col justify-start"
                        >
                            <button
                                v-if="
                                    currentStudy && currentStudy.id === study.id
                                "
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
                                v-else-if="hasDiploma(study)"
                                class="text-sm bg-gray-500 text-white font-bold py-2 px-4 rounded"
                                >Diplôme déjà acquis</span
                            >

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
        </div>
    </AppLayout>
</template>
