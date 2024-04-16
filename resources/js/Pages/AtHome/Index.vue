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
        <template #header></template>

        <div class="flex justify-center">
            <div class="flex flex-col gap-4 mx-auto md:pt-24">
                <!-- Jauges de vie et Image du perso sur une ligne -->
                <div
                    class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 h-full"
                >
                    <!-- Image du perso -->
                    <div
                        class="bg-white flex flex-col col-span-3 p-8 rounded-lg shadow-md mb-4 md:mb-0"
                    >
                        <div class="flex items-end gap-2">
                            <h2 class="text-3xl font-bold">Lif'Home</h2>
                            <h2
                                v-if="perso"
                                class="text-xl font-semibold text-gray-500 italic"
                            >
                                {{ perso.first_name }}
                                {{ perso.last_name }}
                            </h2>
                        </div>
                        <div
                            class="grid grid-cols-1 md:grid-cols-2 gap-4 h-full"
                        >
                            <div class="flex items-end">
                                <img
                                    v-if="bodyImageUrl"
                                    :src="bodyImageUrl"
                                    alt="Image du perso"
                                    class="h-96 mx-auto"
                                />
                                <!-- <div class="mt-4 flex justify-center">
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
                                </div> -->
                            </div>
                            <div class="flex flex-col gap-4">
                                <div
                                    class="h-1/2 bg-gray-300 rounded-lg shadow-md"
                                ></div>
                                <div
                                    class="h-1/2 bg-gray-300 rounded-lg shadow-md"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <!-- Jauges de vie -->
                    <div
                        class="flex flex-col justify-between bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0"
                    >
                        <h2 class="font-semibold mb-4">Jauges de vie</h2>
                        <div
                            v-for="(value, key) in lifeGauges"
                            :key="key"
                            class="mb-4"
                        >
                            <div class="mb-1">{{ key }}</div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div
                                    :class="gaugeColor(value)"
                                    :style="{ width: `${value}%` }"
                                    class="h-4 rounded-full text-center"
                                ></div>
                            </div>
                        </div>
                    </div>
                    <!-- Maladies Actuelles -->
                    <div class="bg-white p-8 rounded-lg shadow-md mb-4 md:mb-0">
                        <h2 class="font-semibold mb-4">
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
                <div class="bg-white p-8 rounded-lg shadow-md">
                    <div class="flex items-center gap-4 mb-4">
                        <h2 class="font-semibold mr-2">Inventaire</h2>
                        <input
                            type="text"
                            v-model="searchTerm"
                            placeholder="Rechercher des articles..."
                            class="p-2 border text-sm border-gray-300 focus:border-gray-500 focus:ring-1 focus:ring-gray-500 rounded shadow-sm"
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
                                'py-2 px-4 rounded transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg text-sm',
                                selectedCategory.valueOf() === category
                                    ? 'bg-gray-400 text-gray-100'
                                    : 'border-2 bg-white border-gray-400 text-gray-700 hover:text-gray-100 hover:bg-gray-500 hover:border-gray-500',
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
                            @click="consumeItem(item)"
                            :key="item.id"
                            class="bg-white hover:bg-gray-300 p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                        >
                            <div class="size-36 place-self-center">
                                <img
                                    :src="item.img_item"
                                    :alt="item.name"
                                    class="mb-3"
                                />
                            </div>
                            <div class="flex">
                                <div class="flex-grow">
                                    <h4 class="text-md font-bold">
                                        {{ item.name }}
                                    </h4>
                                    <div
                                        class="flex items-center space-x-1 pt-1"
                                    >
                                        <p class="text-gray-600">
                                            {{ item.quantity }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            en stock
                                        </p>
                                    </div>
                                </div>
                                <div class="flex self-end items-end gap-2">
                                    <p class="text-xs">Clique pour consommer</p>

                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        xmlns:xlink="http://www.w3.org/1999/xlink"
                                        viewBox="0 0 256 256"
                                        class="size-6 text-gray-500 transition-opacity duration-300 ease-in-out"
                                    >
                                        <defs></defs>
                                        <g
                                            transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)"
                                        >
                                            <path
                                                d="M 69.416 43.298 H 68.97 c -0.983 0 -1.917 0.216 -2.756 0.603 c -0.644 -2.975 -3.295 -5.21 -6.459 -5.21 h -0.447 c -1.128 0 -2.19 0.284 -3.12 0.784 c -1 -2.379 -3.355 -4.054 -6.094 -4.054 h -0.447 c -0.925 0 -1.807 0.191 -2.606 0.536 v -9.458 c 0 -3.644 -2.964 -6.607 -6.608 -6.607 h -0.447 c -3.643 0 -6.607 2.964 -6.607 6.607 v 24.261 l -3.005 2.281 c -2.394 1.817 -3.911 4.461 -4.273 7.444 c -0.362 2.984 0.479 5.914 2.37 8.251 l 9.378 11.594 v 4.608 c 0 2.791 2.271 5.063 5.062 5.063 h 23.411 c 2.791 0 5.063 -2.271 5.063 -5.063 l 0.001 -4.375 c 2.996 -3.766 4.639 -8.438 4.639 -13.242 V 49.905 C 76.023 46.262 73.06 43.298 69.416 43.298 z M 72.023 67.32 c 0 4.102 -1.478 8.088 -4.159 11.224 c -0.311 0.362 -0.48 0.823 -0.48 1.3 v 5.094 c 0 0.586 -0.477 1.063 -1.063 1.063 H 42.911 c -0.585 0 -1.062 -0.477 -1.062 -1.063 v -5.316 c 0 -0.458 -0.157 -0.902 -0.445 -1.258 L 31.581 66.22 c -1.204 -1.488 -1.74 -3.354 -1.509 -5.254 c 0.23 -1.899 1.197 -3.583 2.721 -4.74 l 0.586 -0.444 v 5.495 c 0 1.104 0.896 2 2 2 s 2 -0.896 2 -2 v -9.506 c 0 -0.014 0 -0.026 0 -0.04 V 26.498 c 0 -1.438 1.169 -2.607 2.607 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 15.53 c 0 0 0 0 0 0.001 s 0 0 0 0.001 l 0.01 12.917 c 0.001 1.104 0.896 1.998 2 1.998 c 0 0 0.001 0 0.001 0 c 1.104 -0.001 1.999 -0.896 1.998 -2.002 L 47.04 42.028 c 0 -1.438 1.169 -2.607 2.606 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 3.27 c 0 0 0 0.001 0 0.001 c 0 0 0 0.001 0 0.001 l 0.009 9.647 c 0.001 1.104 0.896 1.998 2 1.998 c 0.001 0 0.001 0 0.002 0 c 1.104 -0.001 1.999 -0.897 1.998 -2.002 l -0.009 -9.646 c 0 -1.438 1.169 -2.607 2.606 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 4.088 c -0.006 0.066 -0.02 0.13 -0.02 0.197 l 0.01 5.366 c 0.002 1.104 0.897 1.996 2 1.996 c 0.001 0 0.002 0 0.004 0 c 1.104 -0.002 1.998 -0.899 1.996 -2.004 l -0.009 -4.852 c 0.006 -0.062 0.019 -0.121 0.019 -0.184 c 0 -1.438 1.17 -2.607 2.607 -2.607 h 0.446 c 1.438 0 2.607 1.17 2.607 2.607 V 67.32 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M 63.994 25.511 H 51.79 c -1.104 0 -2 -0.896 -2 -2 s 0.896 -2 2 -2 h 12.204 c 1.104 0 2 0.896 2 2 S 65.099 25.511 63.994 25.511 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M 39.985 16.204 c -1.104 0 -2 -0.896 -2 -2 V 2 c 0 -1.104 0.896 -2 2 -2 s 2 0.896 2 2 v 12.204 C 41.985 15.309 41.09 16.204 39.985 16.204 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M 48.558 18.441 c -0.512 0 -1.023 -0.195 -1.414 -0.586 c -0.781 -0.781 -0.781 -2.047 0 -2.828 l 8.63 -8.629 c 0.781 -0.781 2.047 -0.781 2.828 0 c 0.781 0.781 0.781 2.047 0 2.828 l -8.63 8.629 C 49.581 18.246 49.069 18.441 48.558 18.441 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M 28.181 25.511 H 15.977 c -1.104 0 -2 -0.896 -2 -2 s 0.896 -2 2 -2 h 12.204 c 1.104 0 2 0.896 2 2 S 29.285 25.511 28.181 25.511 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                            <path
                                                d="M 31.413 18.441 c -0.512 0 -1.024 -0.195 -1.414 -0.586 L 21.37 9.226 c -0.781 -0.781 -0.781 -2.047 0 -2.828 c 0.78 -0.781 2.048 -0.781 2.828 0 l 8.629 8.629 c 0.781 0.781 0.781 2.047 0 2.828 C 32.437 18.246 31.925 18.441 31.413 18.441 z"
                                                style="
                                                    stroke: none;
                                                    stroke-width: 1;
                                                    stroke-dasharray: none;
                                                    stroke-linecap: butt;
                                                    stroke-linejoin: miter;
                                                    stroke-miterlimit: 10;
                                                    fill: currentColor;
                                                    fill-rule: nonzero;
                                                    opacity: 1;
                                                "
                                                transform=" matrix(1 0 0 1 0 0) "
                                                stroke-linecap="round"
                                            />
                                        </g>
                                    </svg>
                                </div>
                            </div>
                            <!-- <div class="text-center mt-3">
                                <button
                                    @click="consumeItem(item)"
                                    class="bg-gray-500 hover:bg-gray-700 focus:outline-none focus:ring focus:ring-gray-300 active:bg-gray-800 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out"
                                >
                                    Consommer
                                </button>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
