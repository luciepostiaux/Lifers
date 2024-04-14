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
        <div class="container flex flex-col gap-4 mx-auto p-4 md:pt-24">
            <div class="flex gap-4">
                <div class="flex bg-white p-4 rounded-lg shadow-md">
                    <div class="space-y-2 flex-grow">
                        <h2 class="text-xl font-bold mb-4">Lif'Université</h2>
                        <p>
                            Explore nos divers programmes d'études conçus pour
                            préparer au monde professionnel dans des domaines
                            variés et stimulants comme la biologie,
                            l'ingénierie, et la psychologie. Engage-toi dans un
                            parcours qui résonne avec tes passions et construis
                            l'avenir que tu désires.
                        </p>
                    </div>
                </div>
                <img
                    src="/images/places/university.webp"
                    alt="University Image"
                    class="rounded-lg shadow-lg max-w-xs"
                />
            </div>

            <!-- Section Études en cours -->
            <div v-if="currentStudy" class="my-4">
                <div
                    class="flex flex-col bg-white p-4 rounded-lg shadow-lg hover:shadow-xl transition-shadow duration-300"
                >
                    <div class="flex flex-col md:flex-row items-center gap-8">
                        <div class="md:w-1/4 size-36 mx-auto">
                            <img
                                :src="currentStudy.img"
                                alt="Image du cours"
                                class="w-full h-full object-contain"
                            />
                        </div>

                        <div class="md:w-3/4 flex flex-col flex-grow">
                            <div class="flex justify-between pb-6">
                                <h2 class="text-xl font-bold mb-1">
                                    {{ currentStudy.name }}
                                </h2>
                                <Link
                                    :href="`/study/current/${currentStudy.id}`"
                                    class="inline-flex items-center justify-center px-4 py-2 bg-gray-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300 disabled:opacity-25 transition"
                                    >Accéder à l'étude</Link
                                >
                            </div>
                            <p class="text-sm text-gray-600">
                                {{ currentStudy.description }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Études choix -->
            <div
                class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 items-stretch"
            >
                <div
                    v-for="study in studies"
                    :key="study.id"
                    class="relative flex flex-col bg-white rounded-lg shadow-md transition-transform duration-300 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer flex-1"
                >
                    <div
                        @click="toggleDescription(study.id)"
                        class="flex flex-col justify-between p-4 flex-grow"
                    >
                        <div>
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
                        <div>
                            <div
                                class="mt-2 flex flex-col items-center text-sm"
                            >
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
                            <div class="flex justify-around mt-2 italic">
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
                    <div
                        v-if="showDescription[study.id]"
                        @click.stop="toggleDescription(study.id)"
                        class="absolute justify-between inset-0 bg-white p-4 rounded-lg shadow-md flex flex-col transition-transform duration-300 ease-in-out cursor-pointer"
                    >
                        <p class="text-gray-600 text-sm">
                            {{ study.description_1 }}
                        </p>
                        <div class="flex justify-around mt-2 italic">
                            <div class="text-sm flex justify-center gap-1">
                                <p class="font-semibold">{{ study.price }}</p>
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
