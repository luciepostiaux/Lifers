<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    money: [String, Number],
    cityStatus: { type: Object, required: true },
});

const page = usePage();
const journalPurchasePending = ref(false);

const purchaseJournal = () => {
    journalPurchasePending.value = true;
    router.post(route("city.journal.purchase"), {}, {
        preserveScroll: true,
        onFinish: () => {
            journalPurchasePending.value = false;
        },
    });
};

const destinations = computed(() => [
    {
        key: "market",
        kicker: "Boutiques",
        title: "LifeMarket",
        description: "Achète des objets utiles puis retrouve-les dans ton inventaire.",
        route: "city.lifemarket",
        action: "Entrer au LifeMarket",
        image: "/images/places/lifemarket.png",
        imageAlt: "LifeMarket de Lifers",
        status: `${props.cityStatus.market_items_count} article${props.cityStatus.market_items_count > 1 ? "s" : ""} · ${props.cityStatus.inventory_quantity} chez toi`,
        tone: "gold",
    },
    {
        key: "doctor",
        kicker: "Santé",
        title: "Hôpital",
        description: "Consulte le médecin et retrouve les soins liés aux maladies actives.",
        route: "doctor.index",
        action: "Aller à l’hôpital",
        image: "/images/places/hopital.png",
        imageAlt: "Hôpital de Lifers",
        status: props.cityStatus.sickness_count
            ? `${props.cityStatus.sickness_count} maladie${props.cityStatus.sickness_count > 1 ? "s" : ""} active${props.cityStatus.sickness_count > 1 ? "s" : ""}`
            : `Santé ${props.cityStatus.health}/100`,
        tone: props.cityStatus.sickness_count ? "rose" : "sage",
    },
    {
        key: "entertainment",
        kicker: "Temps libre",
        title: "Loisirs",
        description: "Choisis une activité pour améliorer le bonheur ou le divertissement.",
        route: "city.entertainment",
        action: "Voir les loisirs",
        image: "/images/places/loisir.png",
        imageAlt: "Lieu de loisirs de Lifers",
        status: `${props.cityStatus.activities_count} activité${props.cityStatus.activities_count > 1 ? "s" : ""}`,
        tone: "rose",
    },
    {
        key: "sport",
        kicker: "Bien-être",
        title: "Sport",
        description: "Accède aux séances ponctuelles et aux abonnements disponibles.",
        route: "city.sport",
        action: "Faire du sport",
        image: "/images/places/sport.png",
        imageAlt: "Centre sportif de Lifers",
        status:
            (props.cityStatus.active_subscription
                ? `Abonnement ${props.cityStatus.active_subscription} actif`
                : `${props.cityStatus.sport_options_count} formule${props.cityStatus.sport_options_count > 1 ? "s" : ""}`),
        tone: "sage",
    },
    {
        key: "orphanage",
        kicker: "Famille",
        title: "Orphelinat",
        description: "Rencontre les enfants sans foyer et découvre les possibilités d’adoption.",
        route: "city.orphanage",
        action: "Aller à l’orphelinat",
        image: "/images/places/orphelinat.png",
        imageAlt: "Orphelinat de Lifers",
        status: `${props.cityStatus.orphan_count} enfant${props.cityStatus.orphan_count > 1 ? "s" : ""} accueilli${props.cityStatus.orphan_count > 1 ? "s" : ""}`,
        tone: "rose",
    },
    {
        key: "study",
        kicker: "Progression",
        title: "Université",
        description: "Consulte les formations et poursuis ton étude actuelle.",
        route: "study.index",
        action: "Aller à l’université",
        image: "/images/places/universite.png",
        imageAlt: "Université de Lifers",
        status: props.cityStatus.current_study || "Aucune étude active",
        tone: "gold",
    },
    {
        key: "job",
        kicker: "Vie professionnelle",
        title: "Maison de l’emploi",
        description: "Découvre les métiers accessibles et gère ton poste actuel.",
        route: "job",
        action: "Voir les métiers",
        image: "/images/places/emploi.png",
        imageAlt: "Maison de l’emploi de Lifers",
        status: props.cityStatus.current_job || "Aucun métier actif",
        tone: "plum",
    },
]);
</script>

<template>
    <AppLayout title="Ville" :money="money">
        <div class="path-page city-page">
            <div v-if="page.props.errors?.journal" class="path-feedback" role="alert">
                {{ page.props.errors.journal }}
            </div>
            <section class="city-hero" aria-labelledby="city-title">
                <div class="city-hero__copy">
                    <span class="path-kicker">LifeCity</span>
                    <h1 id="city-title">La ville t’attend</h1>
                    <p>
                        Retrouve les services utiles à la vie de ton Lifer et
                        poursuis son histoire dans les différents quartiers.
                    </p>
                    <div class="city-hero__stats" aria-label="Résumé de la ville">
                        <span><strong>7</strong> lieux accessibles</span>
                        <span><strong>{{ cityStatus.health }}/100</strong> santé actuelle</span>
                        <span><strong>{{ cityStatus.inventory_quantity }}</strong> objets chez toi</span>
                    </div>
                    <Link
                        v-if="cityStatus.has_daily_journal_access"
                        :href="route('city.journal.index')"
                        class="city-journal-button"
                    >
                        Lire le journal
                    </Link>
                    <button
                        v-else
                        type="button"
                        class="city-journal-button"
                        :disabled="journalPurchasePending || Number(money) < 1"
                        @click="purchaseJournal"
                    >
                        {{ journalPurchasePending ? "Achat…" : "Acheter le journal" }}
                        <small>1 Lif’coin</small>
                    </button>
                </div>
                <div class="city-hero__visual">
                    <img
                        src="/images/landing/hero-lifers.png"
                        alt="Deux Lifers dans la ville"
                        decoding="async"
                    />
                </div>
            </section>

            <section class="city-directory" aria-labelledby="city-directory-title">
                <div class="city-directory__heading">
                    <div>
                        <span class="path-kicker">Se déplacer</span>
                        <h2 id="city-directory-title">Où veux-tu aller ?</h2>
                    </div>
                    <p>Chaque lieu ouvre une fonctionnalité déjà disponible dans le jeu.</p>
                </div>

                <div class="city-grid">
                    <article
                        v-for="destination in destinations"
                        :key="destination.key"
                        class="city-card"
                        :class="`city-card--${destination.tone}`"
                    >
                        <div class="city-card__visual" :class="{ 'city-card__visual--letter': !destination.image }">
                            <img
                                v-if="destination.image"
                                :src="destination.image"
                                :alt="destination.imageAlt"
                                loading="lazy"
                            />
                            <span v-else aria-hidden="true">{{ destination.monogram }}</span>
                        </div>

                        <div class="city-card__content">
                            <div>
                                <span class="path-kicker">{{ destination.kicker }}</span>
                                <h3>{{ destination.title }}</h3>
                                <p>{{ destination.description }}</p>
                            </div>

                            <div class="city-card__status">
                                <span aria-hidden="true"></span>
                                <strong>{{ destination.status }}</strong>
                            </div>

                            <Link
                                :href="route(destination.route)"
                                class="path-button path-button--secondary path-button--full"
                            >
                                {{ destination.action }}
                            </Link>
                        </div>
                    </article>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
