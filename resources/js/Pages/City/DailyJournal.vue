<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";

const props = defineProps({
    money: [String, Number],
    editionDate: { type: String, required: true },
    deaths: { type: Array, default: () => [] },
});

const formattedEditionDate = computed(() =>
    new Intl.DateTimeFormat("fr-FR", {
        weekday: "long",
        day: "numeric",
        month: "long",
        year: "numeric",
    }).format(new Date(`${props.editionDate}T12:00:00`)),
);

const formattedTime = (date) =>
    new Intl.DateTimeFormat("fr-FR", {
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(date));

const identityClass = (death) =>
    death.sex === "female" ? "daily-paper__identity--female" : "daily-paper__identity--male";
</script>

<template>
    <AppLayout title="Le journal de Lifers" :money="money">
        <div class="path-page daily-paper-page">
            <div class="daily-paper__toolbar">
                <Link :href="route('city')" class="path-text-link">← Retourner en ville</Link>
                <span>Édition achetée pour aujourd’hui</span>
            </div>

            <article class="daily-paper" aria-labelledby="daily-paper-title">
                <header class="daily-paper__masthead">
                    <span>Le quotidien de LifeCity</span>
                    <h1 id="daily-paper-title">Le Journal des Lifers</h1>
                    <time :datetime="editionDate">{{ formattedEditionDate }}</time>
                </header>

                <section class="daily-paper__section" aria-labelledby="obituaries-title">
                    <div class="daily-paper__section-title">
                        <span>Vie de la cité</span>
                        <h2 id="obituaries-title">Nécrologie</h2>
                        <p>La ville se souvient des Lifers disparus aujourd’hui.</p>
                    </div>

                    <div v-if="deaths.length" class="daily-paper__deaths">
                        <article v-for="death in deaths" :key="death.key" class="daily-paper__notice">
                            <div>
                                <span class="daily-paper__symbol" :aria-label="death.sex === 'female' ? 'Féminin' : 'Masculin'">
                                    {{ death.sex === "female" ? "♀" : "♂" }}
                                </span>
                                <div>
                                    <h3 :class="identityClass(death)">{{ death.first_name }} {{ death.last_name }}</h3>
                                    <p>
                                        {{ death.age === null ? "Âge non renseigné" : `${death.age} an${death.age > 1 ? "s" : ""}` }}
                                        <span aria-hidden="true">·</span>
                                        {{ death.is_child ? "Enfant de LifeCity" : "Lifer" }}
                                    </p>
                                </div>
                            </div>
                            <p class="daily-paper__cause">{{ death.cause }}</p>
                            <time :datetime="death.died_at">Décès enregistré à {{ formattedTime(death.died_at) }}</time>
                        </article>
                    </div>

                    <div v-else class="daily-paper__empty">
                        <strong>Aucun décès annoncé aujourd’hui.</strong>
                        <p>La rubrique restera vide jusqu’à la prochaine mise à jour du cycle de la ville.</p>
                    </div>
                </section>
            </article>
        </div>
    </AppLayout>
</template>

<style scoped>
.daily-paper-page{max-width:1280px}.daily-paper__toolbar{display:flex;margin-bottom:18px;align-items:center;justify-content:space-between;gap:16px;color:#8d7c8f;font-size:13px}.daily-paper{overflow:hidden;border:1px solid rgb(70 50 78/12%);border-radius:26px;background:#fcf8f2;box-shadow:0 18px 45px rgb(70 50 78/10%)}.daily-paper__masthead{display:grid;padding:28px 40px 24px;border-bottom:4px double rgb(70 50 78/25%);text-align:center}.daily-paper__masthead>span{color:#6f927b;font-size:12px;font-weight:900;letter-spacing:.16em;text-transform:uppercase}.daily-paper__masthead h1{margin:3px 0 5px;color:#46324e;font-family:"Bricolage Grotesque",sans-serif;font-size:clamp(38px,6vw,76px);line-height:.95}.daily-paper__masthead time{color:#8d7c8f;font-weight:700;text-transform:capitalize}.daily-paper__section{padding:34px 40px 42px}.daily-paper__section-title{max-width:720px}.daily-paper__section-title>span{color:#d6a84a;font-size:12px;font-weight:900;letter-spacing:.14em;text-transform:uppercase}.daily-paper__section-title h2{margin:3px 0 6px;color:#46324e;font-family:"Bricolage Grotesque",sans-serif;font-size:clamp(34px,4vw,54px)}.daily-paper__section-title p{margin:0;color:#8d7c8f}.daily-paper__deaths{display:grid;margin-top:28px;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.daily-paper__notice{display:grid;padding:22px;border:1px solid rgb(70 50 78/10%);border-radius:18px;gap:17px;background:#f8f3ec}.daily-paper__notice>div{display:flex;align-items:center;gap:13px}.daily-paper__symbol{display:grid;width:42px;height:42px;border-radius:50%;place-items:center;color:#46324e;background:#f0e6d8;font-size:21px}.daily-paper__notice h3{margin:0;font-family:"Bricolage Grotesque",sans-serif;font-size:24px}.daily-paper__identity--female{color:#b96f7d}.daily-paper__identity--male{color:#587f92}.daily-paper__notice p{margin:0}.daily-paper__notice>div p,.daily-paper__notice time{color:#8d7c8f;font-size:13px}.daily-paper__cause{padding-top:15px;border-top:1px solid rgb(70 50 78/10%);color:#46324e;font-weight:700}.daily-paper__empty{margin-top:28px;padding:30px;border-radius:18px;color:#46324e;background:#f4eee5;text-align:center}.daily-paper__empty p{margin:5px 0 0;color:#8d7c8f}@media(max-width:760px){.daily-paper__toolbar{align-items:flex-start;flex-direction:column}.daily-paper__masthead,.daily-paper__section{padding-right:22px;padding-left:22px}.daily-paper__deaths{grid-template-columns:1fr}}
</style>
