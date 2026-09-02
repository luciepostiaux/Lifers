<script setup>
import { Head, Link } from "@inertiajs/vue3";
import SiteHeader from "@/Components/SiteHeader.vue";
import { h } from "vue";

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    seo: {
        type: Object,
        required: true,
    },
});

const currentYear = new Date().getFullYear();
const structuredData = JSON.stringify({
    "@context": "https://schema.org",
    "@type": "WebSite",
    name: "Lifers",
    url: props.seo.canonicalUrl,
    description: props.seo.description,
    inLanguage: "fr",
});
const StructuredData = () => h(
    "script",
    { type: "application/ld+json", "head-key": "website-structured-data" },
    structuredData,
);

const cityPlaces = [
    {
        eyebrow: "Progression",
        title: "Université et emploi",
        text: "Choisis parmi des études et des métiers qui façonnent réellement le parcours de ton Lifer.",
    },
    {
        eyebrow: "Quotidien",
        title: "LifeMarket",
        text: "Fais tes courses, gère ton inventaire et réponds aux besoins de chaque journée.",
    },
    {
        eyebrow: "Santé",
        title: "Hôpital et sport",
        text: "Surveille les maladies, consulte un médecin et entretiens la condition physique de ton Lifer.",
    },
    {
        eyebrow: "Temps libre",
        title: "Loisirs",
        text: "Accorde-lui des moments de détente pour faire progresser bonheur et divertissement.",
    },
    {
        eyebrow: "Famille",
        title: "Orphelinat",
        text: "Découvre les enfants en attente d’un foyer et construis une nouvelle branche de ton histoire.",
    },
    {
        eyebrow: "Mémoire",
        title: "Journal de la ville",
        text: "Consulte l’édition quotidienne et retrouve les histoires qui viennent de s’achever.",
    },
];
</script>

