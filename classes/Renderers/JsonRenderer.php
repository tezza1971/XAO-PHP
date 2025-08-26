<?php

namespace XAO\Renderers;

use DOMDocument;
use DOMElement;
use DOMNode;

class JsonRenderer implements RendererInterface
{
    public function contentType(): string
    {
        return 'application/json; charset=UTF-8';
    }

    public function render(DOMDocument $dom, array $options = []): string
    {
        // Normalize: ensure we have a root element
        $root = $dom->documentElement;
        if (!$root) {
            // Create an empty placeholder if needed
            $root = $dom->createElement('root');
            $dom->appendChild($root);
        }

        $maxDepth = $options['maxDepth'] ?? 256;
        $pretty = $options['pretty'] ?? true;

        $json = $this->elementToJson($root, 0, $maxDepth);
        return $pretty
            ? json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
            : json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Convert a DOMElement subtree into a React-friendly JSON tree.
     *
     * Output format:
     * {
     *   type: string,
     *   props: object,
     *   children: [ string | node ]
     * }
     */
    protected function elementToJson(DOMElement $el, int $depth, int $maxDepth)
    {
        if ($depth > $maxDepth) {
            return null;
        }

        $node = [
            'type' => $el->tagName,
            'props' => $this->attributesToArray($el),
            'children' => []
        ];

        for ($child = $el->firstChild; $child; $child = $child->nextSibling) {
            if ($child->nodeType === XML_ELEMENT_NODE) {
                $childJson = $this->elementToJson($child, $depth + 1, $maxDepth);
                if ($childJson !== null) {
                    $node['children'][] = $childJson;
                }
            } elseif ($child->nodeType === XML_TEXT_NODE || $child->nodeType === XML_CDATA_SECTION_NODE) {
                $text = trim($child->nodeValue ?? '');
                if ($text !== '') {
                    $node['children'][] = $text;
                }
            }
        }

        return $node;
    }

    protected function attributesToArray(DOMElement $el): array
    {
        $props = [];
        if ($el->hasAttributes()) {
            foreach ($el->attributes as $attr) {
                $props[$attr->name] = $attr->value;
            }
        }
        return $props;
    }
}
