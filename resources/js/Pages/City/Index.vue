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
        <div class="">
            <div class="flex flex-col md:flex-row mb-4 w-full h-full gap-4">
                <div
                    class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
                >
                    <div
                        class="flex flex-col tracking-wide leading-relaxed gap-2"
                    >
                        <h1 class="text-xl font-bold mb-4">
                            Bienvenue à Lif'City
                        </h1>

                        <p>
                            Plonge au cœur de notre ville dynamique, où chaque
                            rue est une invitation à l'aventure et chaque
                            quartier un monde à découvrir. Que tu sois attiré
                            par l'animation du marché, la quiétude d'une visite
                            médicale, la vitalité d'une séance de sport, ou la
                            diversité des divertissements, chaque lieu est une
                            porte ouverte sur une expérience unique.
                        </p>
                        <p>
                            Explore les rues animées qui vibrent au rythme de la
                            vie citadine, où chaque coin de rue révèle une
                            nouvelle surprise. Embrasse l'énergie de la ville et
                            engage-toi dans un parcours qui résonne avec tes
                            intérêts et tes aspirations. Que tu cherches à te
                            divertir, à te former ou à trouver de nouvelles
                            opportunités professionnelles, notre ville regorge
                            d'opportunités et d'expériences à vivre.
                        </p>
                    </div>
                </div>
                <div
                    class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md bg-emerald-900/90 backdrop-blur-md p-4"
                >
                    <div class="">
                        <h2 class="text-xl font-semibold mb-4">Lieux</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <Link
                                href="/city/lifemarket"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Market
                            </Link>
                            <Link
                                href="/city/doctor"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Hôpital
                            </Link>
                            <Link
                                href="/city/sport"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Sport
                            </Link>
                            <Link
                                href="/city/entertainment"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Activité
                            </Link>
                            <Link
                                href="/study"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Université
                            </Link>
                            <Link
                                href="/job"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Emploi
                            </Link>
                            <Link
                                href="/city/housing"
                                class="inline-flex items-center px-4 py-2 bg-emerald-950/50 hover:bg-emerald-950 hover:font-bold border border-transparent rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Lif'Logement
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section pour afficher les persos -->
            <div
                class="bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
            >
                <h2 class="text-xl font-bold mb-2">Liste des Lifers</h2>
                <ul>
                    <li
                        v-for="user in $page.props.usersWithPerso"
                        :key="user.id"
                        class="py-2"
                    >
                        <div v-if="user.perso" class="flex items-center gap-2">
                            <!-- <Link :href="`/perso/${user.perso.id}`" class="">
                                <p
                                    class="text-gray-100 hover:text-gray-300 font-semibold"
                                >
                                    {{ user.perso.first_name }}
                                    {{ user.perso.last_name }}
                                </p>
                            </Link> -->
                            <p class="text-gray-100 font-semibold">
                                {{ user.perso.first_name }}
                                {{ user.perso.last_name }}
                            </p>
                            <p class="text-sm">{{ user.perso.age }} ans</p>
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
                        <hr class="mt-3 border border-gray-200/50" />
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
