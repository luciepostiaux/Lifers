<script setup>
import { defineProps, ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

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
const searchTerm = ref("");

const filteredItems = computed(() => {
    let filtered = props.inventoryItemsByCategory;

    if (searchTerm.value) {
        const lowerSearchTerm = searchTerm.value.toLowerCase();
        filtered = Object.fromEntries(
            Object.entries(filtered).map(([category, items]) => [
                category,
                items.filter((item) =>
                    item.name.toLowerCase().includes(lowerSearchTerm)
                ),
            ])
        );
    }

    if (selectedCategory.value) {
        filtered = filtered[selectedCategory.value] || [];
    } else {
        let allItems = [];
        for (let categoryItems of Object.values(filtered)) {
            allItems = allItems.concat(categoryItems);
        }
        filtered = allItems;
    }

    return filtered;
});

const filterByCategory = (category) => {
    if (selectedCategory.value === category) {
        selectedCategory.value = "";
    } else {
        selectedCategory.value = category;
    }
};

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-500";
    else if (value <= 50) return "bg-orange-500";
    return "bg-green-500";
};

const consumeItem = (item) => {
    router.post(
        "/consume-item",
        { itemId: item.id },
        {
            preserveState: true,
            preserveScroll: true,
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
                        <h2 class="text-xl font-bold mb-4">
                            <!-- Gestion du singulier/pluriel pour le titre -->
                            {{
                                currentSicknesses.length <= 1
                                    ? "Maladie active"
                                    : "Maladies actives"
                            }}
                        </h2>
                        <div v-if="currentSicknesses.length > 0">
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
                        <p v-else class="text-center text-sm text-gray-600">
                            Aucune maladie pour le moment!
                        </p>
                    </div>
                </div>
                <!-- Inventaire -->
                <div class="bg-white p-8 rounded-lg shadow-md mt-4">
                    <h2 class="text-2xl font-semibold mb-4">Inventaire</h2>
                    <div class="flex gap-4 mb-4">
                        <input
                            type="text"
                            v-model="searchTerm"
                            placeholder="Rechercher des articles..."
                            class="p-2 border border-gray-300 focus:border-gray-500 focus:ring-1 focus:ring-gray-500 rounded shadow-sm"
                        />
                        <button
                            class="py-2 px-4 text-gray-100 bg-gray-500 text-sm font-semibold rounded transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg hover:bg-gray-700"
                            @click="filterByCategory('')"
                        >
                            Tout
                        </button>
                        <button
                            v-for="category in Object.keys(
                                props.inventoryItemsByCategory
                            )"
                            :key="category"
                            :class="[
                                'py-2 px-4 rounded transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg',
                                selectedCategory.valueOf() === category
                                    ? 'bg-gray-400 text-gray-100'
                                    : 'border-2 bg-white border-gray-400 text-gray-700 hover:text-gray-100 hover:bg-gray-700 hover:border-gray-700',
                            ]"
                            @click="filterByCategory(category)"
                        >
                            {{ category }}
                        </button>
                    </div>
                    <div
                        class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-4 pb-4"
                    >
                        <div
                            v-for="item in filteredItems"
                            :key="item.id"
                            class="bg-white p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                        >
                            <div class="size-36">
                                <img
                                    :src="item.img_item"
                                    :alt="item.name"
                                    class="mb-3"
                                />
                            </div>
                            <div class="flex-grow">
                                <h4 class="text-md font-bold">
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
                            <div class="text-center mt-3">
                                <button
                                    @click="consumeItem(item)"
                                    class="bg-gray-500 hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 active:bg-gray-800 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out"
                                >
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
