<script setup>
import { computed, ref } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    money: [String, Number],
    children: { type: Array, default: () => [] },
    spouse: { type: Object, default: null },
    adoptionsRemaining: { type: Number, required: true },
});

const page = usePage();
const adoptingChildId = ref(null);
const feedback = computed(() =>
    page.props.flash?.success ?? page.props.errors?.child,
);

function adopt(child) {
    const household = props.spouse
        ? `ton couple avec ${props.spouse.name}`
        : "le foyer de ton Lifer";

    if (!window.confirm(`Adopter ${child.name} dans ${household} ? Cette décision crée un lien de garde permanent.`)) {
        return;
    }

    adoptingChildId.value = child.id;
    router.post(
        route("city.orphanage.adopt", child.id),
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                adoptingChildId.value = null;
            },
        },
    );
}
</script>

<template>
    <AppLayout title="Orphelinat" :money="money">
        <main class="path-page service-page">
            <Link :href="route('city')" class="path-back-link">
                <span aria-hidden="true">←</span> Retour à la ville
            </Link>

            <p v-if="feedback" class="path-feedback" role="status">{{ feedback }}</p>

            <section class="service-hero service-hero--orphanage" aria-labelledby="orphanage-title">
                <div class="service-hero__copy">
                    <span class="path-kicker">LifeCity · Famille</span>
                    <h1 id="orphanage-title">L’orphelinat</h1>
                    <p>
                        Les enfants privés de foyer restent ici jusqu’à leur adoption
                        ou leur passage à l’âge adulte.
                    </p>
                    <div class="service-hero__stats">
                        <span><strong>{{ adoptionsRemaining }}/3</strong> adoptions encore possibles</span>
                        <span><strong>{{ children.length }}</strong> enfant{{ children.length > 1 ? "s" : "" }} accueilli{{ children.length > 1 ? "s" : "" }}</span>
                        <span><strong>{{ spouse ? "En couple" : "Individuelle" }}</strong> adoption</span>
                    </div>
                </div>
                <div class="service-hero__visual">
                    <img
                        src="/images/places/orphelinat.png"
                        alt="Orphelinat de Lifers"
                        decoding="async"
                    />
                </div>
            </section>

            <section class="orphanage-panel" aria-labelledby="orphanage-children-title">
                <header>
                    <div>
                        <span class="orphanage-kicker">En attente d’un foyer</span>
                        <h2 id="orphanage-children-title">Les enfants accueillis</h2>
                    </div>
                    <p v-if="spouse">
                        Une adoption ajoutera automatiquement {{ spouse.name }} comme second parent adoptif.
                    </p>
                    <p v-else>L’adoption sera rattachée uniquement à ton Lifer.</p>
                </header>

                <div v-if="children.length" class="orphanage-grid">
                    <article v-for="child in children" :key="child.id" class="orphanage-card">
                        <span class="orphanage-avatar" aria-hidden="true">{{ child.name.charAt(0) }}</span>
                        <div class="orphanage-card__identity">
                            <h3>{{ child.name }}</h3>
                            <p>{{ child.age }} an(s)</p>
                        </div>
                        <div v-if="child.gauges" class="orphanage-needs">
                            <span>Nourriture <strong>{{ child.gauges.hunger }}/100</strong></span>
                            <span>Hygiène <strong>{{ child.gauges.hygiene }}/100</strong></span>
                            <span>Affection <strong>{{ child.gauges.affection }}/100</strong></span>
                        </div>
                        <button
                            type="button"
                            :disabled="adoptionsRemaining === 0 || adoptingChildId !== null"
                            @click="adopt(child)"
                        >
                            {{ adoptingChildId === child.id ? "Adoption…" : adoptionsRemaining === 0 ? "Limite atteinte" : "Adopter" }}
                        </button>
                    </article>
                </div>

                <div v-else class="orphanage-empty">
                    <span aria-hidden="true">♡</span>
                    <div>
                        <h3>Aucun enfant n’attend de foyer</h3>
                        <p>L’orphelinat est vide pour le moment.</p>
                    </div>
                </div>
            </section>
        </main>
    </AppLayout>
</template>

<style scoped>
.orphanage-panel{padding:clamp(25px,3.5vw,46px);border:1px solid rgb(70 50 78/8%);border-radius:24px;background:#f8f3ec;box-shadow:0 14px 34px rgb(70 50 78/8%)}.orphanage-panel>header{display:flex;margin-bottom:28px;align-items:flex-end;justify-content:space-between;gap:25px}.orphanage-kicker{color:#6f927b;font-size:12px;font-weight:800;letter-spacing:.14em;text-transform:uppercase}.orphanage-panel h2,.orphanage-card h3,.orphanage-empty h3{font-family:"Bricolage Grotesque",ui-sans-serif,system-ui,sans-serif;letter-spacing:-.035em}.orphanage-panel h2{margin:6px 0 0;font-size:clamp(31px,4vw,47px);line-height:1}.orphanage-panel>header>p{max-width:420px;margin:0;color:#8d7c8f;line-height:1.5}.orphanage-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.orphanage-card{display:grid;padding:22px;border-radius:19px;gap:16px;background:#f2e5e3}.orphanage-avatar{display:grid;width:52px;height:52px;border-radius:17px;place-items:center;color:#fff;background:#6f927b;font-family:"Bricolage Grotesque",sans-serif;font-size:23px;font-weight:800}.orphanage-card h3{margin:0;font-size:25px}.orphanage-card p{margin:3px 0 0;color:#8d7c8f}.orphanage-needs{display:grid;gap:7px}.orphanage-needs span{display:flex;padding:8px 10px;border-radius:9px;justify-content:space-between;background:rgb(255 250 244/62%);font-size:12px}.orphanage-card button{min-height:45px;border:0;border-radius:12px;color:#46324e;background:#d6a84a;font-weight:800;cursor:pointer}.orphanage-card button:disabled{cursor:not-allowed;opacity:.5}.orphanage-empty{display:flex;min-height:150px;padding:28px;border:1px dashed rgb(70 50 78/18%);border-radius:18px;align-items:center;gap:20px;background:#fffaf4}.orphanage-empty>span{color:#d6a84a;font-size:38px}.orphanage-empty h3{margin:0 0 5px;font-size:25px}.orphanage-empty p{margin:0;color:#8d7c8f}.orphanage-card button:focus-visible{outline:3px solid rgb(111 146 123/45%);outline-offset:3px}
@media(max-width:980px){.orphanage-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.orphanage-panel>header{align-items:flex-start;flex-direction:column}}
@media(max-width:680px){.orphanage-grid{grid-template-columns:1fr}.orphanage-panel{border-radius:19px}}
</style>
