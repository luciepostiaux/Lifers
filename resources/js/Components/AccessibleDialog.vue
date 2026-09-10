<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from "vue";

defineProps({
    labelledby: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(["close"]);
const dialogElement = ref(null);
let previouslyFocusedElement = null;

const focusableSelector = [
    "a[href]",
    "button:not([disabled])",
    "input:not([disabled])",
    "select:not([disabled])",
    "textarea:not([disabled])",
    "[tabindex]:not([tabindex='-1'])",
].join(",");

const focusableElements = () =>
    Array.from(dialogElement.value?.querySelectorAll(focusableSelector) ?? [])
        .filter((element) => element.getClientRects().length > 0);

const handleKeydown = (event) => {
    if (event.key === "Escape") {
        event.preventDefault();
        event.stopPropagation();
        emit("close");
        return;
    }

    if (event.key !== "Tab") {
        return;
    }

    const elements = focusableElements();

    if (elements.length === 0) {
        event.preventDefault();
        dialogElement.value?.focus();
        return;
    }

    const firstElement = elements[0];
    const lastElement = elements.at(-1);

    if (event.shiftKey && document.activeElement === firstElement) {
        event.preventDefault();
        lastElement.focus();
    } else if (!event.shiftKey && document.activeElement === lastElement) {
        event.preventDefault();
        firstElement.focus();
    }
};

onMounted(async () => {
    previouslyFocusedElement = document.activeElement;
    document.body.style.overflow = "hidden";
    await nextTick();

    const initialTarget =
        dialogElement.value?.querySelector("[autofocus]") ??
        focusableElements()[0] ??
        dialogElement.value;
    initialTarget?.focus();
});

onBeforeUnmount(() => {
    document.body.style.overflow = "";

    if (previouslyFocusedElement?.isConnected) {
        previouslyFocusedElement.focus();
    }
});
</script>

<template>
    <section
        ref="dialogElement"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="labelledby"
        tabindex="-1"
        @keydown="handleKeydown"
    >
        <slot />
    </section>
</template>
