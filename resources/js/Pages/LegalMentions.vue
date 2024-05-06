<!-- resources/js/Pages/LegalMentions.vue -->
<script setup>
import { Head, Link } from "@inertiajs/vue3";
defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
const title = "Mentions légales";
const description =
    "Page des mentions légales pour le site de jeu de simulation Lifers.";
</script>

<template>
    <Head
        :title="title"
        :meta="[{ name: 'description', content: description }]"
    />
    <div class="flex bg-emerald-950 h-screen">
        <div
            class="lg:w-[85vw] lg:h-[85vh] hide-scrollbar custom-scrollbar overflow-y-auto text-gray-200 shadow-lg lg:rounded-lg m-auto bg-cover bg-center p-6"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div>
                <div
                    class="flex flex-col bg-emerald-900/90 w-full h-full rounded-lg shadow-md backdrop-blur-lg p-4"
                >
                    <div class="w-full p-2 text-right">
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('dashboard')" class=" "
                                >Dashboard</Link
                            >
                        </template>
                        <template v-else>
                            <Link :href="route('login')" class="mr-4"
                                >Se connecter</Link
                            >
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="lg:mr-4 font-bold text-amber-500 hover:text-amber-600 transition-all"
                            >
                                S'inscrire</Link
                            >
                        </template>
                    </div>
                    <Link :href="route('welcome')" class="no-underline">
                        <h1
                            class="font-bangers text-7xl text-center text-gray-100 cursor-pointer"
                        >
                            Lifers
                        </h1>
                    </Link>
                    <h2 class="text-2xl font-semibold mb-2">
                        Mentions Légales
                    </h2>

                    <section class="mb-4">
                        <h2 class="text-xl font-semibold mb-2">
                            Informations de l'éditeur du site
                        </h2>
                        <p class="text-sm text-gray-200">
                            Nom de l'éditeur : Lucie Postiaux
                            <br />
                            Localisation : Belgique
                            <br />
                            Adresse e-mail : lifers.info@gmail.com<br />
                        </p>
                    </section>
                    <section class="mb-4">
                        <h2 class="text-xl font-semibold mb-2">
                            Directeur de la publication
                        </h2>
                        <p class="text-sm text-gray-200">
                            Nom : Lucie Postiaux
                        </p>
                    </section>
                    <section class="mb-4">
                        <h2 class="text-xl font-semibold mb-2">
                            Hébergeur du site
                        </h2>
                        <p class="text-sm text-gray-200">
                            Nom : o2Switch<br />
                            Adresse : Chem. des Pardiaux, 63000
                            Clermont-Ferrand, France<br />
                            Numéro de téléphone : Belgique 02 808 59 58<br />
                        </p>
                    </section>
                    <section class="mb-4">
                        <h2 class="text-xl font-semibold mb-2">
                            Propriété intellectuelle
                        </h2>
                        <p class="text-sm text-gray-200">
                            Certains textes, images et autres contenus présents
                            sur ce site ont été générés à l'aide d'une
                            intelligence artificielle (ChatGPT-4). <br />
                            Les textes générés à l'aide de ChatGPT-4 sont
                            fournis en accord avec la politique d'utilisation
                            d'OpenAI. L'utilisateur final détient les droits
                            d'utilisation et de modification, mais doit
                            respecter les politiques d'OpenAI.
                        </p>
                    </section>
                    <hr class="m-4" />

                    <!-- Liste des CGU stylisée -->
                    <section class="mb-4">
                        <h2 class="text-2xl font-semibold mb-2">
                            Conditions Générales d'Utilisation
                        </h2>
                        <ul class="space-y-3 list-none">
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    En accédant et en utilisant ce site web,
                                    vous acceptez les présentes Conditions
                                    Générales d'Utilisation (CGU). Si vous
                                    n'acceptez pas ces conditions, veuillez ne
                                    pas utiliser le site.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    "Utilisateur" désigne toute personne qui
                                    accède et utilise le site web.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    Ce site est réservé aux utilisateurs âgés de
                                    16 ans ou plus.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    Pour accéder à certaines fonctionnalités du
                                    site, l'utilisateur doit créer un compte en
                                    fournissant des informations exactes.
                                    L'utilisateur est responsable de la sécurité
                                    de son compte et doit signaler toute
                                    activité suspecte.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    L'utilisateur s'engage à respecter les
                                    autres membres, à ne pas diffuser de contenu
                                    offensant, et à ne pas enfreindre la loi en
                                    utilisant ce site.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    Nous nous réservons le droit de supprimer
                                    tout contenu utilisateur qui enfreint nos
                                    politiques ou la loi.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    Nous ne garantissons pas que le site sera
                                    toujours disponible ou exempt d'erreurs.
                                    Nous ne pouvons être tenus responsables des
                                    dommages résultant de l'utilisation du site.
                                </p>
                            </li>
                            <li class="flex items-center">
                                <span
                                    class="inline-block w-2 h-2 rounded-full bg-amber-500"
                                ></span>
                                <p class="ml-2 text-sm text-gray-200">
                                    Nous nous réservons le droit de modifier ces
                                    CGU à tout moment. Les modifications seront
                                    publiées ici, et l'utilisation continue du
                                    site après modification implique
                                    l'acceptation des nouvelles conditions.
                                </p>
                            </li>
                        </ul>
                    </section>
                    <hr class="m-4" />
                    <section>
                        <h2 class="text-2xl font-semibold mb-2">
                            Politique de Confidentialité
                        </h2>
                        <h3 class="text-lg font-semibold my-2">
                            Types de Données Collectées
                        </h3>

                        <p class="text-sm text-gray-200">
                            Nous collectons uniquement les adresses e-mail
                            fournies par les utilisateurs lors de la création de
                            leur compte sur notre site. Ces adresses e-mail sont
                            utilisées exclusivement pour permettre
                            l'authentification et la connexion des utilisateurs.
                        </p>
                    </section>
                    <section>
                        <h3 class="text-lg font-semibold my-2">
                            Utilisation des Données
                        </h3>
                        <p class="text-sm text-gray-200">
                            L'adresse e-mail collectée est utilisée uniquement
                            pour l'authentification et la connexion des
                            utilisateurs à leur compte. Nous ne partageons ni
                            n'utilisons cette information à d'autres fins,
                            telles que l'envoi de newsletters ou de publicités.
                        </p>
                    </section>
                    <section>
                        <h3 class="text-lg font-semibold my-2">
                            Conservation et Sécurité des Données
                        </h3>
                        <p class="text-sm text-gray-200">
                            L'adresse e-mail collectée est conservée aussi
                            longtemps que le compte de l'utilisateur est actif.
                            Nous mettons en œuvre des mesures de sécurité pour
                            protéger ces informations contre tout accès non
                            autorisé ou toute modification.
                        </p>
                    </section>
                    <section>
                        <h3 class="text-lg font-semibold my-2">
                            Droits des Utilisateurs
                        </h3>
                        <p class="text-sm text-gray-200">
                            Les utilisateurs peuvent accéder, rectifier ou
                            supprimer leurs données.
                        </p>
                    </section>
                    <section>
                        <h3 class="text-lg font-semibold my-2">Sécurité</h3>
                        <p class="text-sm text-gray-200">
                            Nous prenons la sécurité de vos données personnelles
                            très au sérieux. Pour sécuriser les informations de
                            connexion et les données personnelles, nous
                            utilisons Jetstream, qui inclut des fonctionnalités
                            telles que la double authentification (2FA), la
                            réinitialisation sécurisée des mots de passe et la
                            gestion des sessions actives. Ces mesures aident à
                            protéger votre compte contre l'accès non autorisé et
                            les fuites de données.
                        </p>
                    </section>
                    <section>
                        <h3 class="text-lg font-semibold my-2">
                            Modifications de la Politique
                        </h3>
                        <p class="text-sm text-gray-200">
                            Nous nous réservons le droit de modifier cette
                            politique de confidentialité à tout moment. Lorsque
                            des modifications importantes sont apportées à cette
                            politique, nous vous informerons par un pop-up lors
                            de votre prochaine connexion au site. Cela vous
                            permettra de prendre connaissance des changements et
                            de décider si vous souhaitez continuer à utiliser
                            nos services sous les nouvelles conditions.
                        </p>
                    </section>
                </div>
            </div>
        </div>
        <footer class="absolute inset-x-0 bottom-0 text-gray-200 text-sm mb-4">
            <div
                class="container mx-auto flex justify-center items-center space-x-2"
            >
                <span> {{ new Date().getFullYear() }} Lifers </span>
                <p>|</p>
                <nav>
                    <a href="/mentions-legales" class="hover:underline">
                        Mentions légales</a
                    >
                </nav>
            </div>
        </footer>
    </div>
</template>

<style scoped></style>
