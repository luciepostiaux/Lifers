<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3";
import AuthenticationCard from "@/Components/AuthenticationCard.vue";
import AuthenticationCardLogo from "@/Components/AuthenticationCardLogo.vue";
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    terms: false,
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Register" />
    <div class="flex justify-center bg-emerald-950 h-screen">
        <div
            class="w-[85vw] h-[85vh] custom-scrollbar overflow-y-auto text-gray-800 shadow-lg rounded-lg m-auto bg-cover bg-center"
            style="background-image: url(/images/places/ville.webp)"
        >
            <div
                class="flex justify-center items-center gap-4 md:flex-row w-full h-full"
            >
                <div
                    class="flex flex-col justify-center bg-emerald-900/90 w-3/4 h-3/4 rounded-lg shadow-md md:mb-0 backdrop-blur-md "
                >
                    <AuthenticationCard>
                        <template #logo>
                            <h1 class="font-bangers text-9xl text-gray-100">
                                Lifers
                            </h1>
                        </template>

                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Pseudo" />
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
                                    value="Confirmation mot de passe"
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

                            <div
                                v-if="
                                    $page.props.jetstream
                                        .hasTermsAndPrivacyPolicyFeature
                                "
                                class="mt-4"
                            >
                                <InputLabel for="terms">
                                    <div class="flex items-center">
                                        <Checkbox
                                            id="terms"
                                            v-model:checked="form.terms"
                                            name="terms"
                                            required
                                        />

                                        <div class="ms-2">
                                            I agree to the
                                            <a
                                                target="_blank"
                                                :href="route('terms.show')"
                                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >Terms of Service</a
                                            >
                                            and
                                            <a
                                                target="_blank"
                                                :href="route('policy.show')"
                                                class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                >Privacy Policy</a
                                            >
                                        </div>
                                    </div>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.terms"
                                    />
                                </InputLabel>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <Link
                                    :href="route('login')"
                                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
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
    </div>
</template>