<template>
    <Head :title="seo.title">
        <meta head-key="description" name="description" :content="seo.description" />
        <meta head-key="robots" name="robots" content="index, follow, max-image-preview:large" />
        <link head-key="canonical" rel="canonical" :href="seo.canonicalUrl" />

        <meta head-key="og:type" property="og:type" content="website" />
        <meta head-key="og:locale" property="og:locale" content="fr_BE" />
        <meta head-key="og:site_name" property="og:site_name" content="Lifers" />
        <meta head-key="og:title" property="og:title" :content="seo.title" />
        <meta head-key="og:description" property="og:description" :content="seo.description" />
        <meta head-key="og:url" property="og:url" :content="seo.canonicalUrl" />
        <meta head-key="og:image" property="og:image" :content="seo.socialImageUrl" />
        <meta head-key="og:image:width" property="og:image:width" content="1672" />
        <meta head-key="og:image:height" property="og:image:height" content="941" />
        <meta head-key="og:image:alt" property="og:image:alt" content="Deux Lifers dans une ville illustrée et chaleureuse" />

        <meta head-key="twitter:card" name="twitter:card" content="summary_large_image" />
        <meta head-key="twitter:title" name="twitter:title" :content="seo.title" />
        <meta head-key="twitter:description" name="twitter:description" :content="seo.description" />
        <meta head-key="twitter:image" name="twitter:image" :content="seo.socialImageUrl" />

        <StructuredData />
    </Head>

    <div class="welcome-page">
        <a class="skip-link" href="#contenu-principal">Aller au contenu</a>

        <SiteHeader
            :can-login="canLogin"
            :can-register="canRegister"
            show-public-navigation
        />

        <main id="contenu-principal">
            <section class="hero" aria-labelledby="welcome-title">
                <div class="hero-content">
                    <div class="hero-copy">
                        <h1 id="welcome-title" class="hero-title">
                            <span>Ta seconde vie</span>
                            <span>commence ici.</span>
                        </h1>

                        <div class="accent-line" aria-hidden="true"></div>

                        <p class="hero-intro">
                            Crée ton histoire, fais grandir la ville et rencontre sa communauté.
                        </p>

                        <Link
                            v-if="canRegister && !$page.props.auth.user"
                            :href="route('register')"
                            class="primary-action primary-action--content focus-ring"
                        >
                            Créer mon Lifer
                        </Link>
                    </div>
                </div>

                <div class="hero-visual" aria-hidden="true">
                    <picture>
                        <source srcset="/images/landing/hero-lifers.webp" type="image/webp" />
                        <img
                            src="/images/landing/hero-lifers.png"
                            alt=""
                            width="1672"
                            height="941"
                            fetchpriority="high"
                            decoding="async"
                        />
                    </picture>
                    <div class="hero-veil hero-veil--horizontal"></div>
                </div>
            </section>

            <section id="le-jeu" class="public-section public-section--intro" aria-labelledby="game-title">
                <div class="section-shell">
                    <div class="section-heading section-heading--centered">
                        <span class="section-kicker">Le jeu</span>
                        <h2 id="game-title">Une vie à construire, pas un chemin tout tracé.</h2>
                        <p>
                            Lifers est un jeu de simulation de vie communautaire. Tu incarnes un Lifer,
                            prends soin de son quotidien et choisis la direction de son histoire.
                        </p>
                    </div>

                    <div class="promise-grid">
                        <article class="promise-card promise-card--sage">
                            <span class="promise-card__number" aria-hidden="true">01</span>
                            <h3>Prends soin de ton Lifer</h3>
                            <p>Faim, soif, santé, hygiène, bonheur, divertissement et activité physique évoluent avec tes choix.</p>
                        </article>
                        <article class="promise-card promise-card--gold">
                            <span class="promise-card__number" aria-hidden="true">02</span>
                            <h3>Choisis sa voie</h3>
                            <p>Étudie, décroche des diplômes, trouve un métier et fais progresser ton Lifer au fil des années.</p>
                        </article>
                        <article class="promise-card promise-card--rose">
                            <span class="promise-card__number" aria-hidden="true">03</span>
                            <h3>Écris son histoire</h3>
                            <p>Façonne son profil, ses relations et sa famille, puis transmets cette histoire à une nouvelle génération.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="seconde-vie" class="public-section public-section--choices" aria-labelledby="choices-title">
                <div class="section-shell choices-layout">
                    <div class="section-heading">
                        <span class="section-kicker">Ta vie, ton choix</span>
                        <h2 id="choices-title">Chaque décision laisse une trace.</h2>
                        <p>
                            Ton Lifer grandit, apprend, travaille, aime et vieillit. Une vie peut s’achever,
                            mais ses liens familiaux restent inscrits dans le monde et peuvent donner naissance à une nouvelle histoire.
                        </p>
                    </div>

                    <dl class="choice-facts" aria-label="Quelques repères du monde de Lifers">
                        <div>
                            <dt>3 jours</dt>
                            <dd>pour vivre une année dans le jeu</dd>
                        </div>
                        <div>
                            <dt>36 études</dt>
                            <dd>pour préparer le parcours de ton Lifer</dd>
                        </div>
                        <div>
                            <dt>37 métiers</dt>
                            <dd>avec une économie qui évolue dans le temps</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section id="ville" class="public-section public-section--city" aria-labelledby="city-title">
                <div class="section-shell">
                    <div class="section-heading section-heading--split">
                        <div>
                            <span class="section-kicker">La ville</span>
                            <h2 id="city-title">Tout un quotidien à explorer.</h2>
                        </div>
                        <p>
                            Les lieux de Lifers ne sont pas de simples décors : chacun ouvre une activité,
                            une décision ou un service utile à la vie de ton personnage.
                        </p>
                    </div>

                    <div class="city-grid">
                        <article v-for="place in cityPlaces" :key="place.title" class="city-card">
                            <span>{{ place.eyebrow }}</span>
                            <h3>{{ place.title }}</h3>
                            <p>{{ place.text }}</p>
                        </article>
                    </div>
                </div>
            </section>

            <section id="communaute" class="public-section public-section--community" aria-labelledby="community-title">
                <div class="section-shell community-layout">
                    <div class="community-panel">
                        <span class="community-panel__eyebrow">Toujours une place pour parler</span>
                        <div class="community-bubbles" aria-hidden="true">
                            <span>Place publique</span>
                            <span>Messages privés</span>
                            <span>Groupes</span>
                        </div>
                    </div>

                    <div class="section-heading">
                        <span class="section-kicker">La communauté</span>
                        <h2 id="community-title">Derrière chaque profil, une histoire de Lifer.</h2>
                        <p>
                            Rejoins automatiquement le salon général, échange en privé ou crée un groupe.
                            Les messages arrivent en direct et les profils publics permettent à chaque Lifer de se présenter à sa façon.
                        </p>
                        <p>
                            Les identités réelles restent en retrait : dans le jeu, c’est le nom et l’histoire du Lifer qui comptent.
                        </p>
                    </div>
                </div>
            </section>

            <section id="actualites" class="public-section public-section--news" aria-labelledby="news-title">
                <div class="section-shell news-layout">
                    <article class="newspaper" aria-label="Aperçu du journal quotidien de Lifers">
                        <span class="newspaper__name">Le journal de Lifers</span>
                        <div class="newspaper__rule" aria-hidden="true"></div>
                        <strong>Une nouvelle édition chaque jour</strong>
                        <p>La nécrologie garde la mémoire des Lifers dont l’histoire vient de s’achever.</p>
                        <span class="newspaper__note">Disponible chaque jour dans la ville</span>
                    </article>

                    <div class="section-heading">
                        <span class="section-kicker">Actualités</span>
                        <h2 id="news-title">La ville se souvient de ses habitants.</h2>
                        <p>
                            Le journal de la ville ouvre une édition quotidienne. Sa première rubrique,
                            la nécrologie, rassemble les identités et les histoires arrivées à leur terme ce jour-là.
                        </p>
                    </div>
                </div>
            </section>

            <section class="final-cta" aria-labelledby="cta-title">
                <div class="final-cta__inner">
                    <span class="section-kicker">À toi de jouer</span>
                    <h2 id="cta-title">Quelle vie vas-tu inventer ?</h2>
                    <p>Crée ton compte, donne une identité à ton premier Lifer et commence son histoire.</p>
                    <div class="final-cta__actions">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="primary-action focus-ring"
                        >
                            Reprendre ma partie
                        </Link>
                        <template v-else>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="primary-action focus-ring"
                            >
                                Créer mon Lifer
                            </Link>
                            <Link
                                v-if="canLogin"
                                :href="route('login')"
                                class="secondary-action focus-ring"
                            >
                                J’ai déjà un compte
                            </Link>
                        </template>
                    </div>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <div class="site-footer__inner">
                <Link :href="route('home')" class="site-footer__wordmark focus-ring" aria-label="Retour en haut de l’accueil Lifers">
                    LIFERS
                </Link>
                <p>Une seconde vie communautaire, à écrire à ton rythme.</p>
                <nav class="site-footer__legal" aria-label="Informations légales">
                    <Link :href="route('terms.show')" class="focus-ring">
                        Conditions d’utilisation
                    </Link>
                    <Link :href="route('policy.show')" class="focus-ring">
                        Confidentialité
                    </Link>
                </nav>
                <span>© {{ currentYear }} Lifers</span>
            </div>
        </footer>
    </div>
