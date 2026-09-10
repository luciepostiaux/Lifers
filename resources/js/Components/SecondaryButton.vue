<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex min-h-[44px] items-center px-4 py-2 bg-[#fcf8f2] border border-[#46324e]/20 rounded-md font-semibold text-xs text-[#46324e] uppercase tracking-widest shadow-sm hover:bg-[#f4eee5] focus:outline-none focus:ring-2 focus:ring-[#d6a84a] focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
