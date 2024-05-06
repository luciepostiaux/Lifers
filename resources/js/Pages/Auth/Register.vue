<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import InputLabel from "@/Components/InputLabel.vue";
import InputError from "@/Components/InputError.vue";
import TextInput from "@/Components/TextInput.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import Checkbox from "@/Components/Checkbox.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
    age_confirm: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="S'enregistrer" />
    <div class="flex justify-center bg-emerald-950 h-screen">
        <div
            class="lg:w-[85vw] lg:h-[85vh] w-full h-full custom-scrollbar overflow-y-auto text-gray-800 shadow-lg rounded-lg m-auto bg-cover bg-center"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div
                class="flex justify-center items-center gap-4 md:flex-row w-full h-full"
            >
                <div
                    class="flex flex-col justify-center bg-emerald-900/90 w-4/5 rounded-lg shadow-md p-4 backdrop-blur-lg"
                >
                    <AuthenticationCard>
                        <template #logo>
                            <Link :href="route('welcome')" class="no-underline">
                                <h1
                                    class="font-bangers text-9xl text-gray-100 cursor-pointer"
                                >
                                    Lifers
                                </h1>
                            </Link>
                        </template>

                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Identifiant" />
                                <TextInput
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1 block w-full bg-slate-100"
                                    required
                                    autofocus
                                    autocomplete="name"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.name"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full bg-slate-100"
                                    required
                                    autocomplete="username"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.email"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel
                                    for="password"
                                    value="Mot de passe"
                                />
                                <TextInput
                                    id="password"
                                    v-model="form.password"
                                    type="password"
                                    class="mt-1 block w-full bg-slate-100"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password"
                                />
                            </div>

                            <div class="mt-4">
                                <InputLabel
                                    for="password_confirmation"
                                    value="Confirmation du mot de passe"
                                />
                                <TextInput
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    type="password"
                                    class="mt-1 block w-full bg-slate-100"
                                    required
                                    autocomplete="new-password"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password_confirmation"
                                />
                            </div>

                            <!-- Case pour accepter les CGU -->
                            <div class="mt-4 flex items-center">
                                <Checkbox
                                    id="terms"
                                    v-model:checked="form.terms"
                                    required
                                />
                                <InputLabel for="terms" class="ms-2">
                                    J'accepte les
                                    <a
                                        href="/mentions-legales"
                                        class="underline text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Conditions Générales d'Utilisation
                                    </a>
                                    et la
                                    <a
                                        href="/mentions-legales"
                                        class="underline text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Politique de Confidentialité
                                    </a>
                                </InputLabel>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.terms"
                                />
                            </div>

                            <!-- Case pour confirmer l'âge -->
                            <div class="mt-4 flex items-center">
                                <Checkbox
                                    id="age_confirm"
                                    v-model:checked="form.age_confirm"
                                    required
                                />
                                <InputLabel for="age_confirm" class="ms-2">
                                    Je confirme que j'ai 16 ans ou plus.
                                </InputLabel>
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.age_confirm"
                                />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('login')"
                                    class="underline text-sm text-gray-600 hover:text-gray-900"
                                >
                                    Déjà un compte?
                                </Link>

                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    S'enregistrer
                                </PrimaryButton>
                            </div>
                        </form>
                    </AuthenticationCard>
                </div>
            </div>
        </div>
        <footer class="absolute inset-x-0 bottom-0 text-gray-200 text-sm mb-4">
            <div
                class="container mx-auto flex justify-center items-center space-x-2"
            >
                <span> {{ new Date().getFullYear() }} Lifers </span>
                <p>|</p>
                <nav>
                    <a href="/mentions-legales" class="hover:underline">
                        Mentions légales</a
                    >
                </nav>
            </div>
        </footer>
    </div>
</template>
