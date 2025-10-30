<?php
namespace Xao;

use DOMDocument;
use DOMNode;
use DOMElement;
use DOMException;
use BadMethodCallException;
use Exception;

/**
 * General-purpose DOM class for the XAO framework.
 *
 * This class provides a set of convenience functions for common DOM operations,
 * making it easier to create, manipulate, and export XML documents. It also
 * offers a thread-safe way to interact with the file system.
 */
class DomDoc extends XaoRoot
{
    /**
     * The main DOM document object.
     *
     * This is an instance of PHP's built-in DOMDocument class, which holds
     * the XML document that this class manages.
     */
    public DOMDocument $objDoc;

    /**
     * The root node of the document.
     *
     * This provides a convenient shortcut to the root element of the XML
     * document, which is a common starting point for many DOM operations.
     */
    public ?DOMElement $ndRoot;

    /**
     * A queue of element objects to be processed.
     *
     * This is used to manage custom tag processing. You can add elements to
     * this queue and then process them with a callback function.
     */
    private array $_arrCustomTagNames = [];

    /**
     * A queue of query result node objects to be processed.
     *
     * Similar to the custom tag names queue, but this one is for nodes
     * identified by an XPath query.
     */
    private array $_arrCustomTagQueries = [];

    /**
     * DomDoc constructor.
     *
     * This sets up the DOM document based on the provided data and usage mode.
     *
     * @param mixed $mxdData The data to create the document from (e.g., a root
     *                       element name, a file path, or an existing DOM object).
     * @param int $intUse The mode of document creation (e.g., new, from file,
     *                    or from an existing DOM object).
     */
    public function __construct(mixed $mxdData, int $intUse = XAO_DOC_NEW)
    {
        $this->objDoc = new DOMDocument("1.0", "UTF-8");

        switch ($intUse) {
            case XAO_DOC_NEW:
                $this->ndRoot = $this->objDoc->createElement($mxdData);
                $this->objDoc->appendChild($this->ndRoot);
                break;
            case XAO_DOC_READFILE:
                $this->objDoc->load($mxdData);
                $this->ndRoot = $this->objDoc->documentElement;
                break;
            case XAO_DOC_DATA:
                $this->objDoc->loadXML($mxdData);
                $this->ndRoot = $this->objDoc->documentElement;
                break;
            case XAO_DOC_REFERENCE:
                if ($mxdData instanceof DOMDocument) {
                    $this->objDoc = $mxdData;
                    $this->ndRoot = $mxdData->documentElement;
                } else {
                    $this->Throw("The provided data is not a valid DOMDocument object.");
                }
                break;
            default:
                $this->Throw("Invalid mode for DomDoc constructor.");
        }
    }

    /**
     * Serializes the entire document to an XML string.
     *
     * @return string The XML content of the document.
     */
    public function xmlGetDoc(): string
    {
        return $this->objDoc->saveXML();
    }

    /**
     * Serializes the document to an XML fragment string.
     *
     * @return string The XML fragment content of the document.
     */
    public function xmlGetFrag(): string
    {
        return $this->objDoc->saveXML($this->ndRoot);
    }

    /**
     * Saves the XML document to a file.
     *
     * @param string $uriDestination The path to the destination file.
     * @throws Exception if the file cannot be opened or written to.
     */
    public function CommitToFile(string $uriDestination): void
    {
        if (!file_exists($uriDestination)) {
            $this->Throw("CommitToFile: {$uriDestination} was not found.");
        }

        $this->objDoc->save($uriDestination);
    }

    /**
     * Fetches a single element by its tag name.
     *
     * @param string $strName The name of the element to fetch.
     * @param int $intIdx The index of the element to return (0 for the first).
     * @return DOMNode|null The element node, or null if not found.
     */
    public function ndGetOneEl(string $strName, int $intIdx = 0): ?DOMNode
    {
        $nodes = $this->objDoc->getElementsByTagName($strName);
        return $nodes->item($intIdx);
    }

    /**
     * Appends a new element to the root of the document.
     *
     * @param string $strElName The name of the new element.
     * @param string $strCont The content of the new element.
     * @return DOMNode|null The newly added element node.
     */
    public function ndAppendToRoot(string $strElName, string $strCont = ""): ?DOMNode
    {
        if (!$this->blnTestXmlName($strElName)) {
            $this->Throw("ndAppendToRoot: {$strElName} is not a valid element name.");
        }

        $elNew = $this->objDoc->createElement($strElName, $strCont);
        return $this->ndRoot->appendChild($elNew);
    }

    /**
     * Appends a new element to an existing node.
     *
     * @param DOMNode $ndStub The node to append the new element to.
     * @param string $strElName The name of the new element.
     * @param string $strCont The content of the new element.
     * @return DOMNode|null The newly added element node.
     */
    public function ndAppendToNode(DOMNode $ndStub, string $strElName, string $strCont = ""): ?DOMNode
    {
        if (!$this->blnTestElementNode($ndStub)) {
            $this->Throw("ndAppendToNode: First argument is not a valid element node.");
        }

        if (!$this->blnTestXmlName($strElName)) {
            $this->Throw("ndAppendToNode: {$strElName} is not a valid element name.");
        }

        $elNew = $this->objDoc->createElement($strElName, $strCont);
        return $ndStub->appendChild($elNew);
    }

