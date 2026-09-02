<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({
    lifeGauges: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <AppLayout title="Jauges de vie">
        <main class="container mx-auto p-4">
            <h1 class="text-2xl font-bold mb-4">Jauges de vie</h1>

            <p v-if="!lifeGauges">Aucune jauge disponible.</p>

            <dl v-else>
                <div
                    v-for="(value, label) in lifeGauges"
                    :key="label"
                    class="mb-3"
                >
                    <dt class="font-semibold">{{ label }}</dt>
                    <dd>{{ value }} / 100</dd>
                </div>
            </dl>
        </main>
    </AppLayout>
</template>
