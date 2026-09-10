<script setup>
defineProps({
    type: {
        type: String,
        default: 'submit',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex min-h-[44px] items-center px-4 py-2 bg-[#46324e] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#57405f] focus:bg-[#57405f] active:bg-[#322139] focus:outline-none focus:ring-2 focus:ring-[#d6a84a] focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
