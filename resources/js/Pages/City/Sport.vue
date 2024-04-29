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
        <div class="flex flex-col md:flex-row mb-4 w-full h-full gap-4">
            <div
                class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
            >
                <div class="flex flex-col tracking-wide leading-relaxed gap-2">
                    <h1 class="text-xl font-bold mb-4">Séances de Sport</h1>
                    <p class="">
                        Découvrez notre salle de sport moderne, où vous pouvez
                        vous entraîner selon vos besoins et votre emploi du
                        temps. Nous proposons des options flexibles pour
                        répondre à tous les modes de vie, que vous préfériez
                        payer à l'unité ou opter pour un abonnement adapté à vos
                        besoins.
                    </p>
                    <p class="">
                        Quel que soit l'abonnement que vous choisissez, vous
                        bénéficierez d'un environnement accueillant et motivant,
                        ainsi que du soutien de notre équipe d'entraîneurs
                        professionnels pour vous aider à atteindre vos objectifs
                        de fitness. Rejoignez-nous dès aujourd'hui et commencez
                        votre voyage vers une vie plus saine et plus active.
                    </p>
                </div>
                <div
                    class="bg-emerald-950 p-4 rounded-lg shadow-lg flex justify-between mt-6"
                >
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold mb-2">
                            Séance à l'unité
                        </h3>
                        <div class="flex space-x-4 text-sm">
                            <p class="">40 Lifers'Coins (LC)</p>
                            <p class="">+15 condition physique</p>
                        </div>
                    </div>
                    <div>
                        <button
                            @click="buySingleSession"
                            class="inline-flex items-center justify-center px-4 py-2 bg-emerald-900/80 hover:bg-emerald-900 border border-transparent rounded-md font-semibold text-xs percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105"
                        >
                            Acheter une séance à l'unité
                        </button>
                    </div>
                </div>
            </div>
            <div
                class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900"
            >
                <img
                    src="/images/places/gym_4-6.webp"
                    alt="University Image"
                    class="object-cover h-full"
                />
            </div>
        </div>
        <div
            class="bg-emerald-900/90 backdrop-blur-md p-4 rounded-lg shadow-md"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="session in sportSessions"
                    :key="session.id"
                    class="bg-emerald-950/50 hover:bg-emerald-950 backdrop-blur-md p-4 rounded-lg shadow-md flex flex-col transition-transform duration-200 ease-in-out hover:scale-105 hover:shadow-lg"
                >
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold mb-2 capitalize">
                            {{ session.name }}
                        </h3>
                        <div class="flex space-x-4 text-sm">
                            <p class="mb-4">{{ session.price }} LC</p>
                            <p class="mb-4">
                                Renouvellement tous les:
                                {{ session.duration }} jours
                            </p>
                        </div>
                    </div>
                    <!-- <p class="mb-4">
                        Effet: +{{ session.effect }} condition physique
                    </p> -->
                    <button
                        :disabled="
                            hasActiveSubscription &&
                            !isActiveSubscription(session.name)
                        "
                        :class="[
                            'inline-flex items-center justify-center py-2 m-2 mt-4 bg-emerald-900/80 hover:bg-emerald-900 border border-transparent rounded-md font-semibold text-xs percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105',
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
