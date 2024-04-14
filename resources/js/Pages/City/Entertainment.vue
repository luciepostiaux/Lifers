<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3"; 

const props = defineProps({
    activitiesByCategory: Object,
});

const buyActivity = (activityId) => {
    router.post("/city/participate", {
        activityId,
    });
};
</script>

<template>
    <AppLayout title="Entertainment">
        <template #header></template>
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">
                Bienvenue au Monde de Divertissement
            </h2>
            <p class="mb-4">
                Plongez dans le monde des divertissements et enrichissez la vie
                de votre Lifer. Sélectionnez une activité parmi une multitude de
                lieux pour augmenter le bonheur et l'entertainment.
            </p>

            <h2 class="text-xl font-bold mb-4">Découvrez Nos Activités</h2>
            <div
                v-for="(activities, category) in activitiesByCategory"
                :key="category"
            >
                <h3 class="text-lg font-semibold mb-2">{{ category }}</h3>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <div
                        v-for="activity in activities"
                        :key="activity.id"
                        class="bg-white p-4 rounded-lg shadow-md flex flex-col"
                    >
                        <div class="flex-grow">
                            <h4 class="text-md font-semibold">
                                {{ activity.name }}
                            </h4>
                            <p class="text-gray-600 mt-1">
                                {{ activity.description }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="text-lg font-bold">
                                Prix: {{ activity.price }} Lifers' coins
                            </div>
                            <button
                                @click="buyActivity(activity.id)"
                                class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Participer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
