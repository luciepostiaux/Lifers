<script setup>
import { defineProps, defineEmits } from "vue";

const props = defineProps({
    job: Object,
    isOpen: Boolean,
});

const emit = defineEmits(["close"]);

const close = () => {
    emit("close");
};
</script>

<template>
    <div
        v-if="isOpen"
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
    >
        <div class="fixed inset-0 overflow-y-auto">
            <div
                class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0"
            >
                <div
                    class="relative transform overflow-hidden rounded-lg bg-white px-4 pt-5 pb-4 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:p-6"
                >
                    <div>
                        <div class="mt-3 text-center sm:mt-0 sm:text-left">
                            <h3
                                class="text-lg font-medium leading-6 text-gray-900"
                                v-text="job.name"
                            ></h3>
                            <div class="mt-2">
                                <p
                                    class="text-sm text-gray-500"
                                    v-text="job.description_1"
                                ></p>
                                <p class="text-sm font-bold text-gray-700">
                                    Salaire: {{ job.salary }}€
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                        <button
                            @click="close"
                            type="button"
                            class="mt-3 inline-flex w-full justify-center rounded-md border border-transparent bg-[#71A4B0] px-4 py-2 text-base font-medium text-white shadow-sm hover:bg-[#9EE5F5] focus:outline-none focus:ring-2 focus:ring-[#9EE5F5] sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