</template>

<style scoped>
.welcome-page {
    --cream: #f4eee5;
    --cream-light: #faf6ef;
    --plum: #46324e;
    --sage: #6f927b;
    --gold: #d6a84a;
    --rose: #d98e9b;
    min-width: 320px;
    min-height: 100svh;
    overflow-x: clip;
    color: var(--plum);
    background: var(--cream);
    font-family: "DM Sans", ui-sans-serif, system-ui, sans-serif;
}

.skip-link {
    position: fixed;
    z-index: 100;
    top: 10px;
    left: 10px;
    padding: 11px 15px;
    border-radius: 10px;
    color: var(--cream-light);
    background: var(--plum);
    font-weight: 800;
    text-decoration: none;
    transform: translateY(-160%);
}

.skip-link:focus {
    transform: translateY(0);
}

.hero {
    position: relative;
    display: flex;
    min-height: max(676px, calc(100svh - 84px));
    flex-direction: column;
    isolation: isolate;
    overflow: hidden;
    background: var(--cream);
}

.primary-action,
.secondary-action {
    display: inline-flex;
    min-height: 56px;
    padding: 14px 28px;
    border-radius: 13px;
    align-items: center;
    justify-content: center;
    color: var(--plum);
    font-size: 18px;
    font-weight: 700;
    line-height: 1.2;
    text-align: center;
    text-decoration: none;
    transition: box-shadow 180ms ease, transform 180ms ease, background 180ms ease;
}

.primary-action {
    min-width: 188px;
    background: var(--gold);
    box-shadow: 0 8px 20px rgb(70 50 78 / 12%);
}

.secondary-action {
    border: 1px solid rgb(70 50 78 / 22%);
    background: transparent;
}

