<script setup>
import { ref, defineProps, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import JobDetailsModal from "@/Components/JobDetailsModal.vue";
import { Inertia } from "@inertiajs/inertia";
import { Link } from "@inertiajs/vue3";
import ConfirmationModal from "@/Components/ConfirmationModal.vue";

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

const userCanApply = (job) => {
    return props.userDiplomas.some((diploma) => diploma.id === job.diplomas_id);
};

const applyForJob = (jobId) => {
    Inertia.post(route("job.apply", jobId));
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
        <div class="container mx-auto p-4">
            <section class="space-y-2">
                <h2 class="text-2xl font-bold mb-4">Métiers disponibles</h2>
                <p>
                    Le choix de ton métier est plus qu'une décision, c'est le
                    début d'une aventure. Que tu te vois cultiver la terre en
                    tant qu'Agriculteur ou analyser des données en tant que
                    Juriste, chaque voie t'ouvre des portes uniques. Fais de ta
                    passion ton quotidien et trouve l'équilibre parfait entre
                    satisfaction personnelle et réussite professionnelle.
                </p>
                <p>
                    Chaque métier dans notre monde est une chance de montrer ce
                    dont tu es capable. Tu peux choisir de créer avec style et
                    audace en tant que Styliste, ou d'innover et de construire
                    en tant qu'Ingénieur. Prends les rênes de ton destin, plonge
                    dans le métier qui te fait vibrer et écris ton propre récit
                    de succès.
                </p>
            </section>
            <img
                src="/images/places/poleemploi.webp"
                alt="Workplace Image"
                class="size-64"
            />
            <div v-if="currentJob" class="mb-8">
                <h2 class="text-2xl font-bold mb-2">Job Actuel</h2>
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="text-lg font-semibold">{{ currentJob.name }}</h3>
                    <p class="text-sm text-gray-600">
                        {{ currentJob.description }}
                    </p>
                    <Link
                        :href="`/job/current/${currentJob.id}`"
                        class="mt-4 inline-flex items-center px-4 py-2 bg-blue-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition"
                        >Voir le job actuel</Link
                    >
                </div>
            </div>
            <div class="divide-y divide-gray-200">
                <div
                    v-for="job in jobs"
                    :key="job.id"
                    class="flex items-stretch py-4"
                >
                    <div
                        class="flex-none w-24 h-24 mr-4 bg-gray-100 rounded-lg overflow-hidden self-center"
                    >
                        <img
                            :src="job.img_job"
                            alt="Image du métier"
                            class="w-full h-full object-contain"
                        />
                    </div>
                    <div class="flex-grow self-center">
                        <h3 class="text-lg font-semibold">{{ job.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ job.description_1 }}
                        </p>
                        <div class="flex space-x-4 mt-2">
                            <p class="font-bold">Salaire: {{ job.salary }}€</p>
                        </div>
                    </div>
                    <div
                        class="flex-none self-center w-24 flex flex-col justify-start"
                    >
                        <button
                            v-if="isCurrentJob(job)"
                            disabled
                            class="mt-2 text-sm bg-gray-500 text-white font-bold py-2 px-4 rounded"
                        >
                            Job Actuel
                        </button>
                        <button
                            v-else-if="userCanApply(job)"
                            @click="requestApplyForJob(job.id)"
                            class="mt-2 text-sm bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                        >
                            Postuler
                        </button>

                        <span
                            v-else
                            class="mt-2 text-sm bg-red-500 text-white font-bold py-2 px-4 rounded"
                        >
                            Diplôme requis
                        </span>
                        <button
                            v-if="!isCurrentJob(job)"
                            @click="
                                selectedJob = job;
                                isModalOpen = true;
                            "
                            class="text-sm bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-2"
                        >
                            Voir plus
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <JobDetailsModal
            :job="selectedJob"
            :isOpen="isModalOpen"
            @close="isModalOpen = false"
        />
        <ConfirmationModal
            :show="isConfirmationModalOpen"
            @close="isConfirmationModalOpen = false"
            @confirm="confirmAndApplyForJob"
        >
            <template #title> Confirmation de postulation </template>
            <template #content>
                Êtes-vous sûr de vouloir postuler à ce job ? Cela remplacera
                votre job actuel si vous en avez un.
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
    </AppLayout>
</template>
