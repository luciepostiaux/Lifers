<script setup>
import { ref, computed } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    persoBodies: Array,
});

const form = ref({
    first_name: "",
    last_name: "",
    perso_bodies_id: null,
});

const selectedBodyId = ref(props.persoBodies[0]?.id);

const selectedIndex = ref(0);

const displayBodies = computed(() => {
    const total = props.persoBodies.length;
    const bodies = [
        ...props.persoBodies.slice(-2),
        ...props.persoBodies,
        ...props.persoBodies.slice(0, 2),
    ];
    const middleIndex = selectedIndex.value + 2;
    return bodies.slice(middleIndex - 2, middleIndex + 3);
});

const selectBody = (index) => {
    const realIndex =
        (index + props.persoBodies.length) % props.persoBodies.length;
    selectedBodyId.value = props.persoBodies[realIndex].id;
    form.value.perso_bodies_id = props.persoBodies[realIndex].id;
    selectedIndex.value = realIndex;
};

const submitForm = () => {
    router.post(route("character.store"), form.value);
};

const moveLeft = () => {
    const newIndex =
        (selectedIndex.value - 1 + props.persoBodies.length) %
        props.persoBodies.length;
    selectBody(newIndex);
};

const moveRight = () => {
    const newIndex = (selectedIndex.value + 1) % props.persoBodies.length;
    selectBody(newIndex);
};
</script>

<template>
    <div class="flex justify-center bg-emerald-950 h-screen">
        <div class="flex justify-center items-center w-full h-full">
            <div
                class="w-[85vw] h-[85vh] overflow-y-auto text-gray-100 shadow-lg rounded-lg m-auto bg-cover bg-center"
                style="background-image: url(/images/places/ville.webp)"
            >
                <div
                    class="flex flex-col justify-between bg-emerald-900/90 p-4 rounded-lg shadow-md md:mb-0 backdrop-blur-md  m-8"
                >
                    <h1 class="text-xl font-semibold mb-4 text-white">
                        Créer un nouveau personnage
                    </h1>
                    <form @submit.prevent="submitForm">
                        <div class="grid grid-cols-3 gap-16">
                            <!-- Inputs -->
                            <div class="space-y-4">
                                <label
                                    for="first_name"
                                    class="block text-sm font-medium"
                                    >Prénom</label
                                >
                                <input
                                    type="text"
                                    v-model="form.first_name"
                                    id="first_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                                <label
                                    for="last_name"
                                    class="block text-sm font-medium"
                                    >Nom</label
                                >
                                <input
                                    type="text"
                                    v-model="form.last_name"
                                    id="last_name"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                />
                            </div>
                            <!-- Carousel -->
                            <div
                                class="col-span-2 flex justify-between items0-center"
                            >
                                <button type="button" @click="moveLeft">
                                    &#8592;
                                </button>
                                <div
                                    class="flex items-center justify-center gap-4"
                                >
                                    <div
                                        v-for="(body, index) in displayBodies"
                                        :key="body.id"
                                        :class="{
                                            'ring-2 ring-gray-200 p-4 rounded-lg shadow-md':
                                                body.id === selectedBodyId,
                                        }"
                                        @click="
                                            selectBody(
                                                index + selectedIndex - 1
                                            )
                                        "
                                        class="cursor-pointer transition-transform duration-300 ease-in-out"
                                    >
                                        <img
                                            :src="`/${body.img_perso}`"
                                            alt="Corps du personnage"
                                            class="h-80"
                                        />
                                    </div>
                                </div>
                                <button type="button" @click="moveRight">
                                    &#8594;
                                </button>
                            </div>
                        </div>
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-700 mt-4"
                        >
                            Créer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
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
    outline: 2px solid white;
}

.body-image {
    width: 100px;
    height: auto;
}
</style>
