<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    email: String,
    token: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('password.update'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Lifers — Choisir un nouveau mot de passe">
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
            <section class="lifers-auth-card" aria-labelledby="reset-password-title">
                <h1 id="reset-password-title" class="lifers-auth-title">Nouveau mot de passe</h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="email">Email</label>
                        <input id="email" v-model="form.email" type="email" class="lifers-auth-input" required autofocus autocomplete="username" :aria-invalid="Boolean(form.errors.email)" :aria-describedby="form.errors.email ? 'email-error' : undefined" />
                        <InputError id="email-error" class="lifers-auth-error" :message="form.errors.email" />
                    </div>

                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="password">Nouveau mot de passe</label>
                        <PasswordInput id="password" v-model="form.password" input-class="lifers-auth-input" required autocomplete="new-password" :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'password-error' : undefined" />
                        <InputError id="password-error" class="lifers-auth-error" :message="form.errors.password" />
                    </div>

                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="password_confirmation">Confirmer le mot de passe</label>
                        <PasswordInput id="password_confirmation" v-model="form.password_confirmation" input-class="lifers-auth-input" required autocomplete="new-password" :aria-invalid="Boolean(form.errors.password_confirmation)" :aria-describedby="form.errors.password_confirmation ? 'password-confirmation-error' : undefined" />
                        <InputError id="password-confirmation-error" class="lifers-auth-error" :message="form.errors.password_confirmation" />
                    </div>

                    <button type="submit" class="lifers-auth-submit" :disabled="form.processing">
                        {{ form.processing ? 'Enregistrement…' : 'Enregistrer le mot de passe' }}
                    </button>
                </form>
            </section>
        </main>
    </div>
</template>
