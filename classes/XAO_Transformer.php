<?php
/**
* XAO_Transformer.php
*/

include_once "XAO_XaoRoot.php";
include_once "XAO_DomDoc.php";
include_once "XAO_DomFactory.php";

class Transformer extends XaoRoot {

    public DOMDocument $objSrc;
    public ?DOMElement $ndSrcRoot = null;

    public DOMDocument $objStyle;
    public ?DOMElement $ndStyleRoot = null;

    protected ?string $_uriStyleSheet = null;

    /** XSL parameters */
    public array $arrXslParams = array();

    /** Which processor (kept for BC; now always uses ext/xsl) */
    public string $strXsltProcessor = "XSLTProcessor";

    /** XSLT output result (string) */
    public string $strXsltResult = "";

    public function __construct($mxdSrc, $mxdStyle) {
        $this->Transformer($mxdSrc, $mxdStyle);
    }

    // Back-compat old-style constructor signature
    public function Transformer($mxdSrc, $mxdStyle) { 
        // set up source XML document
        if(is_string($mxdSrc)) {
            $objDomFactory = new DomFactory($mxdSrc);
            if(strlen($objDomFactory->strErrorMsg)) {
                $this->strDebugData = $objDomFactory->strDebugData;
                $this->Throw(
                    $objDomFactory->strErrorMsgFull,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->objSrc = $objDomFactory->objGetObjDoc();
            }
        }
        elseif($mxdSrc instanceof DOMDocument) {
            $this->objSrc = $mxdSrc;
        }
        else {
            if(is_null($mxdSrc)) {
                $this->Throw(
                    "Transformer: NULL source XML argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            } else {
                $this->Throw(
                    "Transformer: Invalid source XML argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
        }

        // set up transformation document
        if(is_string($mxdStyle)) {
            $objDomFactory = new DomFactory($mxdStyle);
            if(strlen($objDomFactory->strErrorMsg)) {
                $this->strDebugData = $objDomFactory->strDebugData;
                $this->Throw(
                    $objDomFactory->strErrorMsgFull,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->objStyle = $objDomFactory->objGetObjDoc();
                $this->_uriStyleSheet = $objDomFactory->uriContextFile;
            }
        }
        elseif($mxdStyle instanceof DOMDocument) {
            $this->objStyle = $mxdStyle;
        }
        else {
            if(is_null($mxdStyle)) {
                $this->Throw(
                    "Transformer: NULL stylesheet argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            } else {
                $this->Throw(
                    "Transformer: Invalid stylesheet argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
        }

        $this->ndStyleRoot = $this->objStyle->documentElement;
        $this->ndSrcRoot = $this->objSrc->documentElement;
    }

    public function transform(array $params = []): string
    {
        if (!extension_loaded('xsl')) {
            $this->Throw("The ext/xsl extension is required to perform XSLT transformations.", $this->arrSetErrFnc(__FUNCTION__, __LINE__));
            return '';
        }

        $proc = new XSLTProcessor();
        // Register PHP functions if needed (disabled by default for security)
        // $proc->registerPHPFunctions();

        // Import stylesheet
        $proc->importStylesheet($this->objStyle);

        // Apply parameters
        foreach (($params ?: $this->arrXslParams) as $name => $value) {
            // Namespaced params can be set using Clark notation {uri}local
            $proc->setParameter('', (string)$name, (string)$value);
        }

        // Transform to string
        $result = $proc->transformToXML($this->objSrc);
        $this->strXsltResult = $result !== false ? $result : '';
        return $this->strXsltResult;
    }

    public function Throw($strErrMsg,$arrAttribs = null): void {
        parent::Throw($strErrMsg,$arrAttribs);
        // In transformer context, failures are critical
        // and we want to fail fast to avoid partial responses in pipelines.
        // Comment the next line out if you want soft failures.
        // die($this->strError);
    }
}
