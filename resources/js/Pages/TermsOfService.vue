<script setup>
import { Head } from "@inertiajs/vue3";
import SiteHeader from "@/Components/SiteHeader.vue";

defineProps({
    terms: String,
});
</script>

<template>
    <Head title="Lifers — Conditions d’utilisation" />

    <div class="lifers-legal-page">
        <SiteHeader />

        <main class="lifers-legal-main">
            <article class="lifers-legal-card" v-html="terms"></article>
        </main>
    </div>
</template>
