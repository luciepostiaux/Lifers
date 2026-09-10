<script setup>
import { nextTick, ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const recovery = ref(false);

const form = useForm({
    code: '',
    recovery_code: '',
});

const recoveryCodeInput = ref(null);
const codeInput = ref(null);

const toggleRecovery = async () => {
    recovery.value ^= true;

    await nextTick();

    if (recovery.value) {
        recoveryCodeInput.value.focus();
        form.code = '';
    } else {
        codeInput.value.focus();
        form.recovery_code = '';
    }
};

const submit = () => {
    form.post(route('two-factor.login'));
};
</script>

<template>
    <Head title="Lifers — Double authentification">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap" rel="stylesheet" />
    </Head>

    <div class="lifers-auth-page">
        <a class="lifers-auth-skip-link" href="#contenu-principal">Aller au contenu</a>
        <SiteHeader :can-login="false" />
        <main id="contenu-principal" class="lifers-auth-main" tabindex="-1">
            <section class="lifers-auth-card" aria-labelledby="two-factor-title">
                <h1 id="two-factor-title" class="lifers-auth-title">Double authentification</h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>
                <p class="lifers-auth-copy">
                    <template v-if="!recovery">Saisis le code temporaire fourni par ton application d’authentification.</template>
                    <template v-else>Saisis l’un de tes codes de récupération d’urgence.</template>
                </p>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div v-if="!recovery" class="lifers-auth-field">
                        <label class="lifers-auth-label" for="code">Code d’authentification</label>
                        <input id="code" ref="codeInput" v-model="form.code" type="text" inputmode="numeric" class="lifers-auth-input" autofocus autocomplete="one-time-code" :aria-invalid="Boolean(form.errors.code)" :aria-describedby="form.errors.code ? 'code-error' : undefined" />
                        <InputError id="code-error" class="lifers-auth-error" :message="form.errors.code" />
                    </div>

                    <div v-else class="lifers-auth-field">
                        <label class="lifers-auth-label" for="recovery_code">Code de récupération</label>
                        <input id="recovery_code" ref="recoveryCodeInput" v-model="form.recovery_code" type="text" class="lifers-auth-input" autocomplete="one-time-code" :aria-invalid="Boolean(form.errors.recovery_code)" :aria-describedby="form.errors.recovery_code ? 'recovery-code-error' : undefined" />
                        <InputError id="recovery-code-error" class="lifers-auth-error" :message="form.errors.recovery_code" />
                    </div>

                    <div class="lifers-auth-actions">
                        <button type="button" class="lifers-auth-link" @click.prevent="toggleRecovery">
                            {{ recovery ? 'Utiliser un code temporaire' : 'Utiliser un code de récupération' }}
                        </button>
                        <button type="submit" class="lifers-auth-submit" :disabled="form.processing">Se connecter</button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
