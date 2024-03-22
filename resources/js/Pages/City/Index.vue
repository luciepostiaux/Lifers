<script setup>
import { defineProps } from "vue";
import { Link } from "@inertiajs/vue3";

import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({});
</script>
<template>
    <AppLayout title="City">
        <div class="container mx-auto p-4">
            <h1>Bienvenue à LifeCity</h1>
            <div class="grid grid-cols-2 gap-4">
                <Link
                    href="/city/lifemarket"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller au LifeMarket
                </Link>
                <Link
                    href="/doctor"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller au Médecin
                </Link>
                <Link
                    href="/city/sport"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller à la Salle de Sport
                </Link>
                <Link
                    href="/city/entertainment"
                    class="bg-pink-500 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller au hall de divertissement
                </Link>
                <Link
                    href="/study"
                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller à l'Université
                </Link>
                <Link
                    href="/job"
                    class="bg-purple-500 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded"
                >
                    Aller au Pôle Emploi
                </Link>
                <!-- Ajoutez plus de boutons selon vos besoins -->
            </div>
        </div>
    </AppLayout>
</template>
