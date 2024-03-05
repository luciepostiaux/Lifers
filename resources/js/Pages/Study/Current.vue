<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    studyDetails: Object,
    associatedStudies: Array,
});
</script>

<template>
    <AppLayout title="Suivi des études">
        <div class="container mx-auto p-4">
            <div class="flex flex-col md:flex-row">
                <div class="md:w-3/4 space-y-2">
                    <h2 class="text-2xl font-bold mb-4">
                        Étude en cours : {{ studyDetails.name }}
                    </h2>
                    <p>{{ studyDetails.description }}</p>
                </div>
                <img
                    :src="studyDetails.img_study"
                    alt="Image de l'étude"
                    class="md:w-1/4 h-64 object-cover md:object-contain md:ml-4"
                />
            </div>

            <div class="my-6">
                <h3 class="text-lg font-semibold">Détails de l'étude</h3>
                <p class="text-sm" v-if="studyDetails.end_date">
                    Fin prévue : {{ studyDetails.end_date }}
                </p>
            </div>

            <div v-if="associatedStudies">
                <h3 class="text-lg font-semibold mb-4">Études associées</h3>
                <div class="divide-y divide-gray-200">
                    <div
                        v-for="study in associatedStudies"
                        :key="study.id"
                        class="py-4"
                    >
                        <div class="flex items-center justify-between">
                            <h4 class="text-md font-semibold">
                                {{ study.name }}
                            </h4>
                            <Link
                                :href="`/study/${study.id}`"
                                class="text-sm bg-[#9EE5F5] hover:text-white rounded px-4 py-2 hover:bg-[#71A4B0] transition-all"
                                >Explorer</Link
                            >
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ study.description }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
