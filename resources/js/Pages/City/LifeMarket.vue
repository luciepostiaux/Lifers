<script setup>
import { defineProps, reactive, computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    productsByCategory: Object,
});

const quantities = reactive({});
const searchTerm = ref("");
const selectedCategory = ref("");

const filteredProductsByCategory = computed(() => {
    let filtered = props.productsByCategory;

    if (searchTerm.value) {
        const lowerSearchTerm = searchTerm.value.toLowerCase();
        filtered = Object.fromEntries(
            Object.entries(filtered).map(([category, products]) => [
                category,
                products.filter((product) =>
                    product.name.toLowerCase().includes(lowerSearchTerm)
                ),
            ])
        );
    }

    if (selectedCategory.value) {
        filtered = {
            [selectedCategory.value]: filtered[selectedCategory.value] || [],
        };
    }

    return filtered;
});

const buyProduct = (productId) => {
    router.post(
        "/purchase",
        {
            productId,
            quantity: quantities[productId] || 1,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
    quantities[productId] = null;
};
</script>

<template>
    <AppLayout title="LifeMarket">
        <template #header></template>
        <div class="flex flex-col md:flex-row mb-4 w-full h-full gap-4">
            <div
                class="flex-1 flex flex-col md:flex-auto md:w-3/5 lg:w-3/5 bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
            >
                <div class="flex flex-col tracking-wide leading-relaxed gap-2">
                    <h1 class="text-xl font-bold mb-4">
                        Bienvenue au LifeMarket
                    </h1>
                    <p class="">
                        Découvrez notre sélection de produits essentiels conçus
                        pour nourrir votre corps, rafraîchir votre esprit et
                        prendre soin de votre hygiène. Notre magasin propose une
                        gamme soigneusement sélectionnée de nourriture, de
                        boissons et de savons pour répondre à vos besoins
                        quotidiens et vous offrir une expérience de shopping
                        pratique et satisfaisante.
                    </p>
                    <p class="">
                        Explorez nos rayons remplis de produits alimentaires de
                        qualité, des ingrédients de base aux délices gourmands,
                        pour créer des repas savoureux et équilibrés qui
                        raviront vos papilles et nourriront votre corps.
                        Laissez-vous tenter par notre sélection de boissons
                        rafraîchissantes, des jus de fruits naturels aux
                        boissons énergisantes, pour étancher votre soif et vous
                        revitaliser tout au long de la journée.
                    </p>
                </div>
            </div>
            <div
                class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900"
            >
                <img
                    src="/images/places/market_4-6.webp"
                    alt="University Image"
                    class="object-cover h-full"
                />
            </div>
        </div>
        <div
            class="bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
        >
            <div class="flex items-center flex-wrap gap-4 my-4">
                <input
                    type="text"
                    v-model="searchTerm"
                    placeholder="Rechercher des produits..."
                    class="border text-sm focus:ring-1 focus:ring-gray-500 rounded shadow-sm bg-gray-300 text-slate-800"
                />
                <div class="flex gap-4">
                    <button
                        class="py-2 px-4 text-gray-100 bg-emerald-950/50 text-sm font-semibold rounded transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg hover:bg-emerald-950"
                        @click="selectedCategory = ''"
                    >
                        Tout
                    </button>
                    <button
                        v-for="category in Object.keys(productsByCategory)"
                        :key="category"
                        @click="selectedCategory = category"
                        :class="[
                            'py-2 px-4 rounded transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg text-sm',
                            selectedCategory.valueOf() === category
                                ? 'bg-gray-300 text-gray-100'
                                : ' bg-white/70  text-gray-900 hover:text-gray-100 hover:bg-emerald-950 ',
                        ]"
                    >
                        {{ category }}
                    </button>
                </div>
            </div>
            <div
                v-for="(products, category) in filteredProductsByCategory"
                :key="category"
                class="mb-8"
            >
                <h3 class="text-lg font-semibold mb-4">{{ category }}</h3>
                <div
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4"
                >
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="relative bg-emerald-950/50 hover:bg-emerald-950 backdrop-blur-md p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                    >
                        <div class="size-36 place-self-center">
                            <img
                                :src="product.img_item"
                                alt="Product image"
                                class="mb-3"
                            />
                        </div>

                        <div class="flex-grow">
                            <div class="flex items-end justify-between md:mb-2">
                                <h4 class="text-md font-bold">
                                    {{ product.name }}
                                </h4>
                                <div class="text-sm flex gap-1 mr-2">
                                    <p class="font-semibold">
                                        {{ product.price }}
                                    </p>
                                    <p class="">LC</p>
                                </div>
                            </div>
                            <p class="text-sm">
                                {{ product.description }}
                            </p>
                        </div>
                        <div class="mt-1 flex flex-col">
                            <!-- <input
                                    type="number"
                                    v-model.number="quantities[product.id]"
                                    min="1"
                                    class="mt-3 border-gray-300 rounded-md"
                                /> -->
                            <input
                                type="number"
                                v-model.number="quantities[product.id]"
                                min="1"
                                :placeholder="`${
                                    product.inventoryQuantity || 0
                                } en stock`"
                                @keyup.enter="buyProduct(product.id)"
                                class="mt-3 bg-gray-300 rounded-md text-sm"
                            />

                            <button
                                @click="buyProduct(product.id)"
                                class="inline-flex items-center justify-center py-2 m-2 mt-4 bg-emerald-900/80 hover:bg-emerald-900 border border-transparent rounded-md font-semibold text-xs percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                            >
                                Acheter
                            </button>
                        </div>
                        <!-- <span
                                class="mt-2 flex justify-center text-xs font-semibold"
                            >
                                {{ product.inventoryQuantity || 0 }} en stock</span
                            > -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
