<script setup>
import { defineProps, ref, computed } from "vue";
import { Link, router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import LifeGauges from "@/Components/LifeGauges.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    perso: Object,
    bodyImageUrl: String,
    money: String,
    age: Number,
    lifeGauges: Object,
    inventoryItemsByCategory: Object,
    currentSicknesses: Array,
    residences: Object,
    activeResidence: Object,
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
    if (value <= 20) return "bg-red-800";
    else if (value <= 50) return "bg-orange-400";
    return "bg-lime-700";
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

const setActiveResidence = (residenceId) => {
    router.put(`/residences/set-active/${residenceId}`);
};

const nonActiveResidences = computed(() => {
    return props.residences.filter(
        (residence) => residence.id !== props.activeResidence
    );
});

const calculateSalePrice = (price) => {
    return Math.round(price * 0.7);
};
const filteredResidences = computed(() => {
    return props.residences.filter(
        (r) => r.pivot.id !== props.activeResidence.id
    );
});
const sellResidence = (residence) => {
    router.post(`/residences/${residence.id}/sell`, {
        price: calculateSalePrice(residence.prix_achat),
    });
};
</script>

<template>
    <AppLayout title="AtHome">
        <template #header></template>

        <div class="flex justify-center">
            <div class="flex flex-col gap-2 lg:gap-4 mx-auto">
                <!-- Jauges de vie et Image du perso sur une ligne -->
                <div class="flex flex-col lg:flex-row gap-2 lg:gap-4 h-full">
                    <!-- Image du perso -->
                    <div
                        class="w-full lg:w-1/3 bg-emerald-900/90 backdrop-blur-lg flex flex-col justify-between p-4 rounded-lg shadow-md"
                    >
                        <h1 class="text-xl font-bold mb-4">Lif'Home</h1>

                        <div class="flex items-end">
                            <div class="w-1/2">
                                <img
                                    v-if="bodyImageUrl"
                                    :src="bodyImageUrl"
                                    alt="Image du perso"
                                    class="h-96 mx-auto"
                                />
                            </div>
                            <div
                                class="text-gray-200 w-1/2 flex flex-col justify-between h-full mb-2"
                            >
                                <div
                                    class="w-full h-1/2 bg-emerald-950 rounded-lg p-4"
                                >
                                    <p class="text-sm text-center">
                                        Contenu à venir
                                    </p>
                                </div>
                                <div class="lg:ml-4">
                                    <h2 v-if="perso" class="text-xl font-bold">
                                        {{ perso.first_name }}
                                        {{ perso.last_name }}
                                    </h2>
                                    <div class="flex flex-col justify-between">
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
                    <div class="flex w-full lg:w-2/3 gap-2 lg:gap-4">
                        <div
                            class="w-1/2 flex flex-col justify-between backdrop-blur-lg bg-emerald-900/90 p-4 rounded-lg shadow-md md:mb-0"
                        >
                            <LifeGauges :gauges="lifeGauges" />
                        </div>
                        <!-- Maladies Actuelles -->
                        <div
                            class="w-1/2 flex flex-col justify-between bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md md:mb-0"
                        >
                            <div>
                                <h3 class="text-lg mb-2">
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
                                        <p
                                            class="text-xs italic text-right pt-2"
                                        >
                                            Contracté le:
                                            {{
                                                formatDate(
                                                    sickness.contracted_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                                <p v-else class="text-sm text-center mt-2">
                                    Aucune maladie pour le moment !
                                </p>
                            </div>
                            <div class="flex self-end">
                                <Link :href="route('doctor.index')">
                                    <PrimaryButton>
                                        <h3 class="font-semibold">
                                            Se soigner
                                        </h3>
                                    </PrimaryButton>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col lg:flex-row gap-4">
                    <div
                        class="flex flex-col w-full lg:w-1/3 gap-4 backdrop-blur-lg bg-emerald-900/90 p-4 rounded-lg shadow-md order-2 lg:order-1"
                    >
                        <div>
                            <!-- Résidence Active ou message d'absence -->
                            <div v-if="activeResidence" class="relative group">
                                <div class="relative w-full h-36 md:h-96">
                                    <img
                                        :src="activeResidence.image_path"
                                        alt="Résidence Active"
                                        class="absolute inset-0 w-full h-full object-cover rounded-lg transition-opacity duration-300 ease-in-out group-hover:opacity-75"
                                    />
                                    />
                                    <div
                                        class="absolute inset-0 bg-emerald-950/50 backdrop-blur-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 ease-in-out rounded-lg"
                                    >
                                        <div>
                                            <h2
                                                class="text-white font-semibold text-center"
                                            >
                                                Résidence Active
                                            </h2>
                                            <p
                                                class="text-white text-2xl font-bold text-center"
                                            >
                                                {{ activeResidence.type }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else class="">
                                <h2 class="text-xl font-bold mb-4">
                                    Aucune résidence active
                                </h2>
                                <p>
                                    Vous n'avez pas de résidence active en ce
                                    moment.
                                </p>
                            </div>

                            <!-- Liste des autres résidences -->
                            <h2 class="text-lg font-semibold py-4">
                                Toutes vos résidences
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div
                                    v-for="residence in nonActiveResidences"
                                    :key="
                                        residence.pivot
                                            ? residence.pivot.id
                                            : residence.id
                                    "
                                    @click="
                                        setActiveResidence(
                                            residence.pivot
                                                ? residence.pivot.id
                                                : residence.id
                                        )
                                    "
                                    class="relative bg-gray-300/30 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer"
                                >
                                    <img
                                        :src="residence.image_path"
                                        alt="Résidence"
                                        class="w-full h-full object-cover rounded-lg transition-opacity duration-300 ease-in-out group-hover:opacity-75"
                                    />
                                    <div
                                        class="absolute inset-0 flex bg-emerald-950/80 backdrop-blur-lg rounded-lg shadow-md opacity-0 hover:opacity-100 transition-opacity duration-300 ease-in-out"
                                    >
                                        <div
                                            class="flex flex-col justify-around p-4"
                                        >
                                            <h2
                                                class="font-semibold text-center text-lg lg:mt-6"
                                            >
                                                {{ residence.type }}
                                            </h2>

                                            <button
                                                class="mt-2 bg-red-700 text-white px-3 py-1 rounded hover:bg-red-900 transition-colors duration-300 font-semibold"
                                                @click.stop="
                                                    sellResidence(residence)
                                                "
                                            >
                                                Vendre
                                            </button>
                                            <div
                                                class="flex justify-around items-center gap-1 px-4"
                                            >
                                                <p class="text-xs">
                                                    Cliquer pour choisir comme
                                                    principale
                                                </p>

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    xmlns:xlink="http://www.w3.org/1999/xlink"
                                                    viewBox="0 0 256 256"
                                                    class="size-8 transition-opacity duration-300 ease-in-out"
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Inventaire -->
                    <div
                        class="w-full lg:w-2/3 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md order-1 lg:order-2"
                    >
                        <div class="flex items-center flex-wrap gap-4 mb-4">
                            <h2 class="text-lg">Inventaire</h2>
                            <input
                                type="text"
                                v-model="searchTerm"
                                placeholder="Rechercher des articles..."
                                class="text-sm border-gray-300 focus:border-amber-600 focus:ring-amber-600 rounded-md shadow-sm text-slate-900 font-semibold"
                            />
                            <button
                                class="py-2 px-4 text-gray-100 bg-amber-500 text-sm font-semibold rounded transition-all hover:shadow-lg hover:bg-amber-600"
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
                                    'py-2 px-4 rounded transition-transform duration-200 ease-in-out  hover:shadow-lg text-sm',
                                    selectedCategory.valueOf() === category
                                        ? 'bg-emerald-950 text-gray-100'
                                        : ' bg-white/70  text-gray-900 hover:text-gray-100 hover:bg-emerald-950 ',
                                ]"
                                @click="filterByCategory(category)"
                            >
                                {{ category }}
                            </button>
                        </div>
                        <div
                            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4 pb-4"
                        >
                            <div
                                v-for="item in filteredItems"
                                @click="consumeItem(item)"
                                :key="item.id"
                                class="relative bg-emerald-950 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg cursor-pointer"
                            >
                                <div class="p-4">
                                    <div class="place-self-center">
                                        <img
                                            :src="item.img_item"
                                            :alt="item.name"
                                            class="w-full h-full object-cover rounded-lg transition-all duration-300 ease-in-out hover:blur-sm"
                                        />
                                        <div
                                            class="absolute inset-0 flex flex-col items-center justify-evenly hover:bg-emerald-950/75 backdrop-blur-lg rounded-lg shadow-md opacity-0 hover:opacity-100 transition-opacity duration-300 ease-in-out"
                                        >
                                            <div class="lg:mt-6">
                                                <h4
                                                    class="text-xl font-bold text-center"
                                                >
                                                    {{ item.name }}
                                                </h4>
                                                <p class="text-sm">
                                                    {{ item.quantity }} en stock
                                                </p>
                                            </div>
                                            <div
                                                class="flex items-end justify-around gap-1 px-4"
                                            >
                                                <p class="text-xs">
                                                    Cliquer pour consommer
                                                </p>

                                                <svg
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    xmlns:xlink="http://www.w3.org/1999/xlink"
                                                    viewBox="0 0 256 256"
                                                    class="size-6 transition-opacity duration-300 ease-in-out"
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
