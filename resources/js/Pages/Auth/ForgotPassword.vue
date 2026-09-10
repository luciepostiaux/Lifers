<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="Lifers — Mot de passe oublié">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    </Head>

    <div class="lifers-auth-page">
        <a class="lifers-auth-skip-link" href="#contenu-principal">Aller au contenu</a>
        <div class="lifers-auth-scene" aria-hidden="true">
            <img src="/images/landing/hero-lifers.png" alt="" width="1672" height="941" decoding="async" />
        </div>

        <SiteHeader />

        <main id="contenu-principal" class="lifers-auth-main" tabindex="-1">
            <section class="lifers-auth-card" aria-labelledby="forgot-password-title">
                <h1 id="forgot-password-title" class="lifers-auth-title">Mot de passe oublié</h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>
                <p class="lifers-auth-copy">
                    Indique ton adresse e-mail. Nous t’enverrons un lien sécurisé pour choisir un nouveau mot de passe.
                </p>

                <div v-if="status" class="lifers-auth-status" role="status">{{ status }}</div>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="email">Email</label>
                        <input id="email" v-model="form.email" type="email" class="lifers-auth-input" required autofocus autocomplete="username" :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'email-error' : undefined" />
                        <InputError id="email-error" class="lifers-auth-error" :message="form.errors.email" />
                    </div>

                    <div class="lifers-auth-actions">
                        <Link :href="route('login')" class="lifers-auth-link">Retour à la connexion</Link>
                        <button type="submit" class="lifers-auth-submit" :disabled="form.processing">
                            {{ form.processing ? 'Envoi…' : 'Recevoir le lien' }}
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
