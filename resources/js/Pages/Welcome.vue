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

    <div class="flex justify-center bg-emerald-950 h-screen">
        <div
            class="w-[85vw] h-[85vh] custom-scrollbar overflow-y-auto text-gray-100 shadow-lg rounded-lg m-auto bg-cover bg-center"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div
                class="flex flex-col justify-center items-center gap-4 md:flex-row w-full h-full"
            >
                <div
                    class="flex flex-col justify-center bg-emerald-900/90 w-3/4 h-3/4 rounded-lg shadow-md md:mb-0 backdrop-blur-sm"
                >
                    <div class="w-full p-6 text-right">
                        <template v-if="$page.props.auth.user">
                            <Link :href="route('dashboard')" class=" "
                                >Dashboard</Link
                            >
                        </template>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="mr-4 font-semibold"
                                >Log in</Link
                            >
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="lg:mr-6 font-semibold"
                                >Register</Link
                            >
                        </template>
                    </div>
                    <div
                        class="flex flex-col md:flex-row items-center justify-around w-full max-w-screen-lg px-4 py-10 gap-10"
                    >
                        <div
                            class="md:w-1/2 flex flex-col items-center md:items-start text-center md:text-left p-8"
                        >
                            <h1 class="font-bangers text-9xl mb-6">Lifers</h1>

                            <p class="font-roboto mb-4">
                                Plongez dans Lifers, un jeu de simulation de vie
                                en ligne en mode RP. Incarnez un personnage,
                                forgez votre destinée et interagissez avec
                                d'autres joueurs pour une expérience unique.
                            </p>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex items-center justify-center mt-4 py-2 w-full bg-emerald-500/80 hover:bg-emerald-400/80 rounded-md font-semibold percase tracking-widest transition-all duration-300 ease-in-out hover:scale-105 uppercase"
                                >Rejoignez-nous maintenant !</Link
                            >
                        </div>

                        <div class="md:flex md:items-end md:gap-10">
                            <img
                                src="images/perso/A-1.webp"
                                alt="Personnage 1"
                                class="h-96"
                            />
                            <img
                                src="images/perso/B-1.webp"
                                alt="Personnage 2"
                                class="h-96"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
