<?php
namespace Xao;

/**
 * An application-specific document class for the XAO framework.
 *
 * This class extends the base DomDoc to provide a structured way to manage
 * application-level data. It's designed to be a central point for
 * content aggregation and transformation.
 */
class AppDoc extends DomDoc
{
    /**
     * The content of the document.
     *
     * @var DomDoc
     */
    public DomDoc $objContent;

    /**
     * The transformer for the document.
     *
     * @var Transformer
     */
    public Transformer $objTransformer;

    /**
     * AppDoc constructor.
     *
     * @param string $strContent The initial content of the document.
     */
    public function __construct(string $strContent = "<content/>")
    {
        parent::__construct("<root/>");
        $this->objContent = new DomDoc($strContent, XAO_DOC_DATA);
        $this->ndConsumeDoc($this->objContent);
    }

    /**
     * Sets the XSLT for the document transformation.
     *
     * @param string $uri The path to the XSLT file.
     */
    public function SetXslt(string $uri): void
    {
        $this->objTransformer = new Transformer($this, $uri);
    }

    /**
     * Transforms the document and returns the result.
     *
     * @return string The transformed output.
     */
    public function strTransform(): string
    {
        return $this->objTransformer->strProcess();
    }
}
