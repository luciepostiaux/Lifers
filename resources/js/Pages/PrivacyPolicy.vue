<script setup>
import { Head } from "@inertiajs/vue3";
import SiteHeader from "@/Components/SiteHeader.vue";

defineProps({
    policy: String,
});
</script>

<template>
    <Head title="Lifers — Politique de confidentialité" />

    <div class="lifers-legal-page">
        <SiteHeader />

        <main class="lifers-legal-main">
            <article class="lifers-legal-card" v-html="policy"></article>
        </main>
    </div>
</template>
