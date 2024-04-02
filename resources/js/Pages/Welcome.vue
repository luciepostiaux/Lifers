<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
</script>

<template>
    <Head title="Welcome" />

    <div
        class="relative flex items-center justify-center min-h-screen bg-dots-darker bg-center selection:bg-red-500 selection:text-white"
    >
        <div v-if="canLogin" class="absolute top-0 end-0 p-6 text-end z-10">
            <template v-if="$page.props.auth.user">
                <Link :href="route('dashboard')" class="">Dashboard</Link>
            </template>
            <template v-else>
                <Link :href="route('login')" class="mr-2">Log in</Link>
                <Link v-if="canRegister" :href="route('register')" class=""
                    >Register</Link
                >
            </template>
        </div>

        <div
            class="flex flex-col sm:flex-row items-center justify-center w-full px-4"
        >
            <img src="images/perso/perso.png" alt="Personnage 1" class="h-60" />
            <div class="sm:w-3/4 text-center">
                <h1>Lifers</h1>
                <p>
                    Bienvenue dans le monde de Lifers, un jeu de simulation de
                    vie en ligne où chaque choix trace le chemin de votre
                    destinée. Incarnez un Lifer, votre alter ego dans un univers
                    parallèle, et vivez une existence pleine de possibilités.
                    Des études approfondies aux carrières ambitieuses, des liens
                    familiaux aux amitiés durables, et même la compagnie
                    d'animaux fidèles, chaque aspect de la vie est à portée de
                    clic. Avec Lifers, chaque jour est une nouvelle chance de
                    façonner l'histoire de votre personnage selon vos rêves et
                    aspirations.
                </p>
                <p>
                    Plongez dans une simulation où le temps a une dimension
                    unique : un an dans la vie de votre Lifer se déroule en
                    seulement trois jours réels. Ce rythme permet une évolution
                    dynamique et une exploration immersive de diverses
                    trajectoires de vie. Que votre objectif soit la longévité,
                    la richesse, la popularité, ou simplement une existence
                    sereine et équilibrée, Lifers offre une plateforme pour
                    concrétiser chaque vision de la vie.
                </p>
                <p>
                    Définissez vos objectifs, forgez votre parcours et
                    interagissez avec une communauté de Lifers animés par des
                    aspirations similaires ou radicalement différentes. Chaque
                    décision influence votre parcours, chaque interaction tisse
                    le réseau de votre existence dans ce monde riche et
                    dynamique. Avec Lifers, prenez les rênes de votre deuxième
                    vie et découvrez l'infini potentiel de vos choix. Bienvenue
                    dans une aventure où chaque clic ouvre un nouveau chapitre,
                    bienvenue dans Lifers.
                </p>

                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="mt-4 inline-block"
                    >S'inscrire</Link
                >
            </div>

            <img
                src="images/perso/perso2.png"
                alt="Personnage 2"
                class="h-60"
            />
        </div>
    </div>
</template>

<style></style>
