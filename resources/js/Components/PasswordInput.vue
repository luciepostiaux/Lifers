<script setup>
import { computed, onMounted, ref, useAttrs } from 'vue';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    modelValue: {
        type: String,
        default: '',
    },
    inputClass: {
        type: [String, Array, Object],
        default: 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm',
    },
});

const emit = defineEmits(['update:modelValue']);
const attrs = useAttrs();
const input = ref(null);
const isVisible = ref(false);

const forwardedAttributes = computed(() => {
    const { class: _className, style: _style, ...attributes } = attrs;

    return attributes;
});

const isDisabled = computed(() => (
    Object.prototype.hasOwnProperty.call(attrs, 'disabled')
    && attrs.disabled !== false
));

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({
    focus: () => input.value?.focus(),
});
</script>

<template>
    <div class="lifers-password-input" :class="$attrs.class" :style="$attrs.style">
        <input
            ref="input"
            v-bind="forwardedAttributes"
            :type="isVisible ? 'text' : 'password'"
            :value="props.modelValue"
            :class="['lifers-password-input__control', inputClass]"
            @input="emit('update:modelValue', $event.target.value)"
        />

        <button
            type="button"
            class="lifers-password-input__toggle"
            :aria-controls="$attrs.id"
            :aria-label="isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
            :aria-pressed="isVisible"
            :disabled="isDisabled"
            :title="isVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
            @mousedown.prevent
            @click="isVisible = !isVisible"
        >
            <svg
                v-if="!isVisible"
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M2.6 12s3.4-6 9.4-6 9.4 6 9.4 6-3.4 6-9.4 6-9.4-6-9.4-6Z" />
                <circle cx="12" cy="12" r="2.8" />
            </svg>

            <svg
                v-else
                aria-hidden="true"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="1.8"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <path d="M3 3l18 18" />
                <path d="M10.6 6.1A8.6 8.6 0 0 1 12 6c6 0 9.4 6 9.4 6a14.7 14.7 0 0 1-2.3 3" />
                <path d="M6.2 6.3C3.8 8 2.6 12 2.6 12s3.4 6 9.4 6a9 9 0 0 0 3.2-.6" />
                <path d="M10 10a2.8 2.8 0 0 0 4 4" />
            </svg>
        </button>
    </div>
</template>

<style scoped>
.lifers-password-input {
    position: relative;
}

.lifers-password-input__control {
    display: block;
    width: 100%;
    padding-inline-end: 3.25rem !important;
}

.lifers-password-input__toggle {
    position: absolute;
    top: 50%;
    right: 0.45rem;
    display: inline-flex;
    width: 2.5rem;
    height: 2.5rem;
    align-items: center;
    justify-content: center;
    padding: 0;
    color: #4c3654;
    background: transparent;
    border: 0;
    border-radius: 0.75rem;
    transform: translateY(-50%);
    cursor: pointer;
    transition: color 160ms ease, background-color 160ms ease;
}

.lifers-password-input__toggle:hover {
    color: #322139;
    background: rgb(76 54 84 / 8%);
}

.lifers-password-input__toggle:focus-visible {
    outline: 2px solid #dcae46;
    outline-offset: 1px;
}

.lifers-password-input__toggle:disabled {
    cursor: not-allowed;
    opacity: 0.45;
}

.lifers-password-input__toggle svg {
    width: 1.35rem;
    height: 1.35rem;
}

@media (prefers-reduced-motion: reduce) {
    .lifers-password-input__toggle {
        transition: none;
    }
}
</style>
