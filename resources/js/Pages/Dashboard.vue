<script setup>
import { defineProps, computed } from "vue";

import AppLayout from "@/Layouts/AppLayout.vue";
import { Link } from "@inertiajs/vue3";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import LifeGauges from "@/Components/LifeGauges.vue";

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
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header></template>
        <div class="flex flex-col gap-4 lg:flex-row mb-4 w-full h-full">
            <div
                class="w-full lg:w-1/3 flex flex-col justify-between bg-emerald-900/90 p-4 rounded-lg shadow-md md:mb-0 backdrop-blur-lg "
            >
                <h1 class="text-xl font-bold mb-4">Accueil</h1>

                <div class="gap-4 h-full">
                    <div class="flex justify-between">
                        <div class="w-1/2 h-full flex items-end">
                            <img
                                v-if="bodyImageUrl"
                                :src="bodyImageUrl"
                                alt="Image du perso"
                                class="h-96 mx-auto"
                            />
                        </div>
                        <div
                            class="flex flex-col w-1/2 h-full justify-between gap-4"
                        >
                            <div class="w-full h-full">
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
                            <div class="text-gray-200 lg:mt-4">
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
                class="w-full lg:w-2/3 flex flex-col md:flex-row gap-2 md:gap-4"
            >
                <div
                    class="w-full md:w-1/2 flex flex-col justify-between bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
                >
                    <!-- <H2 class="">Jauges de vie</H2> -->
                    <div>
                        <LifeGauges :gauges="lifeGauges" />
                    </div>
                    <div class="flex self-end">
                        <Link :href="route('athome')">
                            <PrimaryButton>
                                <h3 class="font-semibold">Aller chez soi</h3>
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div
                    class="w-full md:w-1/2 flex flex-col justify-between bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
                >
                    <div>
                        <h2 class="text-lg mb-2">Accès rapide</h2>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Étude en cours -->
                            <Link :href="route('study.index')">
                                <div
                                    class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:shadow-lg cursor-pointer p-4 h-full"
                                >
                                    <h3 class="font-semibold pb-2">
                                        Étude en cours
                                    </h3>
                                    <div
                                        v-if="studyDetails"
                                        class="flex flex-col lg:flex-grow justify-center items-center"
                                    >
                                        <div class="size-20 mx-auto">
                                            <img
                                                :src="studyDetails.img"
                                                alt="Image du cours"
                                                class="w-full h-full object-contain"
                                            />
                                        </div>
                                        <p class="text-sm text-center">
                                            {{ studyDetails.name }}
                                        </p>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm text-center mt-2">
                                            Pas d'étude en cours.
                                        </p>
                                    </div>
                                </div>
                            </Link>

                            <!-- Métier actif -->
                            <Link :href="route('job')">
                                <div
                                    class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:shadow-lg cursor-pointer p-4 h-full"
                                >
                                    <h3 class="font-semibold pb-2">
                                        Métier actif
                                    </h3>
                                    <div
                                        v-if="perso.job"
                                        class="flex flex-grow justify-center items-center"
                                    >
                                        <div class="size-20 mx-auto">
                                            <img
                                                :src="perso.job.img_job"
                                                alt="Image du job"
                                                class="w-full h-full object-contain"
                                            />
                                        </div>
                                        <p class="text-sm text-center">
                                            {{ perso.job.name }}
                                        </p>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm text-center mt-2">
                                            Pas de métier actif.
                                        </p>
                                    </div>
                                </div>
                            </Link>

                            <!-- Maladies actives -->
                            <Link :href="route('doctor.index')">
                                <div
                                    class="bg-emerald-950 hover:bg-emerald-950/50 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:shadow-lg cursor-pointer p-4 h-full"
                                >
                                    <h3 class="font-semibold mb-2">
                                        {{
                                            currentSicknesses.length <= 1
                                                ? "Maladie active"
                                                : "Maladies actives"
                                        }}
                                    </h3>
                                    <div
                                        v-if="currentSicknesses.length > 0"
                                        class="flex-grow"
                                    >
                                        <div
                                            v-for="sickness in currentSicknesses"
                                            :key="sickness.id"
                                            class="mb-4"
                                        >
                                            <div class="flex space-x-2">
                                                <h4
                                                    class="text-sm font-semibold text-red-500"
                                                >
                                                    {{ sickness.name }}
                                                </h4>
                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 24 24"
                                                    fill="currentColor"
                                                    class="size-4 text-red-500"
                                                >
                                                    <path
                                                        d="M12.8659 3.00017L22.3922 19.5002C22.6684 19.9785 22.5045 20.5901 22.0262 20.8662C21.8742 20.954 21.7017 21.0002 21.5262 21.0002H2.47363C1.92135 21.0002 1.47363 20.5525 1.47363 20.0002C1.47363 19.8246 1.51984 19.6522 1.60761 19.5002L11.1339 3.00017C11.41 2.52187 12.0216 2.358 12.4999 2.63414C12.6519 2.72191 12.7782 2.84815 12.8659 3.00017ZM10.9999 16.0002V18.0002H12.9999V16.0002H10.9999ZM10.9999 9.00017V14.0002H12.9999V9.00017H10.9999Z"
                                                    ></path>
                                                </svg>
                                            </div>
                                            <p class="text-xs mt-1">
                                                {{ sickness.description }}
                                            </p>
                                        </div>
                                    </div>
                                    <p v-else class="text-sm text-center mt-2">
                                        Aucune maladie pour le moment !
                                    </p>
                                </div>
                            </Link>
                        </div>
                    </div>
                    <div class="flex self-end">
                        <Link :href="route('city')">
                            <PrimaryButton>
                                <h3 class="font-semibold">Aller en ville</h3>
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
        <div
            class="h-full flex items-center justify-between p-4 rounded-lg text-sm bg-emerald-900/90 backdrop-blur-lg"
        >
            <div class="">
                <h2 class="text-xl mb-2 font-semibold">
                    Bienvenue dans Lifers !
                </h2>
                <p class="flex-grow leading-6 tracking-wider">
                    Votre nouvelle vie vous attend. Dès maintenant, vous allez
                    réincarner un Lifer et naviguez à travers les choix et les
                    défis d'une existence virtuelle riche et captivante.
                    Commencez par explorer les possibilités qui s'offrent à vous
                    : optez pour un métier sans diplôme pour plonger directement
                    dans le monde du travail et gagner vos premiers
                    Lifers'coins, ou inscrivez-vous à des études pour ouvrir la
                    porte à des carrières plus spécialisées et ambitieuses.
                    <br />
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
                <div class="flex justify-end mt-4">
                    <Link :href="route('help-info')">
                        <PrimaryButton>Informations et Aide</PrimaryButton>
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
