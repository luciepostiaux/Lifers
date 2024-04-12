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

    <div class="min-h-screen flex items-center justify-center">
        <div class="flex flex-col border-4 max-w-1800px" style="height: 50vh">
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
                class="flex flex-col md:flex-row items-center justify-center w-full max-w-screen-lg px-4 py-10 gap-10 border-2 border-blue-900"
            >
                <div
                    class="md:w-1/2 flex flex-col items-center md:items-start text-center md:text-left p-8 border-2 border-orange-500"
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

                <div
                    class="md:flex md:items-end md:gap-10 border-2 border-lime-500"
                >
                    <img
                        src="images/perso/perso.png"
                        alt="Personnage 1"
                        class="h-60"
                    />
                    <img
                        src="images/perso/perso2.png"
                        alt="Personnage 2"
                        class="h-64"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
