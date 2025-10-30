<?php
namespace Xao;

use XSLTProcessor;
use DOMDocument;

/**
 * A class for performing XSLT transformations.
 *
 * This class provides a simple interface for transforming XML documents with
 * XSLT stylesheets.
 */
class Transformer
{
    /**
     * The DomDoc to be transformed.
     *
     * @var DomDoc
     */
    public DomDoc $objDoc;

    /**
     * The XSLT stylesheet.
     *
     * @var DOMDocument
     */
    public DOMDocument $objXslt;

    /**
     * The XSLT processor.
     *
     * @var XSLTProcessor
     */
    public XSLTProcessor $objProcessor;

    /**
     * Transformer constructor.
     *
     * @param DomDoc $objDoc The DomDoc to be transformed.
     * @param string $uriXslt The path to the XSLT stylesheet.
     */
    public function __construct(DomDoc $objDoc, string $uriXslt)
    {
        $this->objDoc = $objDoc;
        $this->objXslt = new DOMDocument();
        $this->objXslt->load($uriXslt);

        $this->objProcessor = new XSLTProcessor();
        $this->objProcessor->importStylesheet($this->objXslt);
    }

    /**
     * Sets a parameter for the XSLT transformation.
     *
     * @param string $strName The name of the parameter.
     * @param string $strValue The value of the parameter.
     */
    public function SetParam(string $strName, string $strValue): void
    {
        $this->objProcessor->setParameter('', $strName, $strValue);
    }

    /**
     * Performs the XSLT transformation.
     *
     * @return string The result of the transformation.
     */
    public function strProcess(): string
    {
        return $this->objProcessor->transformToXML($this->objDoc->objDoc);
    }
}
