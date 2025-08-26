<?php

namespace XAO\Renderers;

use DOMDocument;

interface RendererInterface
{
    /**
     * Render the given DOMDocument into a string response.
     *
     * @param DOMDocument $dom Canonical XML DOM to render from
     * @param array $options Optional render options (e.g., stylesheet path, context)
     * @return string
     */
    public function render(DOMDocument $dom, array $options = []): string;

    /**
     * Return the Content-Type header string that should be sent for this renderer.
     * Example: 'application/json', 'text/html; charset=UTF-8', 'application/xml'.
     */
    public function contentType(): string;
}
