<script setup>
import { ref, defineProps, computed, reactive } from "vue";
import { router, Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    jobs: Array,
    userDiplomas: Array,
    currentJob: Object,
    isOpen: Boolean,
});
const selectedJob = ref(null);
const isModalOpen = ref(false);
const isConfirmationModalOpen = ref(false);
const jobToApplyFor = ref(null);
const showDescription = reactive({});

const toggleDescription = (jobId) => {
    showDescription[jobId] = !showDescription[jobId];
};

const userCanApply = (job) => {
    return (
        !job.diplomas_id ||
        props.userDiplomas.some((diploma) => diploma.id === job.diplomas_id)
    );
};

const applyForJob = (jobId) => {
    router.post(route("job.apply", jobId)); // Utilisation de router.post
};
const requestApplyForJob = (jobId) => {
    jobToApplyFor.value = jobId; // Stocker l'ID du job
    isConfirmationModalOpen.value = true; // Ouvrir le modal de confirmation
};

const confirmAndApplyForJob = () => {
    if (jobToApplyFor.value) {
        applyForJob(jobToApplyFor.value);
        isConfirmationModalOpen.value = false; // Fermez le modal après confirmation
    }
};
const isCurrentJob = (job) => {
    return props.currentJob && props.currentJob.id === job.id;
};
</script>

