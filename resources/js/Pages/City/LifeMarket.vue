<script setup>
import { defineProps, reactive } from "vue";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    productsByCategory: Object,
});

const quantities = reactive({});

const buyProduct = (productId) => {
    Inertia.post("/purchase", {
        productId,
        quantity: quantities[productId] || 1,
    });
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

            <div
                v-for="(products, category) in productsByCategory"
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
                        class="bg-white p-4 rounded-lg shadow-md flex flex-col"
                    >
                        <div class="flex-grow">
                            <h4 class="text-md font-semibold">
                                {{ product.name }}
                            </h4>
                            <p class="text-gray-600 mt-1">
                                {{ product.description }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <div class="text-lg font-bold">
                                Prix: {{ product.price }} Lifers' coins
                            </div>
                            <input
                                type="number"
                                v-model.number="quantities[product.id]"
                                min="1"
                                class="mt-3 border-gray-300 rounded-md"
                            />
                            <button
                                @click="buyProduct(product.id)"
                                class="mt-3 bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Acheter
                            </button>
                        </div>
                        <span class="mt-2"
                            >Dans l'inventaire:
                            {{ product.inventoryQuantity || 0 }}</span
                        >
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
