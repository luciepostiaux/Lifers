<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import DangerButton from '@/Components/DangerButton.vue';
import DialogModal from '@/Components/DialogModal.vue';
import InputError from '@/Components/InputError.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    setTimeout(() => passwordInput.value.focus(), 250);
};

const deleteUser = () => {
    form.delete(route('current-user.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.reset();
};
</script>

<template>
    <ActionSection>
        <template #title>
            Supprimer le compte
        </template>

        <template #description>
            Supprime définitivement ton compte et toutes les données associées.
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600">
                Cette action est irréversible. Toutes les données du compte, du Lifer et de ses vies seront définitivement supprimées.
            </div>

            <div class="mt-5">
                <DangerButton @click="confirmUserDeletion">
                    Supprimer le compte
                </DangerButton>
            </div>

            <!-- Delete Account Confirmation Modal -->
            <DialogModal :show="confirmingUserDeletion" @close="closeModal">
                <template #title>
                    Supprimer le compte
                </template>

                <template #content>
                    Confirme la suppression définitive en saisissant ton mot de passe. Cette action ne pourra pas être annulée.

                    <div class="mt-4">
                        <PasswordInput
                            ref="passwordInput"
                            v-model="form.password"
                            class="mt-1 block w-3/4"
                            placeholder="Mot de passe"
                            aria-label="Mot de passe actuel"
                            autocomplete="current-password"
                            @keyup.enter="deleteUser"
                        />

                        <InputError :message="form.errors.password" class="mt-2" />
                    </div>
                </template>

                <template #footer>
                    <SecondaryButton @click="closeModal">
                        Annuler
                    </SecondaryButton>

                    <DangerButton
                        class="ms-3"
                        :class="{ 'opacity-25': form.processing }"
                        :disabled="form.processing"
                        @click="deleteUser"
                    >
                        Supprimer définitivement
                    </DangerButton>
                </template>
            </DialogModal>
        </template>
    </ActionSection>
</template>