<template>
    <AppLayout title="Jobs">
        <template #header></template>

        <div class="">
            <div
                class="flex flex-col lg:flex-row mb-4 w-full h-full gap-2 lg:gap-4"
            >
                <div
                    class="flex-1 flex flex-col justify-between lg:flex-auto lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 pb-0 rounded-lg shadow-md order-2 lg:order-1"
                >
                    <div
                        class="flex flex-col tracking-wide leading-relaxed gap-2 pb-4"
                    >
                        <h1 class="text-xl font-bold">Lif'Emploi</h1>
                        <p>
                            Le choix de ton métier est plus qu'une décision,
                            c'est le début d'une aventure. Que tu te vois
                            cultiver la terre en tant qu'Agriculteur ou analyser
                            des données en tant que Juriste, chaque voie t'ouvre
                            des portes uniques. Fais de ta passion ton quotidien
                            et trouve l'équilibre parfait entre satisfaction
                            personnelle et réussite professionnelle.
                        </p>
                    </div>
                    <div
                        v-if="currentJob"
                        class="bg-emerald-950 p-4 rounded-lg shadow-lg my-4"
                    >
                        <div
                            class="lg:grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 h-full"
                        >
                            <div
                                class="flex flex-col col-span-1 rounded-lg place-self-center"
                            >
                                <img
                                    :src="currentJob.img_job"
                                    alt="Image du job"
                                    class="w-full max-h-24 object-contain"
                                />
                            </div>
                            <div class="flex flex-col col-span-4">
                                <div
                                    class="flex flex-col lg:flex-row justify-between gap-2 mb-2"
                                >
                                    <h2 class="font-bold">
                                        {{ currentJob.name }}
                                    </h2>

                                    <Link
                                        :href="`/job/current/${currentJob.id}`"
                                    >
                                        <PrimaryButton>
                                            <h3 class="font-semibold">
                                                Suivi du métier
                                            </h3>
                                        </PrimaryButton>
                                    </Link>
                                </div>

                                <p
                                    class="text-sm pt-2 tracking-wide leading-relaxed"
                                >
                                    {{ currentJob.description_1 }}
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
                        src="/images/places/poleemploi_4-6.webp"
                        alt="Workplace Image"
                        class="object-cover h-full"
                    />
                </div>
            </div>

            <!-- Section job choix -->

            <div
                class="bg-emerald-900/90 backdrop-blur-lg rounded-lg shadow-md p-4"
            >
                <h3 class="text-lg mb-4">Liste des métiers disponibles</h3>
                <div
                    class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4"
                >
                    <div
                        v-for="job in jobs"
                        :key="job.id"
                        class="relative flex flex-col bg-emerald-950 backdrop-blur-lg hover:bg-emerald-950/50 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer py-4 min-h-80"
                    >
                        <div
                            @click="toggleDescription(job.id)"
                            class="flex flex-col justify-between p-4 flex-grow"
                        >
                            <div>
                                <div class="size-28 mx-auto mb-4">
                                    <img
                                        :src="job.img_job"
                                        alt="Image du métier"
                                        class="w-full h-full object-contain"
                                    />
                                </div>
                                <div class="flex-grow text-center">
                                    <h3 class="font-semibold text-sm">
                                        {{ job.name }}
                                    </h3>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-col items-center text-sm">
                            <button
                                v-if="isCurrentJob(job)"
                                disabled
                                class="mt-2 text-sm text-amber-500 font-bold py-2 px-4 rounded"
                            >
                                Travail Actuel
                            </button>

                            <PrimaryButton
                                v-else-if="userCanApply(job)"
                                @click="requestApplyForJob(job.id)"
                            >
                                <h3 class="font-semibold">Postuler</h3>
                            </PrimaryButton>
                            <span
                                v-else
                                class="mt-2 text-sm text-red-500 font-bold py-2 px-4 rounded"
                            >
                                Diplôme requis
                            </span>
                        </div>
                        <div class="flex justify-center gap-2 mt-2">
                            <div class="text-sm flex justify-center gap-1">
                                <p class="font-semibold">
                                    Salaire: {{ job.salary }}
                                </p>
                                <p class="">LC</p>
                            </div>
                        </div>

                        <div
                            v-if="showDescription[job.id]"
                            @click.stop="toggleDescription(job.id)"
                            class="absolute flex flex-col bg-emerald-900/90 backdrop-blur-lg hover:bg-emerald-900 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer py-4 min-h-80"
                        >
                            <div class="flex flex-col px-4 pt-4 flex-grow">
                                <h3 class="font-semibold text-center">
                                    {{ job.name }}
                                </h3>
                                <p
                                    class="pt-2 text-xs tracking-wide leading-relaxed"
                                >
                                    {{ job.description_1 }}
                                </p>
                            </div>
                            <div class="flex flex-col items-center text-sm">
                                <button
                                    v-if="isCurrentJob(job)"
                                    disabled
                                    class="mt-2 text-sm text-amber-500 font-bold py-2 px-4 rounded"
                                >
                                    Travail Actuel
                                </button>
                                <PrimaryButton
                                    v-else-if="userCanApply(job)"
                                    @click="requestApplyForJob(job.id)"
                                >
                                    <h3 class="font-semibold">Postuler</h3>
                                </PrimaryButton>

                                <span
                                    v-else
                                    class="mt-2 text-sm text-red-500 font-bold py-2 px-4 rounded"
                                >
                                    Diplôme requis
                                </span>
                            </div>
                            <div class="flex justify-center gap-2 mt-2">
                                <div class="text-sm flex justify-center gap-1">
                                    <p class="font-semibold">
                                        Salaire: {{ job.salary }}
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
    <ConfirmationModal
        :show="isConfirmationModalOpen"
        @close="isConfirmationModalOpen = false"
        @confirm="confirmAndApplyForJob"
    >
        <template #title> Confirmation de postulation </template>
        <template #content>
            Êtes-vous sûr de vouloir postuler à ce job ? Cela remplacera votre
            job actuel si vous en avez un.
        </template>
        <template #footer>
            <button
                @click="isConfirmationModalOpen = false"
                class="px-4 py-2 bg-gray-200 text-black rounded"
            >
                Annuler
            </button>
            <button
                @click="confirmAndApplyForJob"
                class="px-4 py-2 bg-red-600 text-white rounded"
            >
                Confirmer et postuler
            </button>
        </template>
    </ConfirmationModal>
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
