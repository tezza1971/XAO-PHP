<?php
namespace Xao;

use DOMDocument;
use DOMElement;

/**
 * A class for managing exceptions and errors within the XAO framework.
 *
 * This class provides a structured way to handle errors by creating a dedicated
 * XML structure for each exception.
 */
class Exceptions
{
    /**
     * The DOM document where the exceptions will be logged.
     *
     * @var DOMDocument
     */
    public DOMDocument $objDoc;

    /**
     * The root node for all exception elements.
     *
     * @var DOMElement
     */
    public DOMElement $ndParent;

    /**
     * The name of the element to use for each exception.
     *
     * @var string
     */
    public string $strElName;

    /**
     * The main error message.
     *
     * @var string
     */
    public string $strMessage;

    /**
     * Attributes to add to the exception element.
     *
     * @var array
     */
    public array $arrAttribs;

    /**
     * Exceptions constructor.
     *
     * @param DOMDocument $objDoc The DOM document.
     * @param DOMElement $ndParent The parent node for exceptions.
     * @param string $strElName The name of the exception element.
     */
    public function __construct(DOMDocument $objDoc, DOMElement $ndParent, string $strElName)
    {
        $this->objDoc = $objDoc;
        $this->ndParent = $ndParent;
        $this->strElName = $strElName;
    }

    /**
     * Sets the error message.
     *
     * @param string $strMessage The error message.
     */
    public function setMessage(string $strMessage): void
    {
        $this->strMessage = $strMessage;
    }

    /**
     * Sets the attributes for the exception element.
     *
     * @param array $arrAttribs The attributes.
     */
    public function setMsgAttribs(array $arrAttribs): void
    {
        $this->arrAttribs = $arrAttribs;
    }

    /**
     * Creates a new error element in the DOM.
     *
     * @return DOMElement The newly created error element.
     */
    public function ndCreateError(): DOMElement
    {
        $ndErr = $this->objDoc->createElement($this->strElName);
        $ndErr->setAttribute("message", $this->strMessage);

        foreach ($this->arrAttribs as $name => $value) {
            $ndErr->setAttribute($name, $value);
        }

        $this->ndParent->appendChild($ndErr);
        return $ndErr;
    }
}