.primary-action:hover,
.secondary-action:hover {
    box-shadow: 0 12px 26px rgb(70 50 78 / 16%);
    transform: translateY(-1px);
}

.secondary-action:hover {
    background: rgb(255 250 244 / 45%);
}

.primary-action:active,
.secondary-action:active {
    transform: translateY(0);
}

.focus-ring:focus-visible {
    outline: 3px solid var(--plum);
    outline-offset: 4px;
    box-shadow: 0 0 0 7px rgb(244 238 229 / 90%);
}

.hero-content {
    position: relative;
    z-index: 2;
    display: flex;
    width: min(100%, 1696px);
    margin-inline: auto;
    padding: clamp(66px, 7.2vh, 78px) clamp(40px, 3.2vw, 64px) 72px;
    flex: 1 1 auto;
    align-items: flex-start;
}

.hero-copy {
    width: min(43vw, 720px);
}

.hero-title,
.section-heading h2,
.promise-card h3,
.city-card h3,
.final-cta h2 {
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    letter-spacing: -0.035em;
}

.hero-title {
    margin: 0;
    font-size: clamp(64px, 5.15vw, 88px);
    font-weight: 700;
    line-height: 0.98;
}

.hero-title span {
    display: block;
    white-space: nowrap;
}

.accent-line {
    width: clamp(92px, 6vw, 100px);
    height: 5px;
    margin-top: 32px;
    border-radius: 999px;
    background: var(--gold);
}

.hero-intro {
    max-width: 500px;
    margin: 30px 0 0;
    font-size: clamp(23px, 1.5vw, 25px);
    line-height: 1.4;
}

.primary-action--content {
    display: none;
}

.hero-visual {
    position: absolute;
    z-index: 1;
    inset: 0;
    overflow: hidden;
}

.hero-visual picture,
.hero-visual img {
    display: block;
    width: 100%;
    height: 100%;
}

.hero-visual img {
    object-fit: cover;
    object-position: 68% center;
}

.hero-veil {
    position: absolute;
    pointer-events: none;
    inset: 0;
}

.hero-veil--horizontal {
    background: linear-gradient(90deg, rgb(244 238 229 / 99%) 0%, rgb(244 238 229 / 98%) 24%, rgb(244 238 229 / 90%) 30%, rgb(244 238 229 / 64%) 42%, rgb(244 238 229 / 12%) 56%, transparent 62%);
}

.public-section {
    scroll-margin-top: 24px;
    padding: clamp(78px, 9vw, 138px) clamp(20px, 4vw, 64px);
}

.section-shell {
    width: min(100%, 1440px);
    margin-inline: auto;
}

.section-heading {
    max-width: 720px;
}

.section-heading--centered {
    max-width: 880px;
    margin-inline: auto;
    text-align: center;
}

.section-heading--split {
    display: grid;
    max-width: none;
    grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.7fr);
    align-items: end;
    gap: clamp(36px, 7vw, 110px);
}

.section-kicker,
.city-card > span,
.community-panel__eyebrow,
.newspaper__note {
    color: var(--sage);
    font-size: 13px;
    font-weight: 800;
    letter-spacing: 0.14em;
    text-transform: uppercase;
}

.section-heading h2,
.final-cta h2 {
    margin: 12px 0 22px;
    font-size: clamp(43px, 5vw, 72px);
    line-height: 0.98;
}

.section-heading p,
.final-cta p {
    margin: 0;
    color: #77677a;
    font-size: clamp(18px, 1.55vw, 22px);
    line-height: 1.65;
}

.section-heading p + p {
    margin-top: 18px;
}

.public-section--intro,
.public-section--city,
.public-section--news {
    background: var(--cream-light);
}

.promise-grid {
    display: grid;
    margin-top: clamp(48px, 6vw, 76px);
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 18px;
}

.promise-card {
    position: relative;
    min-height: 300px;
    padding: clamp(28px, 3vw, 42px);
    overflow: hidden;
    border-radius: 24px;
}

