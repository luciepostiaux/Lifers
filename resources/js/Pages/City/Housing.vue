<script setup>
import { defineProps, reactive } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    residences: Array,
    perso: Object,
});

const showDetails = reactive({});

const toggleDetails = (residenceId) => {
    showDetails[residenceId] = !showDetails[residenceId];
};

const buyResidence = (residenceId, paymentType) => {
    const residence = props.residences.find((r) => r.id === residenceId);
    console.log(
        "Prix achat:",
        residence.prix_achat,
        "Argent perso:",
        props.perso.money
    ); // Correction: Use props.perso.money instead of perso.money

    if (parseFloat(residence.prix_achat) > parseFloat(props.perso.money)) {
        alert("Fonds insuffisants pour cet achat.");
        return;
    }
    router.post("/buy/residence", {
        residenceId,
        paymentType,
    });
};
</script>

<template>
    <AppLayout title="Logements">
        <div class="">
            <div
                class="flex flex-col md:flex-row mb-4 w-full h-full gap-2 lg:gap-4"
            >
                <div
                    class="flex-1 flex flex-col md:flex-auto md:w-3/5 lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md order-2 md:order-1"
                >
                    <div
                        class="flex flex-col tracking-wide leading-relaxed gap-2"
                    >
                        <h1 class="text-xl font-bold">
                            Découvrez Nos Logements
                        </h1>
                        <p class="">
                            Bienvenue sur notre plateforme dédiée à l'achat de
                            logements, où vous trouverez une sélection
                            diversifiée de propriétés adaptées à tous les
                            besoins et à tous les budgets. Que vous recherchiez
                            une première maison modeste, une villa confortable
                            pour accueillir votre famille, ou même un château
                            majestueux pour vivre dans le luxe absolu, nous
                            avons ce qu'il vous faut.
                        </p>
                        <p class="">
                            Explorez notre catalogue pour découvrir une variété
                            de logements, des caravanes modestes aux maisons de
                            banlieue fonctionnelles, en passant par les villas
                            suburbaines élégantes et les châteaux somptueux.
                            Chaque propriété est accompagnée de descriptions
                            détaillées et d'images captivantes pour vous aider à
                            visualiser votre futur chez-vous.
                        </p>
                    </div>
                </div>
                <div
                    class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900 order-1 md:order-2"
                >
                    <img
                        src="/images/places/market_4-6.webp"
                        alt="University Image"
                        class="object-cover h-full"
                    />
                </div>
            </div>
            <div
                class="bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
            >
                <div
                    class="grid grid-cols-1 md:grid-cols-4 xl:grid-cols-5 gap-4"
                >
                    <div
                        v-for="residence in residences"
                        :key="residence.id"
                        class="relative bg-emerald-950/50 hover:bg-emerald-950 backdrop-blur-lg p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:shadow-lg cursor-pointer"
                        @click="toggleDetails(residence.id)"
                    >
                        <div class="flex flex-col justify-end">
                            <img
                                :src="residence.image_path"
                                class="object-contain w-full mb-3 rounded"
                            />
                            <div
                                v-if="!showDetails[residence.id]"
                                class="flex justify-between items-end"
                            >
                                <div>
                                    <h4 class="text-md font-semibold">
                                        {{ residence.type }}
                                    </h4>
                                    <p class="text-sm">
                                        {{ residence.prix_achat }} LC
                                    </p>
                                </div>
                                <div class="flex flex-col items-end">
                                    <button
                                        v-if="
                                            residence.owned_count === 0 &&
                                            parseFloat(residence.prix_achat) <=
                                                parseFloat(perso.money)
                                        "
                                        @click.stop="
                                            buyResidence(residence.id, 'full')
                                        "
                                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-2"
                                    >
                                        Acheter
                                    </button>
                                    <p
                                        v-else-if="residence.owned_count > 0"
                                        class="text-red-500 font-bold py-2 px-4 rounded mb-2"
                                    >
                                        Déjà possédé
                                    </p>
                                    <p
                                        v-else
                                        class="text-yellow-500 font-bold py-2 px-4 rounded mb-2"
                                    >
                                        Fonds insuffisants
                                    </p>
                                </div>
                            </div>
                            <div
                                v-else
                                class="absolute top-0 left-0 w-full h-full justify-end bg-emerald-900/90 backdrop-blur-lg hover:bg-emerald-900 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer p-4 flex flex-col"
                            >
                                <div>
                                    <p
                                        class="text-sm tracking-wide leading-relaxed md:mb-4"
                                    >
                                        {{ residence.description }}
                                    </p>
                                </div>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <h4 class="text-md font-semibold">
                                            {{ residence.type }}
                                        </h4>
                                        <p class="text-sm">
                                            {{ residence.prix_achat }} LC
                                        </p>
                                    </div>
                                    <div class="flex flex-col items-end">
                                        <button
                                            v-if="
                                                residence.owned_count === 0 &&
                                                parseFloat(
                                                    residence.prix_achat
                                                ) <= parseFloat(perso.money)
                                            "
                                            @click.stop="
                                                buyResidence(
                                                    residence.id,
                                                    'full'
                                                )
                                            "
                                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded mb-2"
                                        >
                                            Acheter
                                        </button>
                                        <p
                                            v-else-if="
                                                residence.owned_count > 0
                                            "
                                            class="text-red-500 font-bold py-2 px-4 rounded mb-2"
                                        >
                                            Déjà possédé
                                        </p>
                                        <p
                                            v-else
                                            class="text-yellow-500 font-bold py-2 px-4 rounded mb-2"
                                        >
                                            Fonds insuffisants
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
