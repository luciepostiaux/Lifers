<script setup>
import { defineProps } from "vue";

import AppLayout from "@/Layouts/AppLayout.vue";

defineProps({});
</script>
<template>
    <AppLayout title="Job">
        <template #header></template>
        <div class="container mx-auto p-4"></div>
    </AppLayout>
</template>
