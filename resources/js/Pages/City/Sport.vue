<script setup>
import { defineProps } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    sportSessions: Array,
    activeSubscription: Object,
});

const subscribeToGym = (sessionType) => {
    Inertia.post("/city/subscribe-to-gym", { subscriptionType: sessionType });
};

const cancelGymSubscription = () => {
    Inertia.post("/city/cancel-gym-subscription");
};
</script>

<template>
    <AppLayout title="Sport">
        <template #header></template>
        <div class="container mx-auto p-4">
            <h2 class="text-2xl font-bold mb-4">Séances de Sport</h2>

            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-2">Séance à l'unité</h3>
                <p class="mb-4">Prix: 40 Lifers' coins</p>
                <p class="mb-4">Effet: +15 condition physique</p>
                <button
                    @click="buySingleSession"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Acheter une séance à l'unité
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="session in sportSessions"
                    :key="session.id"
                    class="bg-white p-6 rounded-lg shadow"
                >
                    <h3 class="text-lg font-semibold mb-2">
                        {{ session.type }}
                    </h3>
                    <p class="mb-4">Prix: {{ session.price }} Lifers' coins</p>
                    <p class="mb-4">
                        Renouvellement tous les: {{ session.duration }} jours
                    </p>
                    <p class="mb-4">
                        Effet: +{{ session.effect }} condition physique
                    </p>
                    <button
                        v-if="
                            activeSubscription &&
                            activeSubscription.type === session.type &&
                            activeSubscription.status === 'active'
                        "
                        @click="cancelGymSubscription"
                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Abonnement Actif - Annuler
                    </button>
                    <button
                        v-else
                        @click="subscribeToGym(session.type)"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                    >
                        Souscrire
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
