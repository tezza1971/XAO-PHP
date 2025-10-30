<?php
namespace Xao;

use DOMDocument;
use Exception;

/**
 * A factory class for creating and debugging DOM documents.
 *
 * This class is responsible for parsing XML data and providing detailed error
 * information if the XML is not well-formed. It's a key part of the framework's
 * XML processing pipeline.
 */
class DomFactory extends XaoRoot
{
    /**
     * The DOM document object.
     *
     * @var DOMDocument
     */
    public DOMDocument $objDoc;

    /**
     * The line number of the last XML parse error.
     *
     * @var int|null
     */
    public ?int $intErrorLine = null;

    /**
     * The file path of the XML document being parsed.
     *
     * @var string|null
     */
    public ?string $uriContextFile = null;

    /**
     * The last error message.
     *
     * @var string|null
     */
    public ?string $strErrorMsg = null;

    /**
     * The full error message, including context.
     *
     * @var string|null
     */
    public ?string $strErrorMsgFull = null;

    /**
     * DomFactory constructor.
     *
     * @param string $strTarget The XML data or file path to parse.
     */
    public function __construct(string $strTarget)
    {
        $this->objDoc = new DOMDocument();

        if (!str_contains($strTarget, "\n") && file_exists($strTarget)) {
            $this->_objDomParseFile($strTarget);
        } else {
            $this->_objDomParseData($strTarget);
        }
    }

    /**
     * Returns the created DOM document.
     *
     * @return DOMDocument
     */
    public function objGetObjDoc(): DOMDocument
    {
        return $this->objDoc;
    }

    /**
     * Parses an XML file.
     *
     * @param string $uriSrc The path to the XML file.
     */
    private function _objDomParseFile(string $uriSrc): void
    {
        $this->uriContextFile = $uriSrc;
        $strFileData = file_get_contents($uriSrc);
        if ($strFileData === false) {
            $this->Throw("Could not open " . $uriSrc);
        }
        $this->_objDomParseData($strFileData);
    }

    /**
     * Parses XML data.
     *
     * @param string $strSrc The XML data to parse.
     */
    private function _objDomParseData(string $strSrc): void
    {
        libxml_use_internal_errors(true);
        if ($this->objDoc->loadXML($strSrc)) {
            return;
        }

        $error = libxml_get_last_error();
        if ($error) {
            $this->intErrorLine = $error->line;
            $this->strErrorMsg = $error->message;
            $this->strErrorMsgFull = "The following parse error occurred";
            if ($this->intErrorLine !== false) {
                $this->strErrorMsgFull .= " on or near line " . $this->intErrorLine;
            }
            if ($this->uriContextFile) {
                $this->strErrorMsgFull .= " in the file " . $this->uriContextFile;
            }
            $this->strErrorMsgFull .= ":\n " . $this->strErrorMsg . "\n";

            if (is_int($this->intErrorLine)) {
                $objDebugData = new TextDebugger($strSrc, $this->intErrorLine);
                $this->strDebugData = $objDebugData->strGetHtml();
            }
            $this->Throw($this->strErrorMsgFull . $this->strDebugData);
        }
    }

    /**
     * Throws an exception with the error message.
     *
     * @param string $strErrMsg The main error message.
     * @param array|null $arrErrAttribs Additional context for the error.
     */
    public function Throw(string $strErrMsg, ?array $arrErrAttribs = null): void
    {
        if ($this->intErrorLine) {
            if ($arrErrAttribs === null) {
                $arrErrAttribs = [];
            }
            $arrErrAttribs["line"] = $this->intErrorLine;
        }
        parent::Throw($strErrMsg, $arrErrAttribs);
    }
}
