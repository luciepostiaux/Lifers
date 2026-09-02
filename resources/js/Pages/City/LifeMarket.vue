<script setup>
import { computed, reactive, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    productsByCategory: { type: Object, default: () => ({}) },
    money: [String, Number],
});

const page = usePage();
const quantities = reactive({});
const purchasingProductId = ref(null);

Object.values(props.productsByCategory ?? {})
    .flat()
    .forEach((product) => {
        quantities[product.id] = 1;
    });

const gaugeLabels = {
    hunger: "Faim",
    thirst: "Soif",
    clean: "Propreté",
    happiness: "Bonheur",
    entertainment: "Divertissement",
    physical_condition: "Condition physique",
    health: "Santé",
};

const categoryEntries = computed(() =>
    Object.entries(props.productsByCategory ?? {}),
);

const products = computed(() =>
    categoryEntries.value.flatMap(([, categoryProducts]) => categoryProducts),
);

const inventoryQuantity = computed(() =>
    products.value.reduce(
        (total, product) => total + Number(product.inventory_quantity || 0),
        0,
    ),
);

const feedbackMessage = computed(
    () =>
        page.props.flash?.message ??
        page.props.flash?.success ??
        page.props.errors?.productId ??
        page.props.errors?.quantity,
);

const quantityFor = (productId) => {
    const quantity = Number(quantities[productId] ?? 1);

    return Number.isInteger(quantity) ? Math.min(100, Math.max(1, quantity)) : 1;
};

const totalPrice = (product) => Number(product.price) * quantityFor(product.id);
const canAfford = (product) => Number(props.money) >= totalPrice(product);

const formatAmount = (amount) =>
    new Intl.NumberFormat("fr-FR", { maximumFractionDigits: 2 }).format(
        Number(amount),
    );

const effectLabel = (effect) =>
    `${effect.effect > 0 ? "+" : ""}${effect.effect} ${gaugeLabels[effect.gauge] || effect.gauge}`;

const buyProduct = (product) => {
    purchasingProductId.value = product.id;
    router.post(
        route("purchase"),
        {
            productId: product.id,
            quantity: quantityFor(product.id),
        },
        {
            preserveScroll: true,
            onFinish: () => (purchasingProductId.value = null),
        },
    );
};
</script>

<template>
    <AppLayout title="LifeMarket" :money="money">
        <div class="path-page service-page">
            <Link :href="route('city')" class="path-back-link">
                <span aria-hidden="true">←</span> Retour à la ville
            </Link>

            <div v-if="feedbackMessage" class="path-feedback" role="status">
                {{ feedbackMessage }}
            </div>

            <section class="service-hero service-hero--market" aria-labelledby="market-title">
                <div class="service-hero__copy">
                    <span class="path-kicker">Boutiques</span>
                    <h1 id="market-title">LifeMarket</h1>
                    <p>
                        Choisis la quantité souhaitée et vérifie les effets avant
                        d’ajouter un objet à l’inventaire de ton Lifer.
                    </p>
                    <div class="service-hero__stats">
                        <span><strong>{{ products.length }}</strong> articles</span>
                        <span><strong>{{ categoryEntries.length }}</strong> catégories</span>
                        <span><strong>{{ inventoryQuantity }}</strong> objets déjà possédés</span>
                    </div>
                </div>
                <div class="service-hero__visual">
                    <img src="/images/places/lifemarket.png" alt="LifeMarket de Lifers" decoding="async" />
                </div>
            </section>

            <section class="service-catalog" aria-labelledby="market-catalog-title">
                <div class="service-catalog__heading">
                    <div>
                        <span class="path-kicker">Catalogue</span>
                        <h2 id="market-catalog-title">Objets disponibles</h2>
                    </div>
                    <p>Le prix final dépend de la quantité choisie et de ton solde actuel.</p>
                </div>

                <div v-if="categoryEntries.length" class="service-categories">
                    <section
                        v-for="([category, categoryProducts]) in categoryEntries"
                        :key="category"
                        class="service-category"
                        :aria-labelledby="`market-category-${category}`"
                    >
                        <h3 :id="`market-category-${category}`">{{ category }}</h3>
                        <div class="service-grid">
                            <article v-for="product in categoryProducts" :key="product.id" class="service-card">
                                <div class="service-card__visual">
                                    <img
                                        v-if="product.image_path"
                                        :src="product.image_path"
                                        :alt="`Illustration de ${product.name}`"
                                        loading="lazy"
                                    />
                                    <span v-else aria-hidden="true">{{ category.charAt(0) }}</span>
                                    <strong>{{ product.inventory_quantity }} possédé{{ product.inventory_quantity > 1 ? "s" : "" }}</strong>
                                </div>

                                <div class="service-card__content">
                                    <div>
                                        <span class="path-kicker">{{ category }}</span>
                                        <h4>{{ product.name }}</h4>
                                        <p>{{ product.description }}</p>
                                        <small v-if="product.units_per_purchase > 1" class="service-card__package">
                                            {{ product.units_per_purchase }} unités ajoutées à l’inventaire par boîte achetée.
                                        </small>
                                    </div>

                                    <div v-if="product.effects.length" class="service-effects" aria-label="Effets de l’objet">
                                        <span v-for="effect in product.effects" :key="`${product.id}-${effect.gauge}`">
                                            {{ effectLabel(effect) }}
                                        </span>
                                    </div>

                                    <div class="service-purchase">
                                        <div class="service-purchase__price">
                                            <span>Prix total</span>
                                            <strong>{{ formatAmount(totalPrice(product)) }} Lif’coins</strong>
                                        </div>
                                        <label>
                                            <span>Quantité</span>
                                            <input
                                                v-model.number="quantities[product.id]"
                                                type="number"
                                                min="1"
                                                max="100"
                                                inputmode="numeric"
                                                :aria-label="`Quantité de ${product.name}`"
                                            />
                                        </label>
                                    </div>

                                    <button
                                        type="button"
                                        class="path-button path-button--primary path-button--full"
                                        :disabled="purchasingProductId !== null || !canAfford(product)"
                                        @click="buyProduct(product)"
                                    >
                                        {{ purchasingProductId === product.id ? "Achat…" : canAfford(product) ? "Acheter" : "Solde insuffisant" }}
                                    </button>
                                </div>
                            </article>
                        </div>
                    </section>
                </div>

                <div v-else class="path-empty">
                    <p>Aucun article n’est disponible pour le moment.</p>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
