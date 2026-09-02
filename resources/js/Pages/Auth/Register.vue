<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import PasswordInput from "@/Components/PasswordInput.vue";
import SiteHeader from "@/Components/SiteHeader.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    adult_confirmation: false,
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Lifers — Créer mon Lifer">
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
                class="lifers-auth-card lifers-auth-card--register"
                aria-labelledby="register-title"
            >
                <h1 id="register-title" class="lifers-auth-title">
                    Créer mon Lifer
                </h1>
                <div class="lifers-auth-accent" aria-hidden="true"></div>

                <form class="lifers-auth-form" @submit.prevent="submit">
                    <div class="lifers-auth-field">
                        <label class="lifers-auth-label" for="name">
                            Pseudo
                        </label>
                        <input
                            id="name"
                            v-model="form.name"
                            type="text"
                            class="lifers-auth-input"
                            required
                            autofocus
                            autocomplete="name"
                            :aria-invalid="Boolean(form.errors.name)"
                            :aria-describedby="
                                form.errors.name ? 'name-error' : undefined
                            "
                        />
                        <InputError
                            id="name-error"
                            class="lifers-auth-error"
                            :message="form.errors.name"
                        />
                    </div>

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
                            autocomplete="new-password"
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

                    <div class="lifers-auth-field">
                        <label
                            class="lifers-auth-label"
                            for="password_confirmation"
                        >
                            Confirmation du mot de passe
                        </label>
                        <PasswordInput
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            input-class="lifers-auth-input"
                            required
                            autocomplete="new-password"
                            :aria-invalid="
                                Boolean(form.errors.password_confirmation)
                            "
                            :aria-describedby="
                                form.errors.password_confirmation
                                    ? 'password-confirmation-error'
                                    : undefined
                            "
                        />
                        <InputError
                            id="password-confirmation-error"
                            class="lifers-auth-error"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <div class="lifers-auth-terms">
                        <label
                            class="lifers-auth-checkline"
                            for="adult_confirmation"
                        >
                            <input
                                id="adult_confirmation"
                                v-model="form.adult_confirmation"
                                name="adult_confirmation"
                                type="checkbox"
                                class="lifers-auth-checkbox"
                                required
                                :aria-invalid="
                                    Boolean(form.errors.adult_confirmation)
                                "
                                :aria-describedby="
                                    form.errors.adult_confirmation
                                        ? 'adult-confirmation-error'
                                        : undefined
                                "
                            />
                            <span>Je confirme avoir 18 ans ou plus.</span>
                        </label>
                        <InputError
                            id="adult-confirmation-error"
                            class="lifers-auth-error"
                            :message="form.errors.adult_confirmation"
                        />
                    </div>

                    <div
                        v-if="
                            $page.props.jetstream
                                .hasTermsAndPrivacyPolicyFeature
                        "
                        class="lifers-auth-terms"
                    >
                        <label class="lifers-auth-checkline" for="terms">
                            <input
                                id="terms"
                                v-model="form.terms"
                                name="terms"
                                type="checkbox"
                                class="lifers-auth-checkbox"
                                required
                                :aria-invalid="Boolean(form.errors.terms)"
                                :aria-describedby="
                                    form.errors.terms
                                        ? 'terms-error'
                                        : undefined
                                "
                            />
                            <span>
                                J’accepte les
                                <a
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :href="route('terms.show')"
                                    class="lifers-auth-link"
                                >
                                    conditions d’utilisation
                                </a>
                                et je reconnais avoir pris connaissance de la
                                <a
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    :href="route('policy.show')"
                                    class="lifers-auth-link"
                                >
                                    politique de confidentialité
                                </a>
                                .
                            </span>
                        </label>
                        <InputError
                            id="terms-error"
                            class="lifers-auth-error"
                            :message="form.errors.terms"
                        />
                    </div>

                    <div class="lifers-auth-actions">
                        <Link
                            :href="route('login')"
                            class="lifers-auth-link"
                        >
                            Déjà un compte ?
                        </Link>

                        <button
                            type="submit"
                            class="lifers-auth-submit"
                            :disabled="form.processing"
                        >
                            Créer mon Lifer
                        </button>
                    </div>
                </form>
            </section>
        </main>
    </div>
</template>
