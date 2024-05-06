<script setup>
import { defineProps } from "vue";

const props = defineProps({
    gauges: {
        type: Object,
        required: true,
    },
});

const gaugeColor = (value) => {
    if (value <= 20) return "bg-red-700";
    else if (value <= 50) return "bg-amber-500";
    return "bg-green-600";
};
</script>

<template>
    <h2 class="text-lg mb-2">Jauges de vie</h2>

    <div v-for="(value, key) in props.gauges" :key="key" class="mb-4 space-y-1">
        <div class="mb-1 text-sm mt-1">{{ key }}</div>
        <div class="w-full bg-gray-200/80 rounded-full h-4">
            <div
                :class="gaugeColor(value)"
                :style="{ width: `${value}%` }"
                class="h-4 rounded-full text-center"
            ></div>
        </div>
    </div>
</template>

<style scoped>
/* Ajoute du style si nécessaire */
</style>
