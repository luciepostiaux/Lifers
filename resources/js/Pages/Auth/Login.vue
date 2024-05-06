<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

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
    <Head title="Log in" />
    <div class="flex flex-col static justify-center bg-emerald-950 h-screen">
        <div
            class="lg:w-[85vw] lg:h-[85vh] w-full h-full custom-scrollbar overflow-y-auto text-gray-800 shadow-lg lg:rounded-lg m-auto bg-cover bg-center"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div
                class="flex justify-center items-center gap-4 md:flex-row w-full h-full"
            >
                <div
                    class="flex flex-col justify-center bg-emerald-900/90 md:w-3/4 h-4/5 rounded-lg shadow-md backdrop-blur-lg p-4"
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

                        <div
                            v-if="status"
                            class="mb-4 font-medium text-sm text-green-600"
                        >
                            {{ status }}
                        </div>

                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="email" value="Email" />
                                <TextInput
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    class="mt-1 block w-full bg-slate-100"
                                    required
                                    autofocus
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
                                    autocomplete="current-password"
                                />
                                <InputError
                                    class="mt-2"
                                    :message="form.errors.password"
                                />
                            </div>

                            <div class="block mt-4">
                                <label class="flex items-center">
                                    <Checkbox
                                        v-model:checked="form.remember"
                                        name="remember"
                                    />
                                    <span class="ms-2 text-sm text-gray-600"
                                        >Se souvenir de moi</span
                                    >
                                </label>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    v-if="canResetPassword"
                                    :href="route('password.request')"
                                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500 focus:bg-white p-1"
                                >
                                    Mot de passe oublié?
                                </Link>

                                <PrimaryButton
                                    class="ms-4"
                                    :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing"
                                >
                                    Se connecter
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
