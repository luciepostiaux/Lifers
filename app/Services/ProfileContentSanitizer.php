<?php

namespace App\Services;

use Illuminate\Validation\ValidationException;

class ProfileContentSanitizer
{
    private const MAX_TEXT_LENGTH = 10000;

    private const MAX_NODES = 1000;

    private const ALLOWED_NODE_TYPES = [
        'doc',
        'paragraph',
        'heading',
        'bulletList',
        'orderedList',
        'listItem',
        'blockquote',
        'hardBreak',
        'text',
        'image',
    ];

    private const ALLOWED_MARK_TYPES = ['bold', 'italic', 'underline', 'textStyle'];

    private const ALLOWED_FONT_SIZES = ['0.875rem', '1rem', '1.25rem', '1.5rem'];

    private int $nodeCount = 0;

    private int $textLength = 0;

    public function sanitize(?array $document, int $liferId): ?array
    {
        if ($document === null || $document === []) {
            return null;
        }

        $this->nodeCount = 0;
        $this->textLength = 0;

        if (($document['type'] ?? null) !== 'doc') {
            $this->invalid();
        }

        return $this->sanitizeNode($document, $liferId, true);
    }

    private function sanitizeNode(array $node, int $liferId, bool $isRoot = false, int $depth = 0): array
    {
        if ($depth > 20) {
            $this->invalid('La structure de la présentation est trop profonde.');
        }

        $this->nodeCount++;

        if ($this->nodeCount > self::MAX_NODES) {
            $this->invalid('La présentation contient trop d’éléments.');
        }

        $type = $node['type'] ?? null;

        if (! is_string($type) || ! in_array($type, self::ALLOWED_NODE_TYPES, true)) {
            $this->invalid();
        }

        if ($isRoot && $type !== 'doc') {
            $this->invalid();
        }

        $clean = ['type' => $type];

        if ($type === 'text') {
            $text = $node['text'] ?? null;

            if (! is_string($text)) {
                $this->invalid();
            }

            $this->textLength += mb_strlen($text);

            if ($this->textLength > self::MAX_TEXT_LENGTH) {
                $this->invalid('La présentation ne peut pas dépasser 10 000 caractères.');
            }

            $clean['text'] = $text;
            $marks = $this->sanitizeMarks($node['marks'] ?? []);

            if ($marks !== []) {
                $clean['marks'] = $marks;
            }

            return $clean;
        }

        $attrs = $this->sanitizeAttributes($type, $node['attrs'] ?? [], $liferId);

        if ($attrs !== []) {
            $clean['attrs'] = $attrs;
        }

        if (isset($node['content'])) {
            if (! is_array($node['content'])) {
                $this->invalid();
            }

            $clean['content'] = array_map(
                fn ($child) => is_array($child)
                    ? $this->sanitizeNode($child, $liferId, false, $depth + 1)
                    : $this->invalid(),
                array_values($node['content']),
            );
        }

        return $clean;
    }

    private function sanitizeAttributes(string $type, mixed $attrs, int $liferId): array
    {
        if (! is_array($attrs)) {
            return [];
        }

        if (in_array($type, ['paragraph', 'heading'], true)) {
            $alignment = $attrs['textAlign'] ?? null;
            $clean = [];

            if ($type === 'heading') {
                $level = (int) ($attrs['level'] ?? 2);
                $clean['level'] = in_array($level, [2, 3], true) ? $level : 2;
            }

            if (in_array($alignment, ['left', 'center', 'right', 'justify'], true)) {
                $clean['textAlign'] = $alignment;
            }

            return $clean;
        }

        if ($type === 'image') {
            $src = $attrs['src'] ?? '';
            $expectedPrefix = "/storage/lifer-profiles/{$liferId}/";

            if (! is_string($src) || ! str_starts_with($src, $expectedPrefix)) {
                $this->invalid('Une image de la présentation est invalide.');
            }

            return [
                'src' => $src,
                'alt' => mb_substr(trim((string) ($attrs['alt'] ?? '')), 0, 160),
                'title' => null,
            ];
        }

        return [];
    }

    private function sanitizeMarks(mixed $marks): array
    {
        if (! is_array($marks)) {
            return [];
        }

        $clean = [];

        foreach ($marks as $mark) {
            if (! is_array($mark) || ! in_array($mark['type'] ?? null, self::ALLOWED_MARK_TYPES, true)) {
                continue;
            }

            if ($mark['type'] !== 'textStyle') {
                $clean[] = ['type' => $mark['type']];

                continue;
            }

            $attrs = is_array($mark['attrs'] ?? null) ? $mark['attrs'] : [];
            $style = [];

            if (is_string($attrs['color'] ?? null) && preg_match('/^#[0-9a-fA-F]{6}$/', $attrs['color'])) {
                $style['color'] = strtolower($attrs['color']);
            }

            if (in_array($attrs['fontSize'] ?? null, self::ALLOWED_FONT_SIZES, true)) {
                $style['fontSize'] = $attrs['fontSize'];
            }

            if ($style !== []) {
                $clean[] = ['type' => 'textStyle', 'attrs' => $style];
            }
        }

        return $clean;
    }

    private function invalid(string $message = 'Le contenu de la présentation est invalide.'): never
    {
        throw ValidationException::withMessages(['content' => $message]);
    }
}
