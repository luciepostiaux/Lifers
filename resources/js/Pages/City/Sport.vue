<script setup>
import { defineProps, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
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
        <div
            class="flex flex-col md:flex-row mb-4 w-full h-full gap-2 lg:gap-4"
        >
            <div
                class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md order-2 md:order-1"
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
                    class="bg-emerald-950/50 backdrop-blur-lg p-4 rounded-lg shadow-lg flex flex-col md:flex-row gap-2 lg:gap-4 justify-between mt-6"
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
                    <div class="text-center">
                        <PrimaryButton @click="buySingleSession">
                            Acheter une séance à l'unité
                        </PrimaryButton>
                    </div>
                </div>
            </div>
            <div
                class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900 order-1 md:order-2"
            >
                <img
                    src="/images/places/gym_4-6.webp"
                    alt="University Image"
                    class="object-cover h-full"
                />
            </div>
        </div>
        <div
            class="bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div
                    v-for="session in sportSessions"
                    :key="session.id"
                    class="bg-emerald-950/50 p-4 rounded-lg shadow-md flex flex-col transition-all hover:shadow-lg"
                >
                    <div class="space-y-2 mb-4">
                        <h3 class="text-lg font-semibold mb-4 capitalize">
                            {{ session.name }}
                        </h3>
                        <p>
                            {{ session.price }} LC / tous les
                            {{ session.duration }} jours
                        </p>
                    </div>
                    <!-- Boutons d'inscription ou d'annulation -->
                    <button
                        :disabled="
                            hasActiveSubscription &&
                            !isActiveSubscription(session.name)
                        "
                        :class="[
                            'inline-flex items-center justify-center py-2 mt-2 text-sm font-semibold px-4 rounded',
                            hasActiveSubscription &&
                            !isActiveSubscription(session.name)
                                ? 'text-amber-500 bg-transparent '
                                : isActiveSubscription(session.name)
                                ? 'bg-red-500 text-white'
                                : 'bg-amber-500 text-white hover:bg-amber-600',
                        ]"
                        @click="
                            isActiveSubscription(session.name)
                                ? cancelGymSubscription()
                                : subscribeToGym(session.name)
                        "
                    >
                        <span v-if="isActiveSubscription(session.name)"
                            >Annuler l'abonnement en cours</span
                        >
                        <span v-else-if="hasActiveSubscription"
                            >Déjà un abonnement en cours</span
                        >
                        <span v-else>Souscrire</span>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
