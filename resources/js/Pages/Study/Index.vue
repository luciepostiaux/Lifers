<script setup>
import { defineProps, reactive } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import { router } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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
        <div class="">
            <div
                class="flex flex-col lg:flex-row mb-4 w-full h-full gap-2 lg:gap-4"
            >
                <div
                    class="flex-1 flex flex-col justify-between lg:flex-auto lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 pb-0 rounded-lg shadow-md order-2 lg:order-1"
                >
                    <div
                        class="flex flex-col tracking-wide leading-relaxed gap-2"
                    >
                        <h1 class="text-xl font-bold">Lif'Université</h1>

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
                        class="bg-emerald-950 p-4 rounded-lg shadow-lg my-4"
                    >
                        <div
                            class="lg:grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 h-full"
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
                                    class="flex flex-col lg:flex-row justify-between gap-2 mb-2"
                                >
                                    <h2
                                        class="font-bold text-center lg:text-left"
                                    >
                                        {{ currentStudy.name }}
                                    </h2>

                                    <Link
                                        :href="`/study/current/${currentStudy.id}`"
                                        class="text-center"
                                    >
                                        <PrimaryButton>
                                            <h3 class="font-semibold">
                                                Suivi de l'étude
                                            </h3>
                                        </PrimaryButton>
                                    </Link>
                                </div>

                                <p
                                    class="text-sm pt-2 tracking-wide leading-relaxed"
                                >
                                    {{ currentStudy.description }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image institutionnelle sur le côté droit -->
                <div
                    class="flex-1 lg:flex-auto lg:w-2/5 rounded-lg shadow-md border-8 border-emerald-900 order-1 lg:order-2"
                >
                    <img
                        src="/images/places/university_4-6.webp"
                        alt="University Image"
                        class="object-cover h-full"
                    />
                </div>
            </div>

            <!-- Section Études choix -->
            <div
                class="bg-emerald-900/90 backdrop-blur-lg rounded-lg shadow-md p-4"
            >
                <h3 class="text-lg mb-4">Liste des études disponibles</h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4"
                >
                    <div
                        v-for="study in studies"
                        :key="study.id"
                        class="relative flex flex-col bg-emerald-950 backdrop-blur-lg hover:bg-emerald-950/50 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer py-4 min-h-80"
                    >
                        <div
                            @click="toggleDescription(study.id)"
                            class="flex flex-col justify-between p-4 flex-grow"
                        >
                            <div>
                                <div class="size-28 mx-auto mb-4">
                                    <img
                                        :src="study.img_study"
                                        alt="Image du métier"
                                        class="w-full h-full object-contain"
                                    />
                                </div>
                                <div class="flex-grow text-center">
                                    <h3 class="font-semibold text-sm">
                                        {{ study.name }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-col items-center text-sm">
                            <span
                                v-if="
                                    currentStudy && currentStudy.id === study.id
                                "
                            >
                                <button
                                    disabled
                                    class="mt-2 text-sm text-amber-500 font-bold py-2 px-4 rounded"
                                >
                                    Étude en cours
                                </button>
                            </span>
                            <span v-else-if="hasDiploma(study)">
                                <div
                                    class="mt-2 text-sm text-green-500 font-bold py-2 px-4 rounded"
                                >
                                    Diplôme déjà acquis
                                </div>
                            </span>
                            <span v-else>
                                <div v-if="userCanEnroll(study)">
                                    <PrimaryButton
                                        @click.stop="enrollForStudy(study.id)"
                                    >
                                        <h3 class="font-semibold">Postuler</h3>
                                    </PrimaryButton>
                                </div>
                                <div v-else>
                                    <div
                                        class="mt-2 text-sm text-red-500 font-bold py-2 px-4 rounded"
                                    >
                                        Diplôme requis
                                    </div>
                                </div>
                            </span>
                        </div>
                        <div class="flex justify-center gap-2 mt-2">
                            <div class="text-sm flex justify-center gap-1">
                                <p class="font-semibold">
                                    {{ study.price }}
                                </p>
                                <p class="">LC</p>
                            </div>
                        </div>

                        <div
                            v-if="showDescription[study.id]"
                            @click.stop="toggleDescription(study.id)"
                            class="absolute flex flex-col bg-emerald-900/90 backdrop-blur-lg hover:bg-emerald-900 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer py-4 min-h-80"
                        >
                            <div class="flex flex-col px-4 pt-4 flex-grow">
                                <h3 class="font-semibold text-center">
                                    {{ study.name }}
                                </h3>
                                <p
                                    class="pt-2 text-xs tracking-wide leading-relaxed"
                                >
                                    {{ study.description_1 }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center text-sm">
                                <span
                                    v-if="
                                        currentStudy &&
                                        currentStudy.id === study.id
                                    "
                                >
                                    <button
                                        disabled
                                        class="mt-2 text-sm text-amber-500 font-bold py-2 px-4 rounded"
                                    >
                                        Étude en cours
                                    </button>
                                </span>
                                <span v-else-if="hasDiploma(study)">
                                    <div
                                        class="mt-2 text-sm text-green-500 font-bold py-2 px-4 rounded"
                                    >
                                        Diplôme déjà acquis
                                    </div>
                                </span>
                                <span v-else>
                                    <div v-if="userCanEnroll(study)">
                                        <PrimaryButton
                                            @click.stop="
                                                enrollForStudy(study.id)
                                            "
                                        >
                                            <h3 class="font-semibold">
                                                Postuler
                                            </h3>
                                        </PrimaryButton>
                                    </div>
                                    <div v-else>
                                        <div
                                            class="mt-2 text-sm text-red-500 font-bold py-2 px-4 rounded"
                                        >
                                            Diplôme requis
                                        </div>
                                    </div>
                                </span>
                            </div>
                            <div class="flex justify-center gap-2 mt-2">
                                <div class="text-sm flex justify-center gap-1">
                                    <p class="font-semibold">
                                        {{ study.price }}
                                    </p>
                                    <p class="">LC</p>
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
