<script setup>
import { onBeforeUnmount, ref, watch } from "vue";
import { EditorContent, useEditor } from "@tiptap/vue-3";
import axios from "axios";
import { emptyProfileDocument, profileEditorExtensions } from "./profileEditorExtensions";

const props = defineProps({
    modelValue: { type: Object, default: null },
    allowImages: { type: Boolean, default: true },
});

const emit = defineEmits(["update:modelValue"]);
const imageInput = ref(null);
const uploadingImage = ref(false);
const uploadError = ref("");
const allowedImageTypes = new Set(["image/jpeg", "image/png", "image/webp"]);
const maximumImageSize = 5 * 1024 * 1024;

const editor = useEditor({
    content: props.modelValue ?? emptyProfileDocument,
    extensions: profileEditorExtensions(),
    editorProps: {
        attributes: { "aria-label": "Contenu de ta présentation" },
        handlePaste: (_view, event) => handleImageTransfer(event, event.clipboardData),
        handleDrop: (view, event) => {
            const position = view.posAtCoords({ left: event.clientX, top: event.clientY })?.pos;
            return handleImageTransfer(event, event.dataTransfer, position);
        },
    },
    onUpdate: ({ editor: currentEditor }) => {
        emit("update:modelValue", currentEditor.getJSON());
    },
});

watch(
    () => props.modelValue,
    (content) => {
        if (!editor.value || JSON.stringify(editor.value.getJSON()) === JSON.stringify(content)) return;
        editor.value.commands.setContent(content ?? emptyProfileDocument, { emitUpdate: false });
    },
    { deep: true },
);

onBeforeUnmount(() => editor.value?.destroy());

function chooseImage() {
    uploadError.value = "";
    imageInput.value?.click();
}

async function uploadImage(event) {
    const file = event.target.files?.[0];
    event.target.value = "";
    await uploadFile(file);
}

function handleImageTransfer(event, transfer, position = null) {
    const file = Array.from(transfer?.files ?? []).find(({ type }) =>
        type.startsWith("image/"),
    );

    if (file) {
        if (!props.allowImages) {
            event.preventDefault();
            uploadError.value = "La modération peut retirer des images, mais pas en ajouter.";
            return true;
        }

        event.preventDefault();
        void uploadFile(file, position);
        return true;
    }

    const html = transfer?.getData?.("text/html") ?? "";
    const dataImage = html.match(/src=["'](data:image\/(?:png|jpe?g|webp);base64,[^"']+)["']/i)?.[1];

    if (dataImage) {
        event.preventDefault();
        void fileFromDataUrl(dataImage)
            .then((clipboardFile) => uploadFile(clipboardFile, position))
            .catch(() => {
                uploadError.value = "L’image copiée n’a pas pu être lue. Utilise le bouton Image pour la sélectionner.";
            });
        return true;
    }

    if (/<img\b/i.test(html)) {
        event.preventDefault();
        uploadError.value = "Cette image distante ne peut pas être collée directement. Enregistre-la d’abord, puis utilise le bouton Image.";
        return true;
    }

    return false;
}

async function fileFromDataUrl(dataUrl) {
    const response = await fetch(dataUrl);
    const blob = await response.blob();
    const extension = blob.type === "image/jpeg" ? "jpg" : blob.type.split("/")[1];
    return new File([blob], `image-collee.${extension}`, { type: blob.type });
}

async function uploadFile(file, position = null) {
    if (!file || uploadingImage.value) return;

    if (!allowedImageTypes.has(file.type)) {
        uploadError.value = "Format non accepté. Choisis une image JPEG, PNG ou WebP.";
        return;
    }

    if (file.size > maximumImageSize) {
        uploadError.value = "L’image dépasse la taille maximale autorisée de 5 Mo.";
        return;
    }

    uploadingImage.value = true;
    uploadError.value = "";
    const data = new FormData();
    data.append("image", file);

    try {
        const response = await axios.post(route("profil.images.store"), data, {
            headers: { Accept: "application/json" },
        });
        const chain = editor.value?.chain().focus();

        if (Number.isInteger(position)) {
            chain?.setTextSelection(position);
        }

        chain?.setImage({
            src: response.data.url,
            alt: "Image de la présentation",
        }).run();
    } catch (error) {
        uploadError.value = error.response?.data?.errors?.image?.[0]
            || error.response?.data?.message
            || "L’image n’a pas pu être ajoutée.";
    } finally {
        uploadingImage.value = false;
    }
}

function setFontSize(event) {
    const size = event.target.value;
    if (size === "1rem") editor.value?.chain().focus().unsetFontSize().run();
    else editor.value?.chain().focus().setFontSize(size).run();
}
</script>

