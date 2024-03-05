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
});

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-500";
    else if (value <= 50) return "bg-orange-500";
    return "bg-green-500";
};
// const daysRemaining = computed(() => {
//     if (props.studyDetails && props.studyDetails.end_date) {
//         const endDate = new Date(props.studyDetails.end_date);
//         const today = new Date();
//         const difference = endDate.getTime() - today.getTime();
//         const days = Math.ceil(difference / (1000 * 3600 * 24));
//         return days > 0 ? days : "Les études sont terminées.";
//     }
//     return "Aucune date de fin prévue.";
// });
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="container mx-auto p-4">
            <!-- <div>
                <h1 v-if="perso" class="text-xl font-bold">
                    {{ perso.first_name }}
                    {{ perso.last_name }}
                </h1>
            </div> -->

            <div class="md:grid md:grid-cols-3 md:gap-4">
                <div class="bg-white p-4 rounded-lg shadow-md mb-4 md:mb-0">
                    <img
                        v-if="bodyImageUrl"
                        :src="bodyImageUrl"
                        alt="Image du perso"
                        class="h-96 mx-auto"
                    />

                    <div class="text-center mt-4">
                        <div class="flex">
                            <p class="font-semibold">Âge :</p>
                            <p>{{ age }} ans</p>
                        </div>
                        <div class="flex">
                            <p class="font-semibold">Lif'coins :</p>
                            <p>{{ money }}$</p>
                        </div>
                    </div>
                </div>

                <div
                    class="bg-white flex flex-col p-4 rounded-lg shadow-md mb-4 md:mb-0"
                >
                    <h3 class="font-semibold pb-2">Jauges de vie</h3>

                    <div
                        v-for="(value, key) in lifeGauges"
                        :key="key"
                        class="mb-4"
                    >
                        <div>{{ key }}</div>
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            <div
                                :class="gaugeColor(value)"
                                :style="{ width: `${value}%` }"
                                class="h-4 rounded-full text-center"
                            ></div>
                        </div>
                    </div>
                    <Link
                        :href="route('athome')"
                        class="mt-2 px-4 py-2 text-sm bg-[#9EE5F5] hover:text-white rounded hover:bg-[#71A4B0] transition-all"
                        >Aller chez soi</Link
                    >
                </div>

                <div class="bg-white p-4 rounded-lg shadow-md">
                    <div class="mb-4">
                        <h3 class="font-semibold pb-2">Études en cours</h3>
                        <div
                            v-if="studyDetails"
                            class="flex flex-col space-y-4"
                        >
                            <p class="text-sm">{{ studyDetails.name }}</p>

                            <!-- <p
                                class="text-sm"
                                v-if="studyDetails && studyDetails.end_date"
                            >
                                Fin prévue : {{ studyDetails.end_date }}
                                <span v-if="daysRemaining"
                                    >{{ daysRemaining }} jours restants.</span
                                >
                            </p> -->
                            <p class="text-sm" v-if="studyDetails.end_date">
                                Fin prévue : {{ studyDetails.end_date }}
                            </p>

                            <Link
                                :href="route('study')"
                                class="px-4 py-2 text-sm bg-[#9EE5F5] hover:text-white rounded hover:bg-[#71A4B0] transition-all"
                                >Aller aux études</Link
                            >
                        </div>
                        <div v-else>Pas d'études en cours.</div>
                    </div>
                    <div class="pt-4">
                        <h3 class="font-semibold pb-2">Métier actif</h3>
                        <div v-if="perso.job" class="flex flex-col space-y-4">
                            <p class="text-sm">
                                {{ perso.job.name }}
                            </p>
                            <Link
                                :href="route('job')"
                                class="px-4 py-2 text-sm bg-[#9EE5F5] hover:text-white rounded hover:bg-[#71A4B0] transition-all"
                                >Aller aux métiers</Link
                            >
                        </div>
                        <div v-else>
                            <p class="text-sm">Pas de métier actif.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="flex items-center justify-between bg-white p-4 rounded-lg shadow-md mt-4"
            >
                <div>
                    <p class="flex-grow">
                        Bienvenue dans Lifers. Votre nouvelle vie vous attend.
                        Dès maintenant, vous allez réincarner un Lifer et
                        naviguez à travers les choix et les défis d'une
                        existence virtuelle riche et captivante. Commencez par
                        explorer les possibilités qui s'offrent à vous : optez
                        pour un métier sans diplôme pour plonger directement
                        dans le monde du travail et gagner vos premiers
                        Lifers'coins, ou inscrivez-vous à des études pour ouvrir
                        la porte à des carrières plus spécialisées et
                        ambitieuses.
                    </p>
                    <p>
                        Pour bien démarrer, n'oubliez pas de prendre soin de
                        votre Lifer. Comme dans la vraie vie, équilibrer
                        travail, loisirs, relations sociales et bien-être est la
                        clé d'une existence épanouie. Faites des choix qui
                        reflètent vos aspirations : construisez une famille,
                        adoptez un animal de compagnie, forgez des amitiés
                        durables. Chaque action a son importance et contribue à
                        l'histoire unique de votre Lifer. Lancez-vous dans cette
                        aventure avec curiosité et enthousiasme, et découvrez
                        tout ce que Lifers a à offrir. C'est le moment de vivre
                        la vie dont vous avez toujours rêvé, en version
                        accélérée !
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
