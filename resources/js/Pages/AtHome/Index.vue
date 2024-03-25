<script setup>
import { defineProps } from "vue";
import { Inertia } from "@inertiajs/inertia";
import AppLayout from "@/Layouts/AppLayout.vue";

// Définir les props ici
const props = defineProps({
    lifeGauges: Object,
    inventoryItemsByCategory: Object,
});
const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-500";
    else if (value <= 50) return "bg-orange-500";
    return "bg-green-500";
};
const consumeItem = (item) => {
    // Envoyer une requête pour consommer l'item

    Inertia.post(
        "/consume-item",
        {
            itemId: item.id, // Assurez-vous que 'item.id' est correct et non null
        },
        {
            preserveState: false, // Cela rafraîchira les données après la requête
        }
    );
};
</script>

<template>
    <AppLayout title="AtHome">
        <div class="container mx-auto p-4">
            <h1>Votre Espace Personnel</h1>
        </div>
        <div
            class="bg-white flex flex-col p-4 rounded-lg shadow-md mb-4 md:mb-0"
        >
            <h3 class="font-semibold pb-2">Jauges de vie</h3>

            <div v-for="(value, key) in lifeGauges" :key="key" class="mb-4">
                <div>{{ key }}</div>
                <div class="w-64 bg-gray-200 rounded-full h-4">
                    <div
                        :class="gaugeColor(value)"
                        :style="{ width: `${value}%` }"
                        class="h-4 rounded-full text-center"
                    ></div>
                </div>
            </div>
            <h2 class="text-2xl font-semibold mb-4">Inventaire</h2>
            <div
                v-for="(items, category) in inventoryItemsByCategory"
                :key="category"
            >
                <h3 class="text-lg font-semibold mb-2">{{ category }}</h3>
                <div
                    class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4"
                >
                    <div
                        v-for="item in items"
                        :key="item.id"
                        class="bg-white p-4 rounded-lg shadow-md flex flex-col"
                    >
                        <div class="flex-grow">
                            <h4 class="text-md font-semibold">
                                {{ item.name }}
                            </h4>
                            <p class="text-gray-600 mt-1">
                                Quantité: {{ item.quantity }}
                            </p>
                        </div>
                        <div class="mt-4">
                            <button
                                @click="consumeItem(item)"
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                            >
                                Consommer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
