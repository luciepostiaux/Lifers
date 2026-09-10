<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button :type="type" class="inline-flex min-h-[44px] items-center justify-center px-4 py-2 bg-[#9a4a5d] border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-[#aa5a6c] active:bg-[#813643] focus:outline-none focus:ring-2 focus:ring-[#9a4a5d] focus:ring-offset-2 transition ease-in-out duration-150">
        <slot />
    </button>
</template>
