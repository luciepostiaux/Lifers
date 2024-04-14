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
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">Bienvenue au LifeMarket</h2>
            <p class="mb-4">
                Découvrez notre large gamme de produits destinés à améliorer la
                vie de votre Lifer. Que ce soit pour augmenter la santé, la joie
                ou satisfaire les besoins primaires, vous trouverez tout ce
                qu'il faut pour un mode de vie équilibré.
            </p>
            <div class="flex gap-4">
                <input
                    type="text"
                    v-model="searchTerm"
                    placeholder="Rechercher des produits..."
                    class="mb-4 p-2 border border-gray-300 focus:border-indigo-500 focus:ring-1 focus:ring-blue-500 rounded shadow-sm"
                />
                <div class="flex gap-4 mb-4">
                    <button
                        class="py-2 px-4 bg-indigo-500 text-white rounded hover:bg-indigo-600 transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                        @click="selectedCategory = ''"
                    >
                        Toutes
                    </button>
                    <button
                        v-for="category in Object.keys(productsByCategory)"
                        :key="category"
                        class="py-2 px-4 border-2 bg-white border-indigo-500 hover:text-white rounded hover:bg-indigo-600 transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                        @click="selectedCategory = category"
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
                    class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
                >
                    <div
                        v-for="product in products"
                        :key="product.id"
                        class="bg-white p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                    >
                        <div class="size-36">
                            <img
                                :src="product.img_item"
                                alt="Product image"
                                class="mb-3"
                            />
                        </div>

                        <div class="flex-grow">
                            <h4 class="text-md font-bold">
                                {{ product.name }}
                            </h4>
                            <!-- <p class="text-gray-600 mt-1">
                                {{ product.description }}
                            </p> -->
                            <div class="text-sm flex gap-1">
                                <p class="ml-1 font-semibold">
                                    {{ product.price }}
                                </p>
                                <p class="">LC</p>
                            </div>
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
                                class="mt-3 border-gray-300 rounded-md"
                            />

                            <button
                                @click="buyProduct(product.id)"
                                class="mt-3 bg-indigo-500 hover:bg-indigo-700 focus:outline-none focus:ring focus:ring-blue-300 active:bg-indigo-800 text-white font-bold py-2 px-4 rounded transition duration-150 ease-in-out"
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
