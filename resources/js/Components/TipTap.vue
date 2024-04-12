<script setup>
import { defineProps, ref, watch } from "vue";
import { useEditor, EditorContent } from "@tiptap/vue-3";
import StarterKit from "@tiptap/starter-kit";

const props = defineProps({
    initialContent: String,
    saveAction: Function,
});

const editor = ref(
    useEditor({
        extensions: [StarterKit],
        content: props.initialContent,
    })
);

watch(
    () => props.initialContent,
    (newValue) => {
        if (editor.value) {
            editor.value.commands.setContent(newValue);
        }
    },
    { immediate: true }
);

function saveDescription() {
    if (editor.value) {
        const content = editor.value.getHTML();
        // Si une saveAction est fournie, utilisez-la, sinon émettez un événement
        if (props.saveAction) {
            props.saveAction(content);
        } else {
            emit("update:content", content);
        }
    }
}
</script>

<template>
    <div class="editor min-h-[150px] border-2 border-gray-200 p-4">
        <div class="toolbar space-x-2 mb-2">
            <button
                @click="editor.chain().focus().toggleBold().run()"
                :class="{ 'is-active': editor?.isActive('bold') }"
            >
                Gras
            </button>
            <button
                @click="editor.chain().focus().toggleItalic().run()"
                :class="{ 'is-active': editor?.isActive('italic') }"
            >
                Italique
            </button>
            <button
                @click="editor.chain().focus().toggleUnderline().run()"
                :class="{ 'is-active': editor?.isActive('underline') }"
            >
                Souligné
            </button>
            <button
                @click="editor.chain().focus().toggleStrike().run()"
                :class="{ 'is-active': editor?.isActive('strike') }"
            >
                Barré
            </button>
            <button
                @click="editor.chain().focus().toggleBulletList().run()"
                :class="{ 'is-active': editor?.isActive('bulletList') }"
            >
                Liste à puces
            </button>
            <button
                @click="editor.chain().focus().toggleOrderedList().run()"
                :class="{ 'is-active': editor?.isActive('orderedList') }"
            >
                Liste numérotée
            </button>
            <button
                @click="editor.chain().focus().toggleBlockquote().run()"
                :class="{ 'is-active': editor?.isActive('blockquote') }"
            >
                Citation
            </button>
        </div>
        <EditorContent
            :editor="editor"
            class="editor-content bg-gray-100 border p-4"
        />
        <button @click.prevent="saveDescription(editor)">Enregistrer</button>
    </div>
</template>
