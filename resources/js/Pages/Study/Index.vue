<script setup>
import { defineProps, reactive } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    studies: Array,
    currentStudy: Object,
    studyDetails: Object,
    persoDiplomas: Array,
});

const showDescription = reactive({});

const toggleDescription = (studyId) => {
    showDescription[studyId] = !showDescription[studyId];
};

const enrollForStudy = (studyId) => {
    router.post(route("study.enroll", { studyId }));
};

const hasDiploma = (study) =>
    props.persoDiplomas?.some((diploma) => diploma.id === study.diplomas_id);

const userCanEnroll = (study) => {
    const requiredDiploma = study.diplomas_required_id;
    if (!requiredDiploma) {
        return true;
    }

    return props.persoDiplomas?.some(
        (diploma) => diploma.id === requiredDiploma
    );
};
</script>

<template>
    <AppLayout title="Study">
        <template #header></template>
        <div class="md:pt-24">
            <div class="flex flex-col md:flex-row mb-4 w-full h-full gap-4">
                <div
                    class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-white p-4 rounded-lg shadow-md"
                >
                    <div class="flex flex-col">
                        <h2 class="text-3xl font-bold mb-4">Lif'Université</h2>
                        <p>
                            Explore nos divers programmes d'études conçus pour
                            préparer au monde professionnel dans des domaines
                            variés et stimulants comme la biologie,
                            l'ingénierie, et la psychologie. Engage-toi dans un
                            parcours qui résonne avec tes passions et construis
                            l'avenir que tu désires.
                        </p>
                    </div>
                    <!-- Card des études en cours intégrée si une étude est en cours -->
                    <div
                        v-if="currentStudy"
                        class="bg-gray-500 p-4 rounded-lg shadow-lg text-white"
                    >
                        <div
                            class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 h-full"
                        >
                            <div
                                class="flex flex-col col-span-1 rounded-lg place-self-center"
                            >
                                <img
                                    :src="currentStudy.img"
                                    alt="Image du cours"
                                    class="w-full max-h-24 object-contain"
                                />
                            </div>
                            <div class="flex flex-col col-span-4">
                                <div
                                    class="flex flex-col md:flex-row justify-between gap-2 mb-2"
                                >
                                    <h2 class="font-bold">
                                        {{ currentStudy.name }}
                                    </h2>
                                    <Link
                                        :href="`/study/current/${currentStudy.id}`"
                                        class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                                        >Aller</Link
                                    >
                                </div>

                                <p class="text-xs font-semibold pt-2">
                                    {{ currentStudy.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image institutionnelle sur le côté droit -->
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

            <!-- Section Études choix -->
            <div
                class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
            >
                <div
                    v-for="study in studies"
                    :key="study.id"
                    class="relative flex flex-col bg-white hover:bg-gray-300 rounded-lg shadow-md transition-all duration-300 ease-in-out hover:scale-105 cursor-pointer py-4 h-72"
                >
                    <div
                        @click="toggleDescription(study.id)"
                        class="flex flex-col justify-between p-4 flex-grow"
                    >
                        <div v-if="!showDescription[study.id]">
                            <div class="size-28 mx-auto mb-4">
                                <img
                                    :src="study.img_study"
                                    alt="Image du cours"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                            <div class="flex-grow text-center">
                                <h3 class="font-semibold text-sm">
                                    {{ study.name }}
                                </h3>
                            </div>
                        </div>
                        <div v-else>
                            <h3 class="font-semibold text-center">
                                {{ study.name }}
                            </h3>
                            <p class="pt-2 text-gray-600 text-xs font-semibold">
                                {{ study.description_1 }}
                            </p>
                        </div>

                        <div class="flex flex-col justify-center mt-2">
                            <div class="flex flex-col items-center text-sm">
                                <span
                                    v-if="
                                        currentStudy &&
                                        currentStudy.id === study.id
                                    "
                                >
                                    <button
                                        disabled
                                        class="bg-gray-500 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Étude en cours
                                    </button>
                                </span>
                                <span v-else-if="hasDiploma(study)">
                                    <div
                                        class="bg-gray-400 text-white font-bold py-2 px-4 rounded"
                                    >
                                        Diplôme acquis
                                    </div>
                                </span>
                                <span v-else>
                                    <div v-if="userCanEnroll(study)">
                                        <button
                                            @click.stop="
                                                enrollForStudy(study.id)
                                            "
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Postuler
                                        </button>
                                    </div>
                                    <div v-else>
                                        <div
                                            class="bg-red-500 text-white font-bold py-2 px-4 rounded"
                                        >
                                            Diplôme requis
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex justify-center gap-2 mt-2 italic">
                                <div class="text-sm flex justify-center gap-1">
                                    <p class="font-semibold">
                                        {{ study.price }}
                                    </p>
                                    <p class="">LC</p>
                                </div>
                                <div class="text-sm flex justify-center gap-1">
                                    <p class="font-semibold">
                                        {{ study.duration }}
                                    </p>
                                    <p class="">jours</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
<style scoped>
.relative {
    position: relative;
}
.absolute {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
}
</style>
