<script setup>
import { defineProps, ref, computed } from "vue";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";

// Définir les props ici
const props = defineProps({
    perso: Object,
    bodyImageUrl: String,
    money: String,
    age: Number,
    lifeGauges: Object,
    inventoryItemsByCategory: Object,
    currentSicknesses: Array,
});

const selectedCategory = ref("");

const filteredItems = computed(() => {
    // Si un filtre est sélectionné, retourne les items de cette catégorie.
    if (selectedCategory.value) {
        return props.inventoryItemsByCategory[selectedCategory.value] || [];
    }
    // Si aucun filtre n'est sélectionné, aplatit tous les items dans un seul tableau.
    let allItems = [];
    for (let categoryItems of Object.values(props.inventoryItemsByCategory)) {
        allItems = allItems.concat(categoryItems);
    }
    return allItems; // Retourne un tableau d'items aplati
});

const filterByCategory = (category) => {
    selectedCategory.value = category;
};

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-500";
    else if (value <= 50) return "bg-orange-500";
    return "bg-green-500";
};

const consumeItem = (item) => {
    Inertia.post(
        "/consume-item",
        {
            itemId: item.id,
        },
        {
            preserveState: false,
        }
    );
};
const formatDate = (dateString) => {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("fr-FR", options);
};
</script>
<template>
    <AppLayout title="AtHome">
        <div class="flex justify-center">
            <div class="container flex flex-col gap-4 mx-auto p-4 md:pt-24">
                <!-- Jauges de vie et Image du perso sur une ligne -->
                <div class="md:grid md:grid-cols-3 md:gap-4">
                    <!-- Jauges de vie -->
                    <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
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
                    </div>
                    <!-- Image du perso -->
                    <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
                        <img
                            v-if="bodyImageUrl"
                            :src="bodyImageUrl"
                            alt="Image du perso"
                            class="h-96 mx-auto"
                        />
                        <div class="mt-4">
                            <h1 v-if="perso" class="text-lg font-semibold">
                                {{ perso.first_name }} {{ perso.last_name }}
                            </h1>
                            <div class="flex flex-col text-sm">
                                <p class="font-semibold">{{ age }} ans</p>
                                <p class="font-semibold">
                                    {{ money }} Lif'coins
                                </p>
                            </div>
                        </div>
                    </div>
                    <!-- Maladies Actuelles -->
                    <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
                        <h2 class="text-xl font-bold mb-4">Maladies actives</h2>
                        <div
                            v-for="sickness in currentSicknesses"
                            :key="sickness.id"
                            class="mb-4"
                        >
                            <h4 class="text-md font-semibold">
                                {{ sickness.name }}
                            </h4>
                            <p class="text-sm text-gray-600 mt-1">
                                {{ sickness.description }}
                            </p>
                            <p
                                class="text-xs italic font-semibold text-right pt-2 text-gray-600"
                            >
                                Contracté le:
                                {{ formatDate(sickness.contracted_at) }}
                            </p>
                        </div>
                    </div>
                </div>
                <!-- Inventaire -->
                <div class="bg-white p-8 rounded-lg shadow-md mt-4">
                    <h2 class="text-2xl font-semibold mb-4">Inventaire</h2>
                    <div class="flex gap-4">
                        <p>Filtre :</p>
                        <button @click="filterByCategory('')">All</button>
                        <button @click="filterByCategory('Nourriture')">
                            Nourriture
                        </button>
                        <button @click="filterByCategory('Boisson')">
                            Boisson
                        </button>
                        <button @click="filterByCategory('Propreté')">
                            Propreté
                        </button>
                    </div>
                    <!-- Utilisation directe de filteredItems puisqu'il s'agit désormais toujours d'un tableau -->
                    <div
                        class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 pb-4"
                    >
                        <div
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="bg-gray-100 p-4 rounded-lg shadow-md flex flex-col"
                        >
                            <div class="flex-grow py-2">
                                <h4 class="text-md font-semibold">
                                    {{ item.name }}
                                </h4>
                                <div class="flex items-center space-x-1 pt-1">
                                    <p class="text-gray-600">
                                        {{ item.quantity }}
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        en stock
                                    </p>
                                </div>
                            </div>
                            <div
                                class="text-center text-sm font-semibold text-[#F5BC81]"
                            >
                                <button @click="consumeItem(item)" class="py-1">
                                    Consommer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
