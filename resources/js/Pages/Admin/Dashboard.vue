<script setup>
import { ref } from "vue";
import { Inertia } from "@inertiajs/inertia";

const userIdForAssign = ref("");
const diplomaIdForAssign = ref("");

const assignDiploma = () => {
    Inertia.post("/admin/grant-diploma", {
        userId: userIdForAssign.value,
        diplomaId: diplomaIdForAssign.value,
    });
};

const userIdForRemove = ref("");
const diplomaIdForRemove = ref("");

const removeDiploma = () => {
    if (!userIdForRemove.value || !diplomaIdForRemove.value) {
        alert("Veuillez remplir les champs ID Utilisateur et ID Diplôme.");
        return;
    }

    Inertia.post("/admin/remove-diploma", {
        userId: userIdForRemove.value,
        diplomaId: diplomaIdForRemove.value,
    });
};
</script>

<template>
    <AppLayout title="Administration Dashboard">
        <div class="container mx-auto p-4">
            <div
                v-if="$page.props.flash.success"
                class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4"
                role="alert"
            >
                <p class="font-bold">Succès</p>
                <p>{{ $page.props.flash.success }}</p>
            </div>
            <div
                v-if="$page.props.flash.error"
                class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4"
                role="alert"
            >
                <p class="font-bold">Erreur</p>
                <p>{{ $page.props.flash.error }}</p>
            </div>
            <div
                v-if="$page.props.flash.warning"
                class="bg-orange-100 border-l-4 border-amber-500 text-amber-700 p-4 mb-4"
                role="alert"
            >
                <p class="font-bold">Attention</p>
                <p>{{ $page.props.flash.warning }}</p>
            </div>

            <h2 class="text-2xl font-bold mb-4">
                Tableau de bord administrateur
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Actions administratives -->
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-semibold pb-2">Gestion des diplômes</h3>
                    <div class="flex items-center mb-4">
                        <input
                            type="number"
                            v-model="userIdForAssign"
                            placeholder="ID Utilisateur"
                            class="mr-2 p-2 border rounded"
                        />
                        <input
                            type="number"
                            v-model="diplomaIdForAssign"
                            placeholder="ID Diplôme"
                            class="mr-2 p-2 border rounded"
                        />
                        <button
                            @click="assignDiploma"
                            class="px-4 py-2 text-sm bg-green-500 hover:bg-green-700 text-white rounded transition-all"
                        >
                            Attribuer un diplôme
                        </button>
                    </div>
                    <div class="flex items-center mb-4">
                        <input
                            v-model="userIdForRemove"
                            type="number"
                            placeholder="ID Utilisateur"
                            class="mr-2 p-2 border rounded"
                        />
                        <input
                            v-model="diplomaIdForRemove"
                            type="number"
                            placeholder="ID Diplôme"
                            class="mr-2 p-2 border rounded"
                        />
                        <button
                            @click="removeDiploma"
                            class="px-4 py-2 text-sm bg-red-500 hover:bg-red-700 text-white rounded transition-all"
                        >
                            Retirer un diplôme
                        </button>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Actions administratives -->
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-semibold pb-2">Gestion de l'argent</h3>

                    <button
                        class="px-4 py-2 text-sm bg-green-500 hover:bg-green-700 text-white rounded transition-all mb-2"
                    >
                        Ajouter des Lif'coins
                    </button>
                    <button
                        class="px-4 py-2 text-sm bg-red-500 hover:bg-red-700 text-white rounded transition-all mb-2"
                    >
                        Retirer des Lif'coins
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Actions administratives -->
                <div class="bg-white p-4 rounded-lg shadow-md">
                    <h3 class="font-semibold pb-2">
                        Gestion des jauges de vies
                    </h3>

                    <button
                        class="px-4 py-2 text-sm bg-blue-500 hover:bg-blue-700 text-white rounded transition-all"
                    >
                        Définir les jauges de vie
                    </button>
                </div>

                <!-- D'autres sections administratives peuvent être ajoutées ici -->
            </div>
        </div>
    </AppLayout>
</template>
