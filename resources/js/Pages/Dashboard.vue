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
});

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-500";
    else if (value <= 50) return "bg-orange-500";
    return "bg-green-500";
};
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header></template>
        <div class="flex flex-col gap-4 md:flex-row mb-4 w-full h-full">
            <div
                class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-white p-4 rounded-lg shadow-md"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 h-full">
                    <div class="flex border-2 border-green-500 pb-4">
                        <div class="w-1/2 flex items-end">
                            <img
                                v-if="bodyImageUrl"
                                :src="bodyImageUrl"
                                alt="Image du perso"
                                class="h-96 mx-auto"
                            />
                        </div>
                        <div
                            class="flex flex-col justify-center items-center w-1/2 border-2 border-blue-400"
                        >
                            <div class="h-1/2 w-full border-2 border-red-400">
                                <p>logement</p>
                            </div>
                            <div
                                class="flex flex-col justify-end border-2 border-red-400 mt-2 text-sm font-bold py-2 px-4 rounded h-1/2 w-full"
                            >
                                <div>
                                    <h1
                                        v-if="perso"
                                        class="text-xl font-semibold"
                                    >
                                        {{ perso.first_name }}
                                        {{ perso.last_name }}
                                    </h1>
                                    <div class="flex flex-col text-sm">
                                        <p class="font-semibold">
                                            {{ age }} ans
                                        </p>
                                        <p class="font-semibold">
                                            {{ money }} Lif'Coins
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col gap-4 py-4">
                        <div class="flex flex-col justify-between">
                            <h3 class="font-semibold mb-4">Jauges de vie</h3>
                            <div
                                v-for="(value, key) in lifeGauges"
                                :key="key"
                                class="mb-4"
                            >
                                <div class="mb-1">{{ key }}</div>
                                <div class="w bg-gray-200 rounded-full h-4">
                                    <div
                                        :class="gaugeColor(value)"
                                        :style="{ width: `${value}%` }"
                                        class="h-4 rounded-full text-center"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex flex-col flex-1 justify-between md:flex-auto md:w-2/5 lg:w-2/5 p-8 mb-4 md:mb-0"
            >
                <div class="grid grid-cols-2 gap-4">
                    <Link :href="route('study.index')">
                        <div
                            class="mt-2 text-sm bg-gray-400 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700"
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
                            class="mt-2 text-sm bg-gray-400 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700"
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
                            class="mt-2 text-sm bg-gray-400 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700"
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
                            class="text-sm bg-gray-400 text-white font-bold py-2 px-4 rounded-lg hover:bg-gray-700"
                        >
                            <h3 class="font-semibold">Aller en ville</h3>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between p-4 text-sm">
            <div>
                <h2 class="text-xl font-semibold mb-2">
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