<template>
    <div v-if="editor" class="profile-editor">
        <div class="profile-editor__toolbar" role="toolbar" aria-label="Mise en forme">
            <button type="button" :class="{ 'is-active': editor.isActive('bold') }" aria-label="Gras" title="Gras" @click="editor.chain().focus().toggleBold().run()"><strong>G</strong></button>
            <button type="button" :class="{ 'is-active': editor.isActive('italic') }" aria-label="Italique" title="Italique" @click="editor.chain().focus().toggleItalic().run()"><em>I</em></button>
            <button type="button" :class="{ 'is-active': editor.isActive('underline') }" aria-label="Souligné" title="Souligné" @click="editor.chain().focus().toggleUnderline().run()"><span class="profile-editor__underline">S</span></button>

            <span class="profile-editor__separator" aria-hidden="true"></span>

            <select aria-label="Taille du texte" title="Taille du texte" @change="setFontSize">
                <option value="0.875rem">Petit</option>
                <option value="1rem" selected>Normal</option>
                <option value="1.25rem">Grand</option>
                <option value="1.5rem">Très grand</option>
            </select>
            <label class="profile-editor__color" title="Couleur du texte">
                <span>Couleur</span>
                <input type="color" value="#46324e" aria-label="Couleur du texte" @input="editor.chain().focus().setColor($event.target.value).run()" />
            </label>

            <span class="profile-editor__separator" aria-hidden="true"></span>

            <button
                v-for="alignment in [['left', 'Gauche'], ['center', 'Centré'], ['right', 'Droite'], ['justify', 'Justifié']]"
                :key="alignment[0]"
                type="button"
                :class="{ 'is-active': editor.isActive({ textAlign: alignment[0] }) }"
                :aria-label="alignment[1]"
                :title="alignment[1]"
                @click="editor.chain().focus().setTextAlign(alignment[0]).run()"
            >
                {{ alignment[0] === "left" ? "≡" : alignment[0] === "center" ? "≣" : alignment[0] === "right" ? "≡" : "☰" }}
            </button>

            <span class="profile-editor__separator" aria-hidden="true"></span>

            <button type="button" :class="{ 'is-active': editor.isActive('bulletList') }" aria-label="Liste à puces" title="Liste à puces" @click="editor.chain().focus().toggleBulletList().run()">• Liste</button>
            <button type="button" :class="{ 'is-active': editor.isActive('blockquote') }" aria-label="Citation" title="Citation" @click="editor.chain().focus().toggleBlockquote().run()">“ ”</button>
            <button v-if="allowImages" type="button" :disabled="uploadingImage" @click="chooseImage">{{ uploadingImage ? "Ajout…" : "Image" }}</button>
            <input v-if="allowImages" ref="imageInput" type="file" class="sr-only" accept="image/jpeg,image/png,image/webp" @change="uploadImage" />
        </div>

        <EditorContent :editor="editor" class="profile-editor__content profile-rich-content" />
        <p v-if="uploadError" class="profile-editor__error" role="alert">{{ uploadError }}</p>
    </div>
</template>

<style>
.profile-editor {
    overflow: hidden;
    border: 1px solid rgb(70 50 78 / 20%);
    border-radius: 14px;
    background: #fcf8f2;
}

.profile-editor__toolbar {
    display: flex;
    padding: 8px;
    border-bottom: 1px solid rgb(70 50 78 / 12%);
    align-items: center;
    flex-wrap: wrap;
    gap: 5px;
    background: #f4eee5;
}

.profile-editor__toolbar button,
.profile-editor__toolbar select,
.profile-editor__color {
    display: inline-flex;
    min-width: 38px;
    min-height: 38px;
    padding: 7px 10px;
    border: 1px solid transparent;
    border-radius: 8px;
    align-items: center;
    justify-content: center;
    color: #46324e;
    background: transparent;
    font: inherit;
    font-size: 13px;
    font-weight: 650;
    cursor: pointer;
}

.profile-editor__toolbar button:hover,
.profile-editor__toolbar button.is-active,
.profile-editor__toolbar select:hover,
.profile-editor__color:hover {
    border-color: rgb(70 50 78 / 11%);
    background: #fffaf4;
}

.profile-editor__toolbar button.is-active { color: #fffaf4; background: #46324e; }

.profile-editor__toolbar button:focus-visible,
.profile-editor__toolbar select:focus-visible,
.profile-editor__color:focus-within {
    outline: 3px solid rgb(214 168 74 / 35%);
    outline-offset: 2px;
}

.profile-editor__toolbar button:disabled { opacity: 0.55; cursor: wait; }
.profile-editor__separator { width: 1px; height: 28px; margin-inline: 3px; background: rgb(70 50 78 / 13%); }
.profile-editor__underline { text-decoration: underline; }
.profile-editor__color { gap: 7px; }
.profile-editor__color input { width: 23px; height: 23px; padding: 0; border: 0; border-radius: 5px; background: transparent; cursor: pointer; }
.profile-editor__content .tiptap { min-height: 280px; padding: 20px; outline: none; }
.profile-editor__content .tiptap:focus-visible { box-shadow: inset 0 0 0 3px rgb(214 168 74 / 24%); }
.profile-editor__error { margin: 0; padding: 10px 14px; color: #8e344b; background: rgb(142 52 75 / 8%); font-size: 13px; font-weight: 600; }
</style>