    /**
     * Imports a fragment from another DOM document.
     *
     * @param DOMNode $ndStub The node to import the fragment under.
     * @param DOMNode $ndNew The foreign node to import.
     * @return DOMNode|null The newly added node.
     */
    public function ndImportChildFrag(DOMNode $ndStub, DOMNode $ndNew): ?DOMNode
    {
        if (!$this->blnTestElementNode($ndStub)) {
            $this->Throw("ndImportChildFrag: First argument is not a valid element node.");
        }

        $importedNode = $this->objDoc->importNode($ndNew, true);
        return $ndStub->appendChild($importedNode);
    }

    /**
     * Consumes an entire DOM document and appends it to this document.
     *
     * @param DomDoc $objDoc The DomDoc object to consume.
     * @param DOMNode|null $ndStub The node to append the new data to.
     * @return DOMNode|null The newly added node.
     */
    public function ndConsumeDoc(DomDoc $objDoc, ?DOMNode $ndStub = null): ?DOMNode
    {
        if (!($objDoc instanceof DomDoc)) {
            $this->Throw("ndConsumeDoc: No DomDoc object given.");
        }

        $ndStub = $ndStub ?? $this->ndRoot;
        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    /**
     * Consumes an XML file and appends it to this document.
     *
     * @param string $uri The path to the XML file.
     * @param DOMNode|null $ndStub The node to append the new data to.
     * @return DOMNode|null The newly added node.
     */
    public function ndConsumeFile(string $uri, ?DOMNode $ndStub = null): ?DOMNode
    {
        $objDoc = new DomDoc($uri, XAO_DOC_READFILE);
        $ndStub = $ndStub ?? $this->ndRoot;
        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    /**
     * Consumes an XML fragment and appends it to this document.
     *
     * @param string $str The XML fragment data.
     * @param string $strRoot The name of the root element to wrap the fragment in.
     * @param DOMNode|null $ndStub The node to append the new data to.
     * @return DOMNode|null The newly added node.
     */
    public function ndConsumeFragData(string $str, string $strRoot, ?DOMNode $ndStub = null): ?DOMNode
    {
        if (!$this->blnTestXmlName($strRoot)) {
            $this->Throw("ndConsumeFragData: {$strRoot} is an invalid name for the root element.");
        }

        $xml = "<?xml version=\"1.0\"?><{$strRoot}>{$str}</{$strRoot}>";
        $objDoc = new DomDoc($xml, XAO_DOC_DATA);
        $ndStub = $ndStub ?? $this->ndRoot;
        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    /**
     * Converts an associative array to attributes on an element.
     *
     * @param DOMElement $ndEl The element to add the attributes to.
     * @param array $arrAttribs The associative array of attributes.
     * @return bool True on success.
     */
    public function Arr2Atts(DOMElement $ndEl, array $arrAttribs): bool
    {
        foreach ($arrAttribs as $strName => $strValue) {
            $ndEl->setAttribute($strName, $strValue);
        }
        return true;
    }

    /**
     * Sets a custom tag query for processing.
     *
     * @param string $strQuery The XPath query.
     * @param string $fncName The name of the callback function.
     * @throws BadMethodCallException if the callback method is not defined.
     */
    public function SetCustomTagQuery(string $strQuery, string $fncName): void
    {
        if (method_exists($this, $fncName)) {
            $this->_arrCustomTagQueries[] = [$strQuery, $fncName];
        } else {
            throw new BadMethodCallException("Method {$fncName} is undefined.");
        }
    }

    /**
     * Processes all custom tags.
     */
    public function ProcessCustomTags(): void
    {
        foreach ($this->_arrCustomTagNames as $elName => $fncName) {
            $nodes = $this->objDoc->getElementsByTagName($elName);
            foreach ($nodes as $node) {
                $this->$fncName($node);
            }
        }

        foreach ($this->_arrCustomTagQueries as list($strQry, $fncName)) {
            $nodes = $this->arrNdXPath($strQry);
            if ($nodes) {
                foreach ($nodes as $node) {
                    $this->$fncName($node);
                }
            }
        }
    }

    /**
     * Executes an XPath query and returns a list of nodes.
     *
     * @param string $strExpr The XPath query.
     * @return array|false The array of nodes, or false on error.
     */
    public function arrNdXPath(string $strExpr)
    {
        $xpath = new \DOMXPath($this->objDoc);
        $nodes = $xpath->query($strExpr);
        return $nodes ?: false;
    }

    /**
     * Tests if a node is an element node.
     *
     * @param mixed $ndEl The node to test.
     * @return bool True if the node is a DOMElement.
     */
    public function blnTestElementNode($ndEl): bool
    {
        return $ndEl instanceof DOMElement;
    }

    /**
     * Tests if a string is a valid XML element name.
     *
     * @param string $strName The name to test.
     * @return bool True if the name is valid.
     */
    public function blnTestXmlName(string $strName): bool
    {
        try {
            $this->objDoc->createElement($strName);
            return true;
        } catch (DOMException $e) {
            return false;
        }
    }
}

// -- Legacy constants for backward compatibility --

/**
 * Creates a new DOM document from scratch.
 */
define("XAO_DOC_NEW", 10);

/**
 * Creates a DOM document from a local file.
 */
define("XAO_DOC_READFILE", 20);

/**
 * Creates a DOM document from an existing PHP DOM object.
 */
define("XAO_DOC_REFERENCE", 50);

/**
 * Creates a DOM document from XML data in a variable.
 */
define("XAO_DOC_DATA", 60);
