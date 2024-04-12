<script setup>
import { defineProps } from "vue";
import { Link } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    usersWithPerso: Object,
    age: Number,
});
</script>
<template>
    <AppLayout title="City">
        <template #header></template>
        <div class="container flex flex-col gap-4 mx-auto p-4 md:p-2 md:pt-24">
            <div class="flex bg-white p-4 rounded-lg shadow-md">
                <div class="space-y-2">
                    <h2 class="text-xl font-bold">Bienvenue à Lif'City</h2>

                    <p>
                        Plonge au cœur de notre ville dynamique, où chaque rue
                        est une invitation à l'aventure et chaque quartier un
                        monde à découvrir. Que tu sois attiré par l'animation du
                        marché, la quiétude d'une visite médicale, la vitalité
                        d'une séance de sport, ou la diversité des
                        divertissements, chaque lieu est une porte ouverte sur
                        une expérience unique.
                    </p>
                    <p>
                        Explore les rues animées qui vibrent au rythme de la vie
                        citadine, où chaque coin de rue révèle une nouvelle
                        surprise. Embrasse l'énergie de la ville et engage-toi
                        dans un parcours qui résonne avec tes intérêts et tes
                        aspirations. Que tu cherches à te divertir, à te former
                        ou à trouver de nouvelles opportunités professionnelles,
                        notre ville regorge d'opportunités et d'expériences à
                        vivre.
                    </p>
                </div>
                <img
                    src="/images/places/ville.webp"
                    alt="Workplace Image"
                    class="size-64 object-cover"
                />
            </div>
            <div class="bg-white p-4 rounded-lg shadow-md">
                <div class="grid grid-cols-2 gap-4">
                    <Link
                        href="/city/lifemarket"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Market
                    </Link>
                    <Link
                        href="/city/doctor"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Hôpital
                    </Link>
                    <Link
                        href="/city/sport"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Sport
                    </Link>
                    <Link
                        href="/city/entertainment"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Activité
                    </Link>
                    <Link
                        href="/study"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Université
                    </Link>
                    <Link
                        href="/job"
                        class="bg-cyan-500 hover:bg-cyan-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Lif'Emploi
                    </Link>
                </div>
            </div>
            <!-- Section pour afficher les persos -->
            <div class="bg-white p-4 rounded-lg shadow-md">
                <h2 class="text-xl font-bold mb-2">Liste des Lifers</h2>
                <ul>
                    <li
                        v-for="user in $page.props.usersWithPerso"
                        :key="user.id"
                        class="py-2"
                    >
                        <div v-if="user.perso" class="flex items-center gap-2">
                            <Link
                                :href="`/perso/${user.perso.id}`"
                                class="text-gray-600 hover:text-gray-800"
                            >
                                <p>
                                    {{ user.perso.first_name }}
                                    {{ user.perso.last_name }}
                                </p>
                            </Link>
                            <p class="text-sm text-gray-500">
                                {{ user.perso.age }} ans
                            </p>
                            <span
                                v-if="user.is_online"
                                class="text-sm font-semibold text-green-500"
                            >
                                En ligne</span
                            >
                            <span
                                v-else
                                class="text-sm font-semibold text-red-500"
                            >
                                Hors ligne</span
                            >
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
