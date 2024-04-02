<script setup>
import { ref, defineProps, defineEmits } from "vue";

const props = defineProps({
    conversations: Array,
});

const selectedConversationId = ref(null);
const emit = defineEmits(["selectConversation"]);

function select(conversationId) {
    selectedConversationId.value = conversationId; // Optionnel, si vous voulez garder trace de la sélection localement
    emit("selectConversation", conversationId);
}
</script>

<template>
    <div class="conversations-list">
        <div
            v-for="conversation in conversations"
            :key="conversation.id"
            @click="select(conversation.id)"
            :class="{ selected: conversation.id === selectedConversationId }"
            class="conversation"
        >
            {{ conversation.name }}
        </div>
    </div>
</template>

<style>
.selected {
    background-color: #e2e8f0; /* Exemple de couleur de fond pour la sélection */
}
</style>
