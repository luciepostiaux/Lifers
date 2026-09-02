<script setup>
import { watch } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import { emptyProfileDocument, profileEditorExtensions } from "./profileEditorExtensions";

const props = defineProps({
    content: { type: Object, default: null },
});

const editor = useEditor({
    content: props.content ?? emptyProfileDocument,
    editable: false,
    extensions: profileEditorExtensions(),
});

watch(
    () => props.content,
    (content) => editor.value?.commands.setContent(content ?? emptyProfileDocument),
    { deep: true },
);
</script>

<template>
    <EditorContent v-if="editor" :editor="editor" class="profile-rich-content" />
</template>

<style>
.profile-rich-content .tiptap {
    color: #55435c;
    font-size: 16px;
    line-height: 1.75;
}

.profile-rich-content .tiptap > *:first-child { margin-top: 0; }
.profile-rich-content .tiptap > *:last-child { margin-bottom: 0; }
.profile-rich-content .tiptap p { margin: 0.8em 0; }

.profile-rich-content .tiptap h2,
.profile-rich-content .tiptap h3 {
    margin: 1.2em 0 0.45em;
    color: #46324e;
    font-family: "Bricolage Grotesque", ui-sans-serif, system-ui, sans-serif;
    font-weight: 700;
    line-height: 1.15;
}

.profile-rich-content .tiptap h2 { font-size: 1.65rem; }
.profile-rich-content .tiptap h3 { font-size: 1.3rem; }

.profile-rich-content .tiptap ul,
.profile-rich-content .tiptap ol {
    margin: 0.8em 0;
    padding-left: 1.5rem;
}

.profile-rich-content .tiptap ul { list-style: disc; }
.profile-rich-content .tiptap ol { list-style: decimal; }

.profile-rich-content .tiptap blockquote {
    margin: 1.1em 0;
    padding: 0.25em 0 0.25em 1rem;
    border-left: 4px solid #d6a84a;
    color: #6f5d74;
}

.profile-rich-content .tiptap img {
    display: block;
    max-width: 100%;
    max-height: 560px;
    margin: 1.3rem auto;
    border-radius: 16px;
    object-fit: contain;
}
</style>
