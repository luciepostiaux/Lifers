<script setup>
import { defineProps, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

// Définir les props ici
const props = defineProps({
    perso: Object,
    bodyImageUrl: String,
    age: Number,
});
const formatDate = (dateString) => {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("fr-FR", options);
};
</script>
<template>
    <AppLayout title="AtHome">
        <div class="flex justify-center">
            <div class="container flex flex-col gap-4 mx-auto p-4 md:pt-24">
                <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
                    <img
                        v-if="bodyImageUrl"
                        :src="bodyImageUrl"
                        alt="Image du perso"
                        class="h-96 mx-auto"
                    />
                    <div class="mt-4">
                        <h1 v-if="perso" class="text-lg font-semibold">
                            {{ perso.first_name }} {{ perso.last_name }}
                        </h1>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