.promise-card--sage { background: #e3eae1; }
.promise-card--gold { background: #f3e7c8; }
.promise-card--rose { background: #f2e2e4; }

.promise-card__number {
    display: block;
    margin-bottom: 52px;
    font-size: 14px;
    font-weight: 800;
}

.promise-card h3,
.city-card h3 {
    margin: 0 0 14px;
    font-size: clamp(25px, 2vw, 32px);
    line-height: 1.05;
}

.promise-card p,
.city-card p,
.newspaper p {
    margin: 0;
    color: #77677a;
    font-size: 17px;
    line-height: 1.6;
}

.choices-layout,
.community-layout,
.news-layout {
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(460px, 1.1fr);
    align-items: center;
    gap: clamp(60px, 9vw, 150px);
}

.choice-facts {
    display: grid;
    margin: 0;
    gap: 12px;
}

.choice-facts div {
    display: grid;
    min-height: 118px;
    padding: 24px 28px;
    border: 1px solid rgb(70 50 78 / 8%);
    border-radius: 20px;
    grid-template-columns: minmax(150px, 0.42fr) 1fr;
    align-items: center;
    gap: 28px;
    background: rgb(250 246 239 / 62%);
}

.choice-facts dt {
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(28px, 3vw, 40px);
    font-weight: 800;
}

.choice-facts dd {
    margin: 0;
    color: #77677a;
    font-size: 17px;
    line-height: 1.5;
}

.city-grid {
    display: grid;
    margin-top: clamp(48px, 6vw, 74px);
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 16px;
}

.city-card {
    min-height: 250px;
    padding: clamp(26px, 3vw, 38px);
    border: 1px solid rgb(70 50 78 / 8%);
    border-radius: 22px;
    background: var(--cream);
    box-shadow: 0 12px 30px rgb(70 50 78 / 5%);
}

.city-card > span {
    display: block;
    margin-bottom: 42px;
}

.community-layout {
    grid-template-columns: minmax(420px, 0.95fr) minmax(0, 1fr);
}

.community-panel {
    min-height: 430px;
    padding: clamp(30px, 4vw, 54px);
    border-radius: 28px;
    background: var(--plum);
    box-shadow: 0 22px 46px rgb(70 50 78 / 16%);
}

.community-panel__eyebrow {
    color: #d9cbd6;
}

.community-bubbles {
    display: grid;
    max-width: 430px;
    margin-top: 70px;
    gap: 16px;
}

.community-bubbles span {
    width: max-content;
    max-width: 100%;
    padding: 15px 20px;
    border-radius: 18px 18px 18px 5px;
    color: var(--plum);
    background: #f7efe4;
    font-weight: 800;
    box-shadow: 0 8px 18px rgb(0 0 0 / 10%);
}

.community-bubbles span:nth-child(2) {
    justify-self: end;
    border-radius: 18px 18px 5px;
    background: #f3e7c8;
}

.community-bubbles span:nth-child(3) {
    margin-left: 42px;
    background: #e1e9df;
}

.news-layout {
    grid-template-columns: minmax(400px, 0.9fr) minmax(0, 1fr);
}

.newspaper {
    min-height: 420px;
    padding: clamp(34px, 4vw, 58px);
    border: 1px solid rgb(70 50 78 / 14%);
    border-radius: 5px;
    background: #f5efe4;
    box-shadow: 12px 16px 0 #e7dccd, 0 26px 48px rgb(70 50 78 / 12%);
    transform: rotate(-1deg);
}

.newspaper__name {
    display: block;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(30px, 3.5vw, 48px);
    font-weight: 800;
    line-height: 1;
    text-align: center;
}

.newspaper__rule {
    height: 4px;
    margin: 24px 0 32px;
    background: var(--plum);
}

.newspaper strong {
    display: block;
    max-width: 480px;
    margin-bottom: 18px;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: clamp(29px, 3vw, 42px);
    line-height: 1.05;
}

.newspaper__note {
    display: block;
    margin-top: 42px;
    color: var(--plum);
}

.final-cta {
    padding: clamp(82px, 10vw, 150px) clamp(20px, 4vw, 64px);
    color: var(--cream-light);
    background: var(--plum);
    text-align: center;
}

.final-cta__inner {
    width: min(100%, 900px);
    margin-inline: auto;
}

.final-cta .section-kicker {
    color: #dcc383;
}

.final-cta h2 {
    color: var(--cream-light);
}

.final-cta p {
    color: #d9cbd6;
}

.final-cta__actions {
    display: flex;
    margin-top: 38px;
    justify-content: center;
    flex-wrap: wrap;
    gap: 14px;
}

.final-cta .secondary-action {
    border-color: rgb(250 246 239 / 38%);
    color: var(--cream-light);
}

.final-cta .focus-ring:focus-visible {
    outline-color: var(--cream-light);
    box-shadow: 0 0 0 7px rgb(70 50 78 / 90%);
}

.site-footer {
    padding: 32px clamp(20px, 4vw, 64px);
    background: #39273f;
}

.site-footer__inner {
    display: grid;
    width: min(100%, 1440px);
    margin-inline: auto;
    grid-template-columns: auto 1fr auto auto;
    align-items: center;
    gap: 28px;
    color: #d9cbd6;
}

.site-footer__wordmark {
    color: var(--cream-light);
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -0.03em;
    text-decoration: none;
}

.site-footer p,
.site-footer span {
    margin: 0;
    font-size: 14px;
}

.site-footer__legal {
    display: flex;
    align-items: center;
    gap: 18px;
}

.site-footer__legal a {
    color: #d9cbd6;
    font-size: 13px;
    font-weight: 600;
    text-decoration-color: #d6a84a;
    text-underline-offset: 4px;
}

@media (max-width: 1279px) {
    .hero {
        min-height: calc(100svh - 76px);
        overflow: visible;
    }

    .hero-content {
        padding: clamp(72px, 10vh, 104px) clamp(28px, 3.5vw, 36px) 64px;
        flex: 0 0 auto;
        background: var(--cream);
    }

    .hero-copy {
        width: 100%;
        max-width: 720px;
    }

    .hero-title {
        font-size: clamp(54px, 6.2vw, 68px);
    }

    .hero-intro {
        max-width: 520px;
        font-size: clamp(21px, 2.15vw, 24px);
    }

    .hero-visual {
        position: relative;
        width: 100%;
        aspect-ratio: 1672 / 941;
        min-height: 0;
    }

    .hero-visual img {
        object-position: center;
    }

    .hero-veil--horizontal {
        display: none;
    }
}

@media (max-width: 980px) {
    .promise-grid,
    .city-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .choices-layout,
    .community-layout,
    .news-layout {
        grid-template-columns: 1fr;
        gap: 54px;
    }

    .community-panel,
    .newspaper {
        width: min(100%, 650px);
    }

    .section-heading--split {
        grid-template-columns: 1fr;
        align-items: start;
        gap: 22px;
    }

    .section-heading--split > p {
        max-width: 720px;
    }
}

@media (max-width: 767px) {
    .hero {
        min-height: calc(100svh - 68px);
    }

    .hero-content {
        display: block;
        padding: clamp(42px, 9vw, 64px) clamp(18px, 5vw, 28px) clamp(40px, 9vw, 56px);
    }

    .hero-title {
        font-size: clamp(42px, 12vw, 56px);
        line-height: 1;
    }

    .hero-title span {
        white-space: normal;
    }

    .accent-line {
        width: 76px;
        height: 4px;
        margin-top: 26px;
    }

    .hero-intro {
        margin-top: 24px;
        font-size: clamp(18px, 5vw, 20px);
        line-height: 1.45;
    }

    .primary-action--content {
        display: inline-flex;
        width: 100%;
        max-width: 360px;
        margin-top: 30px;
    }

    .hero-visual {
        aspect-ratio: 4 / 3;
    }

    .hero-visual img {
        object-position: 70% center;
    }

    .public-section {
        padding-block: 72px;
    }

    .section-heading h2,
    .final-cta h2 {
        font-size: clamp(38px, 11vw, 52px);
    }

    .promise-grid,
    .city-grid {
        grid-template-columns: 1fr;
    }

    .promise-card {
        min-height: 250px;
    }

    .promise-card__number,
    .city-card > span {
        margin-bottom: 34px;
    }

    .choice-facts div {
        grid-template-columns: 1fr;
        gap: 8px;
    }

    .community-panel {
        min-height: 350px;
    }

    .community-bubbles {
        margin-top: 48px;
    }

    .newspaper {
        min-height: 360px;
        box-shadow: 8px 10px 0 #e7dccd, 0 20px 38px rgb(70 50 78 / 10%);
    }

    .site-footer__inner {
        grid-template-columns: 1fr;
        justify-items: center;
        gap: 10px;
        text-align: center;
    }
}

@media (max-width: 359px) {
    .hero-content {
        padding-inline: 16px;
    }

    .public-section,
    .final-cta {
        padding-inline: 16px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .primary-action,
    .secondary-action {
        transition: none;
    }

    .primary-action:hover,
    .secondary-action:hover {
        transform: none;
    }
}
</style>
