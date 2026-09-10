<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const form = useForm({
    password: '',
});

const passwordInput = ref(null);

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();

            passwordInput.value.focus();
        },
    });
};
</script>

<template>
    <Head title="Lifers — Confirmer mon mot de passe">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    </Head>

    <div class="lifers-auth-page">
        <a class="lifers-auth-skip-link" href="#contenu-principal">Aller au contenu</a>
        <SiteHeader />
        <main id="contenu-principal" class="lifers-auth-main" tabindex="-1">
            <section class="lifers-auth-card" aria-labelledby="confirm-password-title">
                <h1 id="confirm-password-title" class="lifers-auth-title">Zone sécurisée</h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>
                <p class="lifers-auth-copy">Confirme ton mot de passe avant de poursuivre cette action sensible.</p>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="password">Mot de passe</label>
                        <PasswordInput id="password" ref="passwordInput" v-model="form.password" input-class="lifers-auth-input" required autocomplete="current-password" autofocus :aria-invalid="Boolean(form.errors.password)" :aria-describedby="form.errors.password ? 'password-error' : undefined" />
                        <InputError id="password-error" class="lifers-auth-error" :message="form.errors.password" />
                    </div>

                    <button type="submit" class="lifers-auth-submit" :disabled="form.processing">Confirmer</button>
                </form>
            </section>
        </main>
    </div>
</template>
