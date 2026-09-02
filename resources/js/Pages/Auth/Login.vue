<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import PasswordInput from "@/Components/PasswordInput.vue";
import SiteHeader from "@/Components/SiteHeader.vue";

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        remember: form.remember ? "on" : "",
    })).post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Lifers — Se connecter">
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=bricolage-grotesque:700,800|dm-sans:400,500,600,700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div class="lifers-auth-page">
        <div class="lifers-auth-scene" aria-hidden="true">
            <img
                src="/images/landing/hero-lifers.png"
                alt=""
                width="1672"
                height="941"
                decoding="async"
            />
        </div>

        <SiteHeader />

        <main class="lifers-auth-main">
            <section
                class="lifers-auth-card"
                aria-labelledby="login-title"
            >
                <h1 id="login-title" class="lifers-auth-title">
                    Se connecter
                </h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>

                <div
                    v-if="status"
                    class="lifers-auth-status"
                    role="status"
                >
                    {{ status }}
                </div>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="email">
                            Email
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="lifers-auth-input"
                            required
                            autofocus
                            autocomplete="username"
                            :aria-invalid="Boolean(form.errors.email)"
                            :aria-describedby="
                                form.errors.email ? 'email-error' : undefined
                            "
                        />
                        <InputError
                            id="email-error"
                            class="lifers-auth-error"
                            :message="form.errors.email"
                        />
                    </div>

                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="password">
                            Mot de passe
                        </label>
                        <PasswordInput
                            id="password"
                            v-model="form.password"
                            input-class="lifers-auth-input"
                            required
                            autocomplete="current-password"
                            :aria-invalid="Boolean(form.errors.password)"
                            :aria-describedby="
                                form.errors.password
                                    ? 'password-error'
                                    : undefined
                            "
                        />
                        <InputError
                            id="password-error"
                            class="lifers-auth-error"
                            :message="form.errors.password"
                        />
                    </div>

                    <label class="lifers-auth-checkline">
                        <input
                            v-model="form.remember"
                            name="remember"
                            type="checkbox"
                            class="lifers-auth-checkbox"
                        />
                        <span>Se souvenir de moi</span>
                    </label>

                    <div class="lifers-auth-actions">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="lifers-auth-link"
                        >
                            Mot de passe oublié ?
                        </Link>

                        <button
                            type="submit"
                            class="lifers-auth-submit"
                            :disabled="form.processing"
                        >
                            Se connecter
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
