<script setup>
import { toRefs } from "vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { router } from "@inertiajs/vue3";
import LifeGauges from "@/Components/LifeGauges.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";

const props = defineProps({
    perso: Object,
    currentSicknesses: Array,
    allSicknesses: Array,
    lifeGauges: Object,
});

const { currentSicknesses, allSicknesses } = toRefs(props);

const treatSickness = (sicknessId) => {
    router.post(
        "/treat-sickness",
        { sicknessId },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const formatDate = (dateString) => {
    const options = { year: "numeric", month: "long", day: "numeric" };
    return new Date(dateString).toLocaleDateString("fr-FR", options);
};

const visitDoctor = () => {
    router.post(
        "/visit-doctor",
        {},
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
};

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-800";
    else if (value <= 50) return "bg-orange-400";
    return "bg-lime-700";
};
</script>

<template>
    <AppLayout title="Soin des maladies">
        <template #header></template>

        <div class="">
            <div
                class="flex flex-col md:flex-row mb-2 lg:mb-4 w-full h-full gap-2 lg:gap-4"
            >
                <div
                    class="flex-1 flex flex-col justify-between md:flex-auto md:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md order-2 md:order-1"
                >
                    <div
                        class="flex flex-col tracking-wide leading-relaxed gap-2"
                    >
                        <h1 class="text-xl font-bold">Lif'Hôpital</h1>
                        <p class="">
                            Prenez soin de votre personnage en lui offrant les
                            traitements nécessaires pour combattre ses maladies
                            et améliorer sa santé. Choisissez parmi notre
                            sélection de maladies courantes, telles que le
                            rhume, la grippe, ou les troubles
                            gastro-intestinaux, et payez le traitement approprié
                            pour aider votre personnage à retrouver son
                            bien-être.
                        </p>
                        <p class="">
                            Explorez notre gamme de médicaments et de remèdes,
                            soigneusement sélectionnés pour leur efficacité et
                            leur sécurité, et choisissez celui qui correspond le
                            mieux aux besoins de votre personnage. Que ce soit
                            des comprimés pour soulager les symptômes, des
                            sirops pour apaiser la toux, ou des probiotiques
                            pour rétablir l'équilibre intestinal, nous avons
                            tout ce qu'il faut pour aider votre personnage à se
                            sentir mieux.
                        </p>
                    </div>
                </div>
                <!-- Image institutionnelle sur le côté droit -->
                <div
                    class="flex-1 md:flex-auto md:w-2/5 rounded-lg shadow-md border-8 border-emerald-900 order-1 md:order-2"
                >
                    <img
                        src="/images/places/hopital_4-6.webp"
                        alt="Workplace Image"
                        class="object-cover h-full"
                    />
                </div>
            </div>
            <div class="flex gap-2 lg:gap-4">
                <div
                    class="flex-1 md:flex-auto md:w-2/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md md:mb-0"
                >
                    <div class="flex flex-col justify-between">
                        <LifeGauges :gauges="lifeGauges" />
                    </div>
                </div>
                <div
                    class="flex-1 flex flex-col md:flex-auto md:w-3/5 lg:w-3/5 bg-emerald-900/90 backdrop-blur-lg p-4 rounded-lg shadow-md"
                >
                    <div class="flex flex-col lg:flex-row justify-between mb-4">
                        <h2 class="text-lg mb-4">
                            {{
                                currentSicknesses.length <= 1
                                    ? "Maladie active"
                                    : "Maladies actives"
                            }}
                        </h2>
                        <PrimaryButton @click="visitDoctor">
                            Soigner votre jauge de santé (200 LC)
                        </PrimaryButton>
                    </div>
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 gap-2 lg:gap-4 mb-8"
                    >
                        <div
                            v-for="sickness in currentSicknesses"
                            :key="sickness.id"
                            @click="treatSickness(sickness.id)"
                            class="relative flex flex-col bg-emerald-950 backdrop-blur-lg hover:bg-emerald-950/50 rounded-lg shadow-md transition-all duration-300 ease-in-out cursor-pointer py-4 p-4"
                        >
                            <div
                                class="flex-grow flex flex-col justify-between"
                            >
                                <h4 class="text-sm font-semibold text-red-500">
                                    {{ sickness.name }}
                                </h4>
                                <!-- <p class="text-gray-600 mt-1">
                                    Contracté le:
                                    {{ formatDate(sickness.pivot.created_at) }}
                                </p> -->
                                <p class="text-sm font-light mt-1">
                                    {{ sickness.description }}
                                </p>
                                <span class="flex gap-2 text-sm lg:mt-4">
                                    <p class="text-gray-300">
                                        Coût du traitement:
                                    </p>
                                    <p class="text-amber-500 font-medium">
                                        {{ sickness.treatment_cost }} LC
                                    </p>
                                </span>
                            </div>
                            <div class="flex self-end items-end gap-2">
                                <p class="text-xs">Cliquez pour vous soigner</p>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    xmlns:xlink="http://www.w3.org/1999/xlink"
                                    viewBox="0 0 256 256"
                                    class="size-6 transition-opacity duration-300 ease-in-out"
                                >
                                    <defs></defs>
                                    <g
                                        transform="translate(1.4065934065934016 1.4065934065934016) scale(2.81 2.81)"
                                    >
                                        <path
                                            d="M 69.416 43.298 H 68.97 c -0.983 0 -1.917 0.216 -2.756 0.603 c -0.644 -2.975 -3.295 -5.21 -6.459 -5.21 h -0.447 c -1.128 0 -2.19 0.284 -3.12 0.784 c -1 -2.379 -3.355 -4.054 -6.094 -4.054 h -0.447 c -0.925 0 -1.807 0.191 -2.606 0.536 v -9.458 c 0 -3.644 -2.964 -6.607 -6.608 -6.607 h -0.447 c -3.643 0 -6.607 2.964 -6.607 6.607 v 24.261 l -3.005 2.281 c -2.394 1.817 -3.911 4.461 -4.273 7.444 c -0.362 2.984 0.479 5.914 2.37 8.251 l 9.378 11.594 v 4.608 c 0 2.791 2.271 5.063 5.062 5.063 h 23.411 c 2.791 0 5.063 -2.271 5.063 -5.063 l 0.001 -4.375 c 2.996 -3.766 4.639 -8.438 4.639 -13.242 V 49.905 C 76.023 46.262 73.06 43.298 69.416 43.298 z M 72.023 67.32 c 0 4.102 -1.478 8.088 -4.159 11.224 c -0.311 0.362 -0.48 0.823 -0.48 1.3 v 5.094 c 0 0.586 -0.477 1.063 -1.063 1.063 H 42.911 c -0.585 0 -1.062 -0.477 -1.062 -1.063 v -5.316 c 0 -0.458 -0.157 -0.902 -0.445 -1.258 L 31.581 66.22 c -1.204 -1.488 -1.74 -3.354 -1.509 -5.254 c 0.23 -1.899 1.197 -3.583 2.721 -4.74 l 0.586 -0.444 v 5.495 c 0 1.104 0.896 2 2 2 s 2 -0.896 2 -2 v -9.506 c 0 -0.014 0 -0.026 0 -0.04 V 26.498 c 0 -1.438 1.169 -2.607 2.607 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 15.53 c 0 0 0 0 0 0.001 s 0 0 0 0.001 l 0.01 12.917 c 0.001 1.104 0.896 1.998 2 1.998 c 0 0 0.001 0 0.001 0 c 1.104 -0.001 1.999 -0.896 1.998 -2.002 L 47.04 42.028 c 0 -1.438 1.169 -2.607 2.606 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 3.27 c 0 0 0 0.001 0 0.001 c 0 0 0 0.001 0 0.001 l 0.009 9.647 c 0.001 1.104 0.896 1.998 2 1.998 c 0.001 0 0.001 0 0.002 0 c 1.104 -0.001 1.999 -0.897 1.998 -2.002 l -0.009 -9.646 c 0 -1.438 1.169 -2.607 2.606 -2.607 h 0.447 c 1.438 0 2.607 1.17 2.607 2.607 v 4.088 c -0.006 0.066 -0.02 0.13 -0.02 0.197 l 0.01 5.366 c 0.002 1.104 0.897 1.996 2 1.996 c 0.001 0 0.002 0 0.004 0 c 1.104 -0.002 1.998 -0.899 1.996 -2.004 l -0.009 -4.852 c 0.006 -0.062 0.019 -0.121 0.019 -0.184 c 0 -1.438 1.17 -2.607 2.607 -2.607 h 0.446 c 1.438 0 2.607 1.17 2.607 2.607 V 67.32 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M 63.994 25.511 H 51.79 c -1.104 0 -2 -0.896 -2 -2 s 0.896 -2 2 -2 h 12.204 c 1.104 0 2 0.896 2 2 S 65.099 25.511 63.994 25.511 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M 39.985 16.204 c -1.104 0 -2 -0.896 -2 -2 V 2 c 0 -1.104 0.896 -2 2 -2 s 2 0.896 2 2 v 12.204 C 41.985 15.309 41.09 16.204 39.985 16.204 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M 48.558 18.441 c -0.512 0 -1.023 -0.195 -1.414 -0.586 c -0.781 -0.781 -0.781 -2.047 0 -2.828 l 8.63 -8.629 c 0.781 -0.781 2.047 -0.781 2.828 0 c 0.781 0.781 0.781 2.047 0 2.828 l -8.63 8.629 C 49.581 18.246 49.069 18.441 48.558 18.441 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M 28.181 25.511 H 15.977 c -1.104 0 -2 -0.896 -2 -2 s 0.896 -2 2 -2 h 12.204 c 1.104 0 2 0.896 2 2 S 29.285 25.511 28.181 25.511 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                        <path
                                            d="M 31.413 18.441 c -0.512 0 -1.024 -0.195 -1.414 -0.586 L 21.37 9.226 c -0.781 -0.781 -0.781 -2.047 0 -2.828 c 0.78 -0.781 2.048 -0.781 2.828 0 l 8.629 8.629 c 0.781 0.781 0.781 2.047 0 2.828 C 32.437 18.246 31.925 18.441 31.413 18.441 z"
                                            style="
                                                stroke: none;
                                                stroke-width: 1;
                                                stroke-dasharray: none;
                                                stroke-linecap: butt;
                                                stroke-linejoin: miter;
                                                stroke-miterlimit: 10;
                                                fill: currentColor;
                                                fill-rule: nonzero;
                                                opacity: 1;
                                            "
                                            transform=" matrix(1 0 0 1 0 0) "
                                            stroke-linecap="round"
                                        />
                                    </g>
                                </svg>
                            </div>
                            <!-- <div class="mt-4">
                                <button
                                    @click="treatSickness(sickness.id)"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                                >
                                    Se soigner
                                </button>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <h2 class="text-xl font-bold">Liste des Maladies Disponibles</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                    v-for="sickness in allSicknesses"
                    :key="sickness.id"
                    class="bg-emerald-900/90 backdrop-blur-lg  p-4 rounded-lg shadow-md flex flex-col"
                >
                    <div class="flex-grow">
                        <h4 class="text-md font-semibold">
                            {{ sickness.name }}
                        </h4>
                        <p class="text-gray-600 mt-1">
                            {{ sickness.description }}
                        </p>
                    </div>
                    <div class="mt-4">
                        <div class="text-lg font-bold">
                            Coût du traitement: {{ sickness.treatment_cost }} €
                        </div>
                    </div>
                </div>
            </div> -->
    </AppLayout>
</template>
