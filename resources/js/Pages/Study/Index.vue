<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    studies: Array,
});
</script>
<template>
    <AppLayout title="Study">
        <template #header></template>
        <div class="container mx-auto p-4">
            <div class="flex">
                <div class="space-y-2">
                    <h2 class="text-2xl font-bold mb-4">Cours disponibles</h2>

                    <p>
                        Lance-toi dans l'exploration de nos divers programmes
                        d'études, où chaque cours est une étape vers ta future
                        carrière. Notre univers t'offre des domaines aussi
                        variés que stimulants. Que tu sois attiré par les
                        mystères de la biologie, la précision de l'ingénierie,
                        la profondeur de la psychologie, ou l'expression de
                        l'art, chaque discipline est une porte ouverte sur un
                        monde de possibilités.
                    </p>
                    <p>
                        Plonge dans un voyage éducatif riche et captivant, où
                        chaque leçon te rapproche de ton but. Nos programmes
                        sont conçus pour te préparer au monde professionnel,
                        affûtant tes compétences et élargissant ton horizon.
                        Chaque étude que tu choisis est un pas de plus vers ta
                        réalisation personnelle et professionnelle. Embrasse ton
                        ambition, engage-toi dans le parcours qui résonne avec
                        tes passions et construis l'avenir que tu désires dans
                        notre monde plein d'opportunités et de découvertes.
                    </p>
                </div>
                <img
                    src="/images/places/university.webp"
                    alt="University Image"
                    class="size-64"
                />
            </div>

            <div class="divide-y divide-gray-200">
                <div
                    v-for="study in studies"
                    :key="study.id"
                    class="flex items-stretch py-4"
                >
                    <div
                        class="flex-none w-24 h-24 mr-4 bg-gray-100 rounded-lg overflow-hidden self-center"
                    >
                        <img
                            :src="study.img_study"
                            alt="Image du cours"
                            class="w-full h-full object-contain"
                        />
                    </div>

                    <div class="flex-grow self-center">
                        <h3 class="text-lg font-semibold">{{ study.name }}</h3>
                        <p class="text-sm text-gray-600 mt-1">
                            {{ study.description_1 }}
                        </p>
                        <div class="flex space-x-4 mt-2">
                            <p class="font-bold">Prix: {{ study.price }}€</p>
                            <p class="">Durée: {{ study.duration }} jours</p>
                        </div>
                    </div>

                    <div
                        class="flex-none self-center w-24 flex flex-col justify-start"
                    >
                        <Link
                            :href="`/study/${study.id}`"
                            class="text-sm bg-[#9EE5F5] hover:text-white rounded px-4 py-2 hover:bg-[#71A4B0] transition-all mb-2"
                        >
                            Voir plus
                        </Link>
                        <button
                            class="text-sm bg-green-500 hover:text-white rounded px-4 py-2 hover:bg-green-700 transition-all"
                        >
                            Postuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
