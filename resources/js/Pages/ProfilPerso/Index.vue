<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { defineProps, ref } from "vue";
import { Inertia } from "@inertiajs/inertia";
import Description from "@/Components/Description.vue";

const props = defineProps({
    perso: Object,
    age: Number,
    bodyImageUrl: String,
    studyDetails: Object,
});
</script>
<template>
    <AppLayout title="Profil">
        <template #header></template>
        <div v-if="$page.props.flash.success" class="alert alert-success">
            {{ $page.props.flash.success }}
        </div>
        <div class="container mx-auto p-4">
            <div class="md:grid md:grid-cols-3 md:gap-4">
                <div class="bg-white p-4 rounded-lg shadow-md mb-4 md:mb-0">
                    <div class="text-center mt-4">
                        <h2>{{ perso.first_name }} {{ perso.last_name }}</h2>
                        <img
                            v-if="bodyImageUrl"
                            :src="bodyImageUrl"
                            alt="Image du perso"
                            class="h-96 mx-auto"
                        />
                        <p>Âge : {{ age }}</p>
                        <p>Lif'coins : {{ perso.money }}</p>
                        <div
                            v-if="studyDetails"
                            class="flex flex-col space-y-4"
                        >
                            <p class="text-sm">{{ studyDetails.name }}</p>
                        </div>
                        <p>
                            Job actuel :
                            {{ perso.job ? perso.job.name : "Aucun" }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="">
                <div id="app" class="flex flex-col">
                    <Description />
                </div>
            </div>

            <!-- Espace commentaires -->
            <!-- <div class="commentaires">
                <h3>Commentaires</h3>
            </div> -->
        </div>
    </AppLayout>
</template>
