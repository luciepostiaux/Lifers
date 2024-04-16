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

    <div class="min-h-screen flex items-center justify-center bg-emerald-950">
        <div class="flex flex-col max-w-1800px bg-slate-100 p-4 rounded-xl">
            <div class="w-full p-6 text-right">
                <template v-if="$page.props.auth.user">
                    <Link
                        :href="route('dashboard')"
                        class="text-gray-700 hover:text-gray-900"
                        >Dashboard</Link
                    >
                </template>
                <template v-else>
                    <Link
                        :href="route('login')"
                        class="text-gray-700 hover:text-gray-900 mr-4"
                        >Log in</Link
                    >
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="text-gray-700 hover:text-gray-900"
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
                        Plongez dans Lifers, un jeu de simulation de vie en
                        ligne en mode RP. Incarnez un personnage, forgez votre
                        destinée et interagissez avec d'autres joueurs pour une
                        expérience unique.
                    </p>
                    <Link
                        v-if="canRegister"
                        :href="route('register')"
                        class="bg-green-500 text-white font-roboto font-bold py-2 px-4 rounded hover:bg-green-700 transition duration-300"
                        >Rejoignez-nous maintenant !</Link
                    >
                </div>

                <div class="md:flex md:items-end md:gap-10">
                    <img
                        src="images/perso/A-1.webp"
                        alt="Personnage 1"
                        class="h-80"
                    />
                    <img
                        src="images/perso/B-1.webp"
                        alt="Personnage 2"
                        class="h-80"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
