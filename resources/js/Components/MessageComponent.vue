<script setup>
import { ref, defineProps } from "vue";
import { Inertia } from "@inertiajs/inertia";

const props = defineProps({
    messages: Array,
    conversationId: Number,
});

const newMessage = ref("");

const sendMessage = () => {
    if (newMessage.value.trim() !== "") {
        Inertia.post(`/chat/conversations/${props.conversationId}/send`, {
            content: newMessage.value,
        });
        newMessage.value = ""; // Réinitialiser après l'envoi
    }
};
</script>

<template>
    <div class="message-area">
        <ul class="message-list">
            <li v-for="message in messages" :key="message.id">
                <div class="message">{{ message.content }}</div>
            </li>
        </ul>
        <input
            type="text"
            v-model="newMessage"
            @keyup.enter="sendMessage"
            placeholder="Type a message..."
        />
    </div>
</template>
