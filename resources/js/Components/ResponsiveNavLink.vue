<script setup>
import { computed } from "vue";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    active: Boolean,
    href: String,
    as: String,
});

const classes = computed(() => {
    return props.active
        ? "block ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base font-medium text-gray-200 bg-indigo-50 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out"
        : "block ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-200 hover:text-gray-800 hover:bg-gray-50 focus:outline-none focus:text-gray-800 focus:bg-gray-50 focus:border-gray-300 transition duration-150 ease-in-out";
});
</script>

<template>
    <div class="flex items-center">
        <button
            v-if="as == 'button'"
            :class="classes"
            class="w-full flex items-center"
        >
            <slot />
        </button>

        <Link v-else :href="href" :class="classes" class="flex items-center">
            <slot />
        </Link>
    </div>
</template>
