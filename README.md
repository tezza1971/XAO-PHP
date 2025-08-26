# XAO-PHP v2

XML Application Objects for PHP — modernized for PHP 8.x

XAO-PHP is a lightweight framework centered around XML as the canonical application state. It provides a simple, consistent way to build up an XML DOM as your single source of truth, and then render it to different targets such as HTML (via XSLT/HTMX) or JSON (for React or other front-ends).

This repo contains a modernized version of the original XAO codebase, updated to run on PHP 8.x and the native DOM APIs. The core idea remains the same: treat XML DOM as your application state, and transform it as needed.


Features
- XML DOM as the canonical state tree using PHP’s DOMDocument
- Fluent helpers for building and composing XML documents
- XPath utilities with DOMXPath
- XSLT rendering via XSLTProcessor (server-side), or client-side if you prefer
- Structured error capture and debug helpers
- Backward-compatibility shims for older XAO APIs where sensible


Status (Modernization Roadmap)
- Option A (completed)
  - Migrated legacy domxml functions to native DOM (DOMDocument, DOMXPath)
  - Replaced dump_mem/document_element/etc. with saveXML/documentElement
  - Introduced safer XML parsing via DomFactory with libxml errors
- Option B (in progress)
  - Migrate old-style constructors to __construct while keeping BC wrappers
  - Add type hints and return types where unambiguous
  - Replace var with explicit visibility
  - Modernize Transformer to use XSLTProcessor
- Phase 2 (planned)
  - Introduce RendererInterface abstraction (XSLT/HTMX/JSON)
  - Add JSON renderer to support React or other SPA frontends
  - Provide Controller + Router glue code for typical HTTP request flow


Concept Overview
- XML as State: Your application builds an XML DOM that represents the entire page/app state. All downstream renderers transform from this DOM.
- Composability: Merge or consume fragments and documents to aggregate the final DOM.
- Rendering: Use XSLTProcessor (server-side) or client-side XSLT/HTMX, and optionally output JSON.


Core Classes
- XaoRoot: Base class providing error handling and shared utilities.
- DomDoc: High-level utilities for creating and manipulating DOMDocument instances.
  - Create new docs, consume XML from files/strings, append fragments, run XPath queries
- DomFactory: Safe parsing layer that returns DOMDocument instances and captures libxml errors with helpful debug output
- Transformer: XSLT transformation helper (modernized to XSLTProcessor)


Quick Start
1) Create a document

<?php
require_once 'classes/XAO_DomDoc.php';

// Create a document with a root element <root>
$doc = new DomDoc('root', XAO_DOC_NEW);

// Append a child element
$child = $doc->ndAppendToNode($doc->ndRoot, 'message', 'Hello XAO');

// Query with XPath
$nodes = $doc->arrNdXPath('//message');

// Serialize
echo $doc->xmlGetDoc();
?>

2) Consume XML data

<?php
$xml = '<items><item id="1"/><item id="2"/></items>';
$doc2 = new DomDoc($xml, XAO_DOC_DATA);

// Append to an existing document
$doc->ndConsumeDoc($doc2);
?>

3) Transform with XSLT

<?php
require_once 'classes/XAO_Transformer.php';

$xsl = <<<XSL
<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="/">
    <html><body>
      <h1>Messages</h1>
      <ul>
        <xsl:for-each select="//message">
          <li><xsl:value-of select="."/></li>
        </xsl:for-each>
      </ul>
    </body></html>
  </xsl:template>
</xsl:stylesheet>
XSL;

$transformer = new Transformer($doc->objDoc, (new DomFactory($xsl))->objGetObjDoc());
$result = $transformer->transform();

echo $result; // HTML
?>


Development
- Requirements: PHP 8.x, ext-dom, ext-libxml, ext-simplexml, ext-xsl (for XSLTProcessor)
- Code style: aiming for clear, typed, modern PHP while preserving compatibility where reasonable
- Repo structure:
  - classes/: Core library classes
  - docs/: Original phpDocumentor documentation (historical)
  - schema/: XAO XML schema references


Testing locally
- You can run the included test script to smoke test the modernization work.

bash
php test_modernization.php

If PHP is not on your PATH, use the absolute path to your PHP binary or set up your environment accordingly.


Roadmap Highlights
- Rendering Abstraction: RendererInterface with XsltRenderer, HtmxRenderer, JsonRenderer
- JSON Bridge: XML-to-JSON format for React: {"type": "ComponentName", "props": {...}, "children": [...]}
- Component Registry: Map XML element names to React components
- Fluent Builder: Developer-friendly XML creation APIs (e.g., $xml->el('user')->att('id', 'u1')->text('...'))
- Optional: Plugin system, headless CMS, GraphQL bridge, AI tooling for mapping XML to front-end components


License
Apache License, Version 2.0. See LICENSE for details.
