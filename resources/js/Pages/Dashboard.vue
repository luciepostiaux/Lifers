<script setup>
import { defineProps, computed } from "vue";

import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";

defineProps({
    perso: Object,
    bodyImageUrl: String,
    money: String,
    age: Number,
    lifeGauges: Object,
    studyDetails: Object,
    currentSicknesses: Array,
    activeResidence: Object,
});

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-800";
    else if (value <= 50) return "bg-orange-400";
    return "bg-lime-700";
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header></template>
        <div class="flex flex-col gap-4 md:flex-row mb-4 w-full h-full">
            <div
                class="md:w-1/3 flex flex-col justify-between bg-emerald-900/90 p-4 rounded-lg shadow-md md:mb-0 backdrop-blur-md"
            >
                <h1 class="text-xl font-bold mb-4">Accueil</h1>

                <div class="gap-4 h-full">
                    <div class="flex pb-4 justify-between">
                        <div class="w-1/2 flex items-end">
                            <img
                                v-if="bodyImageUrl"
                                :src="bodyImageUrl"
                                alt="Image du perso"
                                class="h-96 mx-auto"
                            />
                        </div>
                        <div
                            class="flex flex-col w-1/2 justify-between items-center"
                        >
                            <div class="w-full">
                                <div
                                    v-if="activeResidence"
                                    class="relative group"
                                >
                                    <div class="relative w-full md:h-64">
                                        <img
                                            :src="activeResidence.image_path"
                                            alt="Résidence Active"
                                            class="absolute inset-0 w-full h-full object-cover rounded-lg transition-opacity duration-300 ease-in-out group-hover:opacity-75"
                                        />

                                        <div
                                            class="absolute inset-0 bg-emerald-950 bg-opacity-70 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 ease-in-out rounded-lg"
                                        >
                                            <div>
                                                <h2
                                                    class="text-white font-semibold text-center"
                                                >
                                                    Résidence Active
                                                </h2>
                                                <p
                                                    class="text-white text-xl font-bold text-center"
                                                >
                                                    {{ activeResidence.type }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div
                                    v-else
                                    class="bg-gray-300/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer p-4"
                                >
                                    <h2 class="text-xl font-bold mb-4">
                                        Aucune résidence active
                                    </h2>
                                    <p>
                                        Vous n'avez pas de résidence active en
                                        ce moment.
                                    </p>
                                </div>
                            </div>
                            <div class="text-gray-200">
                                <h2 v-if="perso" class="text-xl font-bold">
                                    {{ perso.first_name }}
                                    {{ perso.last_name }}
                                </h2>
                                <div class="flex flex-col text-sm">
                                    <div class="flex gap-1">
                                        <p class="font-semibold">
                                            {{ age }}
                                        </p>
                                        <p class="">ans</p>
                                    </div>
                                    <div class="flex gap-1">
                                        <p class="font-semibold">
                                            {{ money }}
                                        </p>
                                        <p class="">LC</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="md:w-1/3 flex flex-col justify-between bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md mb-4 md:mb-0"
            >
                <h2 class="text-lg font-semibold mb-4">Jauges de vie</h2>
                <div
                    v-for="(value, key) in lifeGauges"
                    :key="key"
                    class="mb-4 p"
                >
                    <div class="mb-1 text-sm font-semibold">
                        {{ key }}
                    </div>
                    <div class="w-full bg-gray-200/80 rounded-full h-4">
                        <div
                            :class="gaugeColor(value)"
                            :style="{ width: `${value}%` }"
                            class="h-4 rounded-full text-center"
                        ></div>
                    </div>
                </div>
            </div>

            <div
                class="md:w-1/3 flex flex-col justify-between bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md mb-4 md:mb-0"
            >
                <div class="grid grid-cols-2 gap-4">
                    <Link :href="route('study.index')">
                        <div
                            class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer p-4"
                        >
                            <h3 class="font-semibold pb-2">Étude en cours</h3>
                            <div
                                v-if="studyDetails"
                                class="flex flex-col flex-grow"
                            >
                                <div class="size-20 mx-auto mb-4">
                                    <img
                                        :src="studyDetails.img"
                                        alt="Image du cours"
                                        class="w-full h-full object-contain"
                                    />
                                </div>

                                <p class="text-sm">
                                    {{ studyDetails.name }}
                                </p>
                            </div>
                            <div v-else>
                                <p class="text-sm">Pas d'étude en cours.</p>
                            </div>
                        </div>
                    </Link>
                    <Link :href="route('job')">
                        <div
                            class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer p-4"
                        >
                            <h3 class="font-semibold pb-2">Métier actif</h3>
                            <div
                                v-if="perso.job"
                                class="flex flex-col space-y-4"
                            >
                                <div class="size-20 mx-auto mb-4">
                                    <img
                                        :src="perso.job.img_job"
                                        alt="Image du job"
                                        class="w-full h-full object-contain"
                                    />
                                </div>

                                <p class="text-sm">
                                    {{ perso.job.name }}
                                </p>
                                <!-- <p
                                            class="px-4 py-2 text-sm font-semibold underline underline-offset-2"
                                        >
                                            Aller aux métiers
                                        </p> -->
                            </div>
                            <div v-else>
                                <p class="text-sm">Pas de métier actif.</p>
                            </div>
                        </div>
                    </Link>
                    <Link :href="route('doctor.index')">
                        <div
                            class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer p-4"
                        >
                            <h3 class="font-semibold mb-4">
                                {{
                                    currentSicknesses.length <= 1
                                        ? "Maladie active"
                                        : "Maladies actives"
                                }}
                            </h3>
                            <div v-if="currentSicknesses.length > 0">
                                <div
                                    v-for="sickness in currentSicknesses"
                                    :key="sickness.id"
                                    class="mb-4"
                                >
                                    <h4 class="text-md">
                                        {{ sickness.name }}
                                    </h4>
                                    <p class="text-sm mt-1">
                                        {{ sickness.description }}
                                    </p>
                                </div>
                            </div>
                            <p v-else class="text-center text-sm">
                                Aucune maladie pour le moment!
                            </p>
                        </div>
                    </Link>
                </div>
                <div class="flex self-end">
                    <Link :href="route('city')">
                        <div
                            class="bg-gray-300/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer p-2 px-4"
                        >
                            <h3 class="font-semibold">Aller en ville</h3>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        <div
            class="h-full flex items-center justify-between p-4 rounded-lg text-sm bg-emerald-900/90 backdrop-blur-md"
        >
            <div class="tracking-wide leading-relaxed">
                <h2 class="text-xl mb-2 font-semibold">
                    Bienvenue dans Lifers !
                </h2>
                <p class="flex-grow">
                    Votre nouvelle vie vous attend. Dès maintenant, vous allez
                    réincarner un Lifer et naviguez à travers les choix et les
                    défis d'une existence virtuelle riche et captivante.
                    Commencez par explorer les possibilités qui s'offrent à vous
                    : optez pour un métier sans diplôme pour plonger directement
                    dans le monde du travail et gagner vos premiers
                    Lifers'coins, ou inscrivez-vous à des études pour ouvrir la
                    porte à des carrières plus spécialisées et ambitieuses.
                </p>
                <p>
                    Pour bien démarrer, n'oubliez pas de prendre soin de votre
                    Lifer. Comme dans la vraie vie, équilibrer travail, loisirs,
                    relations sociales et bien-être est la clé d'une existence
                    épanouie. Faites des choix qui reflètent vos aspirations :
                    construisez une famille, adoptez un animal de compagnie,
                    forgez des amitiés durables. Chaque action a son importance
                    et contribue à l'histoire unique de votre Lifer. Lancez-vous
                    dans cette aventure avec curiosité et enthousiasme, et
                    découvrez tout ce que Lifers a à offrir. C'est le moment de
                    vivre la vie dont vous avez toujours rêvé, en version
                    accélérée !
                </p>
            </div>
        </div>
    </AppLayout>
</template>
