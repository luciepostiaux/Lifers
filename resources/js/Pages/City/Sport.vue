<script setup>
import { defineProps, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    sportSessions: Array,
    activeSubscription: Object,
});

const buySingleSession = () => {
    router.post("/city/buy-single-sport-session", { sessionId: "single" });
};

const subscribeToGym = (sessionName) => {
    router.post("/city/subscribe-to-gym", { subscriptionName: sessionName });
};

const cancelGymSubscription = () => {
    router.post("/city/cancel-gym-subscription");
};

const isActiveSubscription = (name) => {
    const isActive =
        props.activeSubscription &&
        props.activeSubscription.name === name &&
        props.activeSubscription.status === "active";
    console.log(`IsActive for ${name}: ${isActive}`);
    return isActive;
};

const hasActiveSubscription = computed(() => {
    return (
        props.activeSubscription && props.activeSubscription.status === "active"
    );
});
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
                        {{ session.name }}
                    </h3>
                    <p class="mb-4">Prix: {{ session.price }} Lifers' coins</p>
                    <p class="mb-4">
                        Renouvellement tous les: {{ session.duration }} jours
                    </p>
                    <p class="mb-4">
                        Effet: +{{ session.effect }} condition physique
                    </p>
                    <button
                        :disabled="
                            hasActiveSubscription &&
                            !isActiveSubscription(session.name)
                        "
                        :class="[
                            'font-bold py-2 px-4 rounded text-white',
                            hasActiveSubscription &&
                            !isActiveSubscription(session.name)
                                ? 'bg-gray-500 hover:bg-gray-500 cursor-not-allowed'
                                : isActiveSubscription(session.name)
                                ? 'bg-red-500 hover:bg-red-700'
                                : 'bg-blue-500 hover:bg-blue-700',
                        ]"
                        @click="
                            hasActiveSubscription &&
                            isActiveSubscription(session.name)
                                ? cancelGymSubscription()
                                : subscribeToGym(session.name)
                        "
                    >
                        <span v-if="isActiveSubscription(session.name)"
                            >Annuler abonnement actif</span
                        >
                        <span v-else>Souscrire</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
