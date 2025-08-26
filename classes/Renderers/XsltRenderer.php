<?php

namespace XAO\Renderers;

use DOMDocument;

class XsltRenderer implements RendererInterface
{
    public function contentType(): string
    {
        // We return XML since the browser can apply client-side XSLT via PI
        return 'application/xml; charset=UTF-8';
    }

    public function render(DOMDocument $dom, array $options = []): string
    {
        $stylesheetHref = $options['stylesheet'] ?? $options['stylesheetHref'] ?? 'styles/demo.xsl';

        // Ensure XML declaration
        $dom->formatOutput = true;

        // Remove existing xml-stylesheet PIs to avoid duplicates
        // Use nodeName for portability (nodeName == target for PI nodes)
        for ($n = $dom->firstChild; $n; ) {
            $next = $n->nextSibling;
            if ($n->nodeType === XML_PI_NODE && strtolower($n->nodeName) === 'xml-stylesheet') {
                $dom->removeChild($n);
            }
            $n = $next;
        }

        // Add xml-stylesheet processing instruction for client-side XSLT
        $piData = sprintf('type="text/xsl" href="%s"', htmlspecialchars($stylesheetHref, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $pi = $dom->createProcessingInstruction('xml-stylesheet', $piData);
        $dom->insertBefore($pi, $dom->documentElement);

        return $dom->saveXML();
    }
}
