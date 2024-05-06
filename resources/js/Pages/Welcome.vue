<script setup>
import { Head, Link } from "@inertiajs/vue3";

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
});
const title = "Lifers - Votre monde de simulation de vie en ligne";
const description =
    "Découvrez Lifers, le jeu de simulation de vie immersive pour tout le monde (+16ans). Créez votre personnage, explorez un monde virtuel et forgez des amitiés dans une communauté good vibes.";
</script>

<template>
    <Head
        :title="title"
        :meta="[{ name: 'description', content: description }]"
    />

    <div class="flex bg-emerald-950 h-screen">
        <div
            class="lg:w-[85vw] lg:h-[85vh] md:flex justify-center items-center hide-scrollbar custom-scrollbar overflow-y-auto text-gray-200 shadow-lg lg:rounded-lg lg:m-auto bg-cover bg-center p-6"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div
                class="flex flex-col justify-center items-center lg:flex-row w-full mb-6 lg:mb-0"
            >
                <div
                    class="flex flex-col justify-around bg-emerald-900/90 w-full h-full lg:w-4/5 lg:h-4/5 rounded-lg shadow-md backdrop-blur-lg py-4"
                >
                    <div class="w-full p-2 pt-4 text-right">
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
                    <div
                        class="flex flex-col lg:flex-row items-center justify-center gap-10 w-full"
                    >
                        <div
                            class="lg:w-2/3 flex flex-col text-center lg:text-left p-4 tracking-wide leading-relaxed"
                        >
                            <h1 class="font-bangers text-9xl lg:mb-4">
                                Lifers
                            </h1>
                            <h2 class="font-roboto text-2xl mb-2">
                                Incarnez qui vous voulez dans
                                <strong class="text-amber-500">
                                    Lifers !
                                </strong>
                            </h2>

                            <p class="font-roboto text-gray-200">
                                Découvrez
                                <strong class="text-amber-500">Lifers</strong>,
                                une aventure captivante dans un univers de
                                simulation de vie en ligne en mode RP. Créez et
                                personnalisez votre propre personnage, forgez
                                votre destinée et interagissez avec une
                                communauté dynamique de joueurs. Chaque décision
                                que vous prenez façonne votre expérience unique
                                et impacte le monde virtuel autour de vous.
                                <br />
                                <strong class="text-amber-500"
                                    >Rejoignez dès maintenant</strong
                                >
                                et prenez part à l'évolution de Lifers : votre
                                feedback et vos idées sont précieux pour nous
                                aider à perfectionner le jeu. Participez à notre
                                boîte à idées et voyez vos suggestions prendre
                                vie au fur et à mesure que Lifers grandit et
                                évolue.
                            </p>
                            <div
                                class="flex justify-center space-y-2 pt-4 lg:pt-0"
                            >
                                <Link
                                    v-if="canRegister"
                                    :href="route('register')"
                                    class="inline-flex items-center justify-center lg:mt-12 py-2 w-2/3 bg-amber-500 hover:bg-amber-600 rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out uppercase text-white"
                                    >Rejoignez-nous dès maintenant !</Link
                                >
                            </div>
                        </div>

                        <div class="flex items-end gap-4 lg:gap-10">
                            <img
                                src="images/perso/A-1.webp"
                                alt="Personnage de Lifers femme"
                                class="max-h-96"
                            />

                            <img
                                src="images/perso/B-1.webp"
                                alt="Avatar de Lifers homme"
                                class="max-h-96"
                            />
                        </div>
                    </div>
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
