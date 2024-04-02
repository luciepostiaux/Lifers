<script setup>
import { ref } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    persoBodies: Array,
});

const form = ref({
    first_name: "",
    last_name: "",
    perso_bodies_id: null,
});

const selectedBodyId = ref(null);
const selectBody = (bodyId) => {
    selectedBodyId.value = bodyId;
    form.value.perso_bodies_id = bodyId; 
};

const submitForm = () => {
    Inertia.post(route("character.store"), form.value);
};
</script>

<template>
    <AppLayout title="Create Character">
        <div class="container mx-auto p-4">
            <h1 class="text-xl font-semibold mb-4">
                Créer un nouveau personnage
            </h1>
            <form @submit.prevent="submitForm">
                <div class="mb-4">
                    <label
                        for="first_name"
                        class="block text-sm font-medium text-gray-700"
                        >Prénom</label
                    >
                    <input
                        type="text"
                        v-model="form.first_name"
                        id="first_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    />
                </div>
                <div class="mb-4">
                    <label
                        for="last_name"
                        class="block text-sm font-medium text-gray-700"
                        >Nom</label
                    >
                    <input
                        type="text"
                        v-model="form.last_name"
                        id="last_name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                    />
                </div>
                <div class="bodies-container">
                    <div
                        v-for="body in props.persoBodies"
                        :key="body.id"
                        class="body-option"
                        :class="{ selected: body.id === selectedBodyId }"
                        @click="selectBody(body.id)"
                    >
                        <img
                            :src="`/${body.img_perso}`"
                            alt="Corps du personnage"
                            class="h-64"
                        />
                    </div>
                </div>
                <button
                    type="submit"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700"
                >
                    Créer
                </button>
            </form>
        </div>
    </AppLayout>
</template>
<style scoped>
.body-option {
    cursor: pointer;
    transition: transform 0.3s ease;
}

.body-option:hover {
    transform: scale(1.05);
}

.body-option.selected {
    outline: 2px solid blue;
}

.body-image {
    width: 100px;
    height: auto;
}
</style>
