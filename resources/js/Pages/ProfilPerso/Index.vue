<script setup>
import { ref, onMounted } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";
import { router } from "@inertiajs/vue3"; 
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    perso: Object,
    age: Number,
    money: String,
    bodyImageUrl: String,
    studyDetails: Object,
});
</script>

<template>
    <AppLayout title="Profil">
        <template #header></template>

        <div class="container flex flex-col gap-4 mx-auto p-4 md:pt-24">
            <div class="md:grid md:grid-cols-3 md:gap-4">
                <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
                    <img
                        v-if="bodyImageUrl"
                        :src="bodyImageUrl"
                        alt="Image du perso"
                        class="h-96 mx-auto"
                    />

                    <div class="mt-4">
                        <h1 v-if="perso" class="text-lg font-semibold">
                            {{ perso.first_name }}
                            {{ perso.last_name }}
                        </h1>
                        <div class="flex text-sm">
                            <p class="font-semibold">{{ age }} ans</p>
                        </div>
                        <div class="flex text-sm">
                            <p class="font-semibold">{{ money }}</p>
                            <p class="">Lif'coins</p>
                        </div>
                    </div>
                </div>
                <div
                    class="col-span-2 bg-white p-4 rounded-lg shadow-md mb-4 md:mb-0"
                >
                    <div class="text-center mt-4">
                        <p>Barre d'actions</p>
                        <p>Liste d'amis</p>
                        <p>Famille + btn arbre</p>
                        <p>Animaux</p>
                    </div>
                </div>
            </div>
            <div class="md:gap-4">
                <div
                    class="bg-white h-64 p-4 rounded-lg shadow-md mb-4 md:mb-0"
                >
                    <p>Custom de la description du profil</p>
                </div>
            </div>

            <div class="md:gap-4">
                <div
                    class="bg-white min-h-96 p-4 rounded-lg shadow-md mb-4 md:mb-0"
                >
                    <p>Zone de commentaires</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
