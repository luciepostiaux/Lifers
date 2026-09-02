import Color from "@tiptap/extension-color";
import Image from "@tiptap/extension-image";
import TextAlign from "@tiptap/extension-text-align";
import { FontSize, TextStyle } from "@tiptap/extension-text-style";
import StarterKit from "@tiptap/starter-kit";

export function profileEditorExtensions() {
    return [
        StarterKit.configure({
            heading: { levels: [2, 3] },
        }),
        TextStyle,
        FontSize.configure({ types: ["textStyle"] }),
        Color.configure({ types: ["textStyle"] }),
        TextAlign.configure({ types: ["heading", "paragraph"] }),
        Image.configure({ allowBase64: false, inline: false }),
    ];
}

export const emptyProfileDocument = {
    type: "doc",
    content: [{ type: "paragraph" }],
};
