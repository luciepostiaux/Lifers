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

const genderFilter = ref(1);

const filteredBodies = computed(() => {
    return props.persoBodies.filter(
        (body) => body.genders_id === genderFilter.value
    );
});

const selectBody = (body) => {
    form.value.perso_bodies_id = body.id;
};

const submitForm = () => {
    router.post(route("character.store"), form.value);
};

function setGenderFilter(genderId) {
    genderFilter.value = genderId;
}
</script>

<template>
    <div class="flex justify-center bg-emerald-950 h-screen">
        <div class="flex justify-center items-center w-full h-full">
            <div
                class="w-[85vw] h-[85vh] overflow-y-auto text-gray-200 shadow-lg rounded-lg m-auto bg-cover bg-center"
                style="background-image: url(/images/places/ville.webp)"
            >
                <div
                    class="flex bg-emerald-900/90 p-4 rounded-lg shadow-md md:mb-0 backdrop-blur-lg m-8 gap-4"
                >
                    <div class="flex flex-col lg:w-1/2">
                        <h1 class="text-xl font-semibold mb-2">
                            Créer un nouveau personnage
                        </h1>
                        <p class="tracking-wide leading-relaxed text-sm">
                            Ici, vous pouvez choisir le nom et le prénom de
                            votre Lifer. Une fois sélectionné, celui-ci sera
                            définitif et ne pourra pas être modifié
                            ultérieurement. Vous avez également la possibilité
                            de choisir le personnage qui vous représentera. Bien
                            que la fonctionnalité de personnalisation ne soit
                            pas encore disponible étant donné que le jeu est
                            actuellement en développement, vous pourrez tout de
                            même choisir parmi plusieurs options préétablies.
                        </p>
                    </div>
                    <div class="flex flex-col justify-between lg:w-1/2">
                        <form @submit.prevent="submitForm">
                            <!-- Inputs -->
                            <div class="space-y-2">
                                <label for="first_name" class="block text-sm"
                                    >Prénom de votre Lifer</label
                                >
                                <input
                                    type="text"
                                    v-model="form.first_name"
                                    id="first_name"
                                    class="mt-1 block w-full border-gray-300 focus:border-amber-600 focus:ring-amber-600 rounded-md shadow-sm text-slate-900"
                                />
                                <label
                                    for="last_name"
                                    class="block text-sm pt-4"
                                    >Nom de votre Lifer</label
                                >
                                <input
                                    type="text"
                                    v-model="form.last_name"
                                    id="last_name"
                                    class="mt-1 block w-full border-gray-300 focus:border-amber-600 focus:ring-amber-600 rounded-md shadow-sm text-slate-900"
                                />
                            </div>
                            <!-- Carousel -->
                            <label for="last_name" class="block text-sm pt-4"
                                >Choisissez le personnage qui vous
                                représentera</label
                            >
                            <div
                                class="flex justify-center items-center mt-4 gap-4"
                            >
                                <button
                                    @click="setGenderFilter(1)"
                                    class="bg-pink-400 hover:bg-pink-500 text-slate-900 font-semibold py-2 px-4 rounded text-sm"
                                >
                                    Physique A
                                </button>
                                <button
                                    @click="setGenderFilter(2)"
                                    class="bg-sky-400 hover:bg-sky-500 text-slate-900 font-semibold py-2 px-4 rounded text-sm"
                                >
                                    Physique B
                                </button>
                            </div>
                            <div class="flex items-center justify-center gap-4">
                                <div class="grid grid-cols-4 gap-4 mt-4">
                                    <div
                                        v-for="body in filteredBodies"
                                        :key="body.id"
                                        @click="selectBody(body)"
                                        :class="{
                                            'border-4 border-amber-500':
                                                body.id ===
                                                form.perso_bodies_id,
                                        }"
                                        class="cursor-pointer transition duration-300 ease-in-out transform hover:scale-105 rounded-lg"
                                    >
                                        <img
                                            :src="`/${body.img_perso}`"
                                            :alt="body.description"
                                            class="h-80 w-full object-contain"
                                        />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end mt-4">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-amber-500 border border-transparent rounded-md font-semibold text-xs text-slate-900 uppercase tracking-widest hover:bg-amber-600 focus:bg-amber-600 active:bg-amber-600 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    Créer mon Lifer
                                </button>
                            </div>
                        </form>
                    </div>
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
