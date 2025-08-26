<?php

namespace XAO\Renderers;

use DOMDocument;

class HtmxRenderer implements RendererInterface
{
    public function contentType(): string
    {
        return 'text/html; charset=UTF-8';
    }

    public function render(DOMDocument $dom, array $options = []): string
    {
        // Two strategies:
        // 1) If an XSL stylesheet is provided, do server-side transform to HTML fragment
        // 2) Else, emit a basic HTML serialization of the XML tree for debugging
        $stylesheetPath = $options['stylesheetPath'] ?? $options['stylesheet'] ?? null;

        if ($stylesheetPath && is_readable($stylesheetPath)) {
            return $this->transformWithXslt($dom, $stylesheetPath);
        }

        // Fallback: naive HTML dump of XML content
        $dom->formatOutput = true;
        $xml = $dom->saveXML($dom->documentElement);
        return '<div class="xao-xml-debug"><pre>' . htmlspecialchars($xml, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre></div>';
    }

    protected function transformWithXslt(DOMDocument $dom, string $stylesheetPath): string
    {
        $xsl = new DOMDocument();
        $xsl->load($stylesheetPath);

        $proc = new \XSLTProcessor();
        $proc->importStylesheet($xsl);

        $result = $proc->transformToXml($dom);
        if ($result === false) {
            return '<!-- XSLT transform failed -->';
        }
        return $result;
    }
}
