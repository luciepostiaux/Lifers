<script setup>
import { computed } from "vue";
import { Head, Link, useForm } from "@inertiajs/vue3";
import SiteHeader from "@/Components/SiteHeader.vue";

const props = defineProps({ status: String });
const form = useForm({});
const verificationLinkSent = computed(() => props.status === "verification-link-sent");

const submit = () => {
    form.post(route("verification.send"));
};
</script>

<template>
    <Head title="Lifers — Vérifier mon adresse e-mail">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    </Head>

    <div class="lifers-auth-page">
        <SiteHeader :can-login="false" />

        <main class="lifers-auth-main">
            <section class="lifers-auth-card" aria-labelledby="verify-email-title">
                <h1 id="verify-email-title" class="lifers-auth-title">Vérifie ton adresse e-mail</h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>

                <p>
                    Nous venons de t’envoyer un lien de vérification. Ouvre-le pour confirmer que cette adresse t’appartient avant d’accéder à Lifers.
                </p>

                <div v-if="verificationLinkSent" class="lifers-auth-status" role="status">
                    Un nouveau lien de vérification vient de t’être envoyé.
                </div>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <button type="submit" class="lifers-auth-submit" :disabled="form.processing">
                        {{ form.processing ? "Envoi…" : "Renvoyer le lien" }}
                    </button>

                    <div class="lifers-auth-actions">
                        <Link :href="route('profile.show')" class="lifers-auth-link">Modifier mon adresse</Link>
                        <Link :href="route('logout')" method="post" as="button" class="lifers-auth-link">Se déconnecter</Link>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
