<script setup>
import { defineProps, reactive, computed, ref } from "vue";
import { router } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

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
        <div class="flex justify-center">
            <div class="flex flex-col gap-2 lg:gap-4 mx-auto">
                <div
                    class="flex flex-col lg:flex-row gap-2 lg:gap-4 mx-auto w-full"
                >
                    <div
                        class="flex-1 flex flex-col md:flex-auto w-full lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md order-2 md:order-1"
                    >
                        <div
                            class="flex flex-col tracking-wide leading-relaxed gap-2"
                        >
                            <h1 class="text-xl font-bold">
                                Bienvenue au LifeMarket
                            </h1>
                            <p class="">
                                Découvrez notre sélection de produits essentiels
                                conçus pour nourrir votre corps, rafraîchir
                                votre esprit et prendre soin de votre hygiène.
                                Notre magasin propose une gamme soigneusement
                                sélectionnée de nourriture, de boissons et de
                                savons pour répondre à vos besoins quotidiens et
                                vous offrir une expérience de shopping pratique
                                et satisfaisante.
                            </p>
                            <p class="">
                                Explorez nos rayons remplis de produits
                                alimentaires de qualité, des ingrédients de base
                                aux délices gourmands, pour créer des repas
                                savoureux et équilibrés qui raviront vos
                                papilles et nourriront votre corps. Laissez-vous
                                tenter par notre sélection de boissons
                                rafraîchissantes, des jus de fruits naturels aux
                                boissons énergisantes, pour étancher votre soif
                                et vous revitaliser tout au long de la journée.
                            </p>
                        </div>
                    </div>
                    <div
                        class="flex-1 md:flex-auto w-full md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900 order-1 md:order-2"
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
                    <div class="flex items-center flex-wrap gap-4 my-4">
                        <input
                            type="text"
                            v-model="searchTerm"
                            placeholder="Rechercher des produits..."
                            class="border text-sm focus:ring-1 focus:ring-gray-500 rounded shadow-sm bg-gray-300 text-slate-800"
                        />
                        <div class="flex flex-wrap gap-4">
                            <button
                                class="py-2 px-4 text-gray-100 bg-amber-500 text-sm font-semibold rounded transition-all hover:shadow-lg hover:bg-amber-600"
                                @click="selectedCategory = ''"
                            >
                                Tout
                            </button>
                            <button
                                v-for="category in Object.keys(
                                    productsByCategory
                                )"
                                :key="category"
                                @click="selectedCategory = category"
                                :class="[
                                    'py-2 px-4 rounded transition-all hover:shadow-lg text-sm',
                                    selectedCategory.valueOf() === category
                                        ? 'bg-emerald-950 text-gray-100'
                                        : ' bg-white/70  text-gray-900 hover:text-gray-100 hover:bg-emerald-950 ',
                                ]"
                            >
                                {{ category }}
                            </button>
                        </div>
                    </div>
                    <div
                        v-for="(
                            products, category
                        ) in filteredProductsByCategory"
                        :key="category"
                        class="mb-8"
                    >
                        <h3 class="text-lg font-semibold mb-4">
                            {{ category }}
                        </h3>
                        <div
                            class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4"
                        >
                            <div
                                v-for="product in products"
                                :key="product.id"
                                class="relative bg-emerald-950/50 hover:bg-emerald-950 p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:shadow-lg"
                            >
                                <div class="size-36 place-self-center">
                                    <img
                                        :src="product.img_item"
                                        alt="Product image"
                                        class="mb-3"
                                    />
                                </div>

                                <div class="flex-grow">
                                    <div
                                        class="flex items-end justify-between md:mb-2"
                                    >
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
                                <div
                                    class="mt-1 flex flex-col lg:flex-row gap-2 w-full pt-2"
                                >
                                    <input
                                        type="number"
                                        v-model.number="quantities[product.id]"
                                        min="1"
                                        :placeholder="`${
                                            product.inventoryQuantity || 0
                                        } en stock`"
                                        @keyup.enter="buyProduct(product.id)"
                                        class="text-sm w-full border-gray-300 focus:border-amber-600 focus:ring-amber-600 rounded-md shadow-sm text-slate-900 font-semibold"
                                    />

                                    <PrimaryButton
                                        @click="buyProduct(product.id)"
                                    >
                                        <p>Acheter</p>
                                    </PrimaryButton>
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
            </div>
        </div>
    </AppLayout>
</template>
