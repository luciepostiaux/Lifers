<!-- resources/js/Pages/HelpInfo.vue -->
<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import { Head } from "@inertiajs/vue3";
const title = "Aides et Informations";
const description =
    "Toutes les informations et aides nécessaires pour bien fonctionner dans Lifers.";
</script>

<template>
    <AppLayout>
        <Head
            :title="title"
            :meta="[{ name: 'description', content: description }]"
        />

        <div
            class="h-full flex items-center justify-between p-4 rounded-lg text-sm bg-emerald-900/90 backdrop-blur-lg"
        >
            <div class="container mx-auto px-4 py-8 text-gray-100">
                <h1 class="text-xl font-bold mb-6">{{ title }}</h1>
                <section>
                    <h2 class="text-lg font-semibold my-4">
                        Développement du Jeu
                    </h2>
                    <p>
                        Le jeu est actuellement en version bêta et continuera à
                        évoluer avec le temps. Bien que les fonctionnalités
                        disponibles ne représentent que les bases d'une
                        simulation de vie pour l'instant, elles sont destinées à
                        s'enrichir davantage.
                    </p>
                    <h3 class="font-bold my-2">Fonctionnalités prévues</h3>
                    <ul class="mb-4 space-y-2 md:ml-2">
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>
                            <p class="ml-2 text-sm text-gray-200">
                                Prochainement, un système de messagerie privée à
                                deux ou en groupe sera intégré.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Une fonctionnalité de création de famille sera
                                introduite, permettant aux Lifers de se marier
                                et de fonder leur propre famille.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Une attention particulière sera accordée à la
                                dimension familiale et aux animaux domestiques
                                dans les mises à jour à venir.
                            </p>
                        </li>

                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Des mini-jeux pour les métiers seront également
                                disponibles, offrant aux joueurs la possibilité
                                de gagner davantage en accomplissant diverses
                                missions.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Les systèmes d'éducation seront améliorés,
                                introduisant un système de points. Un meilleur
                                score se traduira par un salaire plus élevé dans
                                le domaine concerné.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                La personnalisation des personnages sera
                                élargie, permettant aux joueurs de modifier
                                leurs vêtements selon leurs préférences.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Les personnages vieilliront visuellement avec le
                                temps, ajoutant ainsi une dimension réaliste à
                                l'expérience de jeu.
                            </p>
                        </li>
                        <li class="flex items-center text-sm">
                            <span
                                class="inline-block w-2 h-2 rounded-full bg-amber-500"
                            ></span>

                            <p class="ml-2 text-sm text-gray-200">
                                Une mini-carte sera disponible pour faciliter
                                les déplacements dans la ville.
                            </p>
                        </li>
                    </ul>
                </section>
                <section>
                    <h2 class="text-lg font-semibold mb-4">
                        Débuter dans le Jeu
                    </h2>
                    <p>
                        Lorsque vous arrivez, il est primordial de trouver un
                        emploi et de commencer des études afin de gagner de
                        l'argent pour prendre soin de votre Lifer. Vous devrez
                        acheter des aliments et des produits d'hygiène pour
                        répondre à ses besoins essentiels. La salle de sport est
                        également un lieu incontournable pour maintenir sa forme
                        physique, et en cas de maladie, il est important de
                        consulter un médecin pour le soigner. Toutes ces
                        activités sont accessibles via l'onglet "Ville". Par
                        ailleurs, tous les biens que vous possédez sont
                        disponibles dans votre domicile, consultable dans la
                        section "Chez Soi".
                    </p>
                </section>
                <section>
                    <h2 class="text-lg font-semibold my-4">
                        Fonctionnalités Clés
                    </h2>
                    <p>
                        Dans Lifers, le temps s'écoule avec une année pour trois
                        jours écoulés dans le jeu. À noter que le jeu est
                        toujours en phase d'équilibrage, donc le coût des objets
                        et des services est susceptible de changer à l'avenir.
                    </p>
                </section>
                <section>
                    <h2 class="text-lg font-semibold my-4">Items</h2>
                    <p>
                        Les items du jeu sont essentiels pour prendre soin de
                        votre Lifer. Chaque aliment ou objet a un effet unique :
                        il peut augmenter ou diminuer certaines jauges, voire
                        les deux. N'hésitez pas à effectuer des tests pour
                        découvrir ce qui est le plus efficace pour votre
                        personnage !
                    </p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>
