<?php
/**
* XAO_XaoRoot.php
*
* Base class for all XAO classes. Provides minimum shared functionality and
* basic error handling.
*/

function XAO_CHECK_MINIMUM_REQUIREMENTS() {
    // Require modern PHP (8.x+) for XAO v2
    $arrPhpVer = explode(".", phpversion());
    $major = (int)($arrPhpVer[0] ?? 0);
    if ($major < 8) {
        die("XAO requires PHP 8.0 or greater. Current version: " . phpversion());
    }

    // Require the DOM extension (ext-dom) and libxml
    if (!extension_loaded('dom')) {
        die('The DOM extension (ext-dom) is required for XAO functionality.');
    }

    if (!extension_loaded('libxml')) {
        die('The libxml extension is required for XAO functionality.');
    }

    // SimpleXML is used in places; ensure it's available
    if (!extension_loaded('simplexml')) {
        die('The SimpleXML extension is required for XAO functionality.');
    }
}
XAO_CHECK_MINIMUM_REQUIREMENTS();

class XaoRoot {
    /** XAO XML Namespace Identifier */
    public string $idXaoNamespace = "http://github.com/tezza1971/XAO-PHP/schema/xao_1-0.xsd";

    /** XAO XML Namespace Prefix */
    public string $strXaoNamespacePrefix = "xao";

    /** Debug HTML payload (optional) */
    public ?string $strDebugData = null;

    /** Error message payload */
    public string $strError = "";

    /** Name of member method to call on error (optional) */
    public string $fncErrCallbackFunc = "";

    /** Cache parameters */
    public array $arrCacheParams = array();

    /**
     * Generic/default error handler.
     *
     * @param string $strErrMsg User-defined error message
     * @param array|null $arrAttribs Metadata to provide supportive context
     */
    public function Throw(string $strErrMsg, ?array $arrAttribs = null): void {
        if(is_null($arrAttribs)) $arrAttribs = array();
        // clear any previous errors
        $this->strError = "";

        if(
            isset($arrAttribs["class"]) &&
            isset($arrAttribs["function"]) &&
            isset($arrAttribs["line"]) 
        ) {
            $this->strError .=
                "In method "
                .$arrAttribs["class"]."::"
                .$arrAttribs["function"]."() on line "
                .$arrAttribs["line"]."\n\n";
        }

        $this->strError .= $strErrMsg;
        // call user-defined error function
        if(strlen($this->fncErrCallbackFunc)) {
            $func = $this->fncErrCallbackFunc;
            if(method_exists($this,$func)) {
                $this->$func($strErrMsg);
            }
        }
    }

    public function arrSetErrFnc(string $fcnCurrent, int $intLine): array {
        return [
            "class" => get_class($this),
            "function" => $fcnCurrent,
            "line" => $intLine,
        ];
    }

    public function blnTestSafeName(string $strSubject): bool {
        if(strstr($strSubject,"\n")) return false;       // multi-line not safe
        if(preg_match("/^\d/",$strSubject)) return false; // begins with digit
        if(preg_match("/\W/",$strSubject)) return false;  // non-word (except _)
        return true;
    }
}
?>
