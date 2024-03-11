<?php

/******************************************************************************\
* IMPORT REQUIRED CLASSES
\******************************************************************************/

/**
* Import the root (base) XAO class.
* 
* All classes in the XAO library should have this class at the top of their
* inheritance tree. See documentation on the class itself for more details.
* Including the following DomDoc class will automatically include the XaoRoot
* definition, however, it is included here for clarity and completeness.
*
* @import XaoRoot
*/
include_once "XAO_XaoRoot.php";

/**
* Import the basic DomDoc class for inheritance.
* 
* This class is based on DomDoc and hence supports all of it's functionality,
* including it's ability to be consumed by another DomDoc based object.
* 
* @import DomDoc
*/
include_once "XAO_DomDoc.php";

/**
* Import an XML parsing utility class used for debugging XML errors.
* 
* @import DomDoc
*/
include_once "XAO_DomFactory.php";

/**
* Utility performing XSLT transformations
*
* This class encapsulates comprehensive XSLT functionality. It makes available
* multiple (pluggable) transformation engines through a unified, simplified API.
*
* @author       Terence Kearns
* @version      0.0
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @package      XAO
* @link         https://github.com/tezza1971/XAO-PHP
*/
class Transformer extends XaoRoot {

    /**
    * DOM XML object instance of the source document
    * 
    * This has to be specified in the constructor. The Transformer class will
    * not work without this object and a DOM XML instnace of the stylsheet.
    * The constructor may need to ensure this object eventuates by parsing
    * in external XML data - same applies for the stylesheet.
    *
    * @access   public
    * @var      object  
    */
    var $objSrc;
    
    /**
    * Root element of source document object
    * 
    * @access   public
    * @var      node  
    */
    var $ndSrcRoot;

    /**
    * DOM XML object instance of stylsheet used in transformation
    * 
    * This has to be specified in the constructor. The Transformer class will
    * not work without this object and a DOM XML instnace of the source doc.
    * The constructor may need to ensure this object eventuates by parsing
    * in external XML data - same applies for the source doc.
    *
    * @access   public
    * @var      object  
    */
    var $objStyle;
    
    /**
    * Root element of source document object
    * 
    * @access   public
    * @var      node  
    */
    var $ndStyleRoot;

    var $_uriStyleSheet;
    
    /**
    * List of XSL parameters to be passed to the XSLT processor
    *
    * Usually these will be set by the application class which inherits DomDoc.
    * The transform methods of this class should use this associative array to
    * add these parameters to the processor.
    * WARNING: RELYING ON THIS METHOD OF SETTING XSL PARAMS WILL MAKE YOUR
    * STYLSHEET INCOMPATIBLE WITH CLIENT-SIDE TRANSFORMATION.
    * IMPORTANT NOTE: YOUR PARAMS WILL NOT BE AVAILABLE IN YOUR STYLSHEET IF
    * YOU DO NOT DECLARE THEM (WITH EMPTY VALUES). THE PROCESSOR WILL ONLY FEED
    * PARAM VALUES TO THE STYLESHEET BY OVERRIDING THE VALUES OF EXISTING ONES.
    *
    * @access   public
    * @var      array  
    */
    var $arrXslParams = array();

    /**
    * Which XSLT processor to use
    * 
    * This option allows the user to choose which implemented XSLT processor
    * to employ. At this stage, possible choices are: 
    * - SABLOTRON which uses the xslt_ functions built into PHP.
    * - DOMXML which uses the experimental transformation capabilities of the
    *   native PHP domxml extension itself.
    * Future implementations could use external procesors which may be Java, 
    * Com, or command-line executables.
    *
    * @access   public
    * @var      string  
    */
    var $strXsltProcessor = "DOMXML";
    
    /**
    * XSLT processing result container
    * 
    * Regardless of which XSLT processor is used, the result is always stored
    * in this variable AS A STRING.
    *
    * @access   public
    * @var      string  
    */
    var $strXsltResult;

    /**
    * Transformer constructor
    *
    * The main job here is to parse the source XML and the XSLT as DOM XML
    * objects. Note that this technique incurs unneccesary overhead for 
    * tranform processors like sablotron. But this is neccesary to maintain
    * the simplicity of the API - it's a trade-off!
    * The stylesheet and the XML source can be supplied in one of three formats:
    * 1) DOM XML object (preferrred)
    * 2) file URI
    * 3) well formed ascii data
    *
    * @param    mixed   starting XML source data
    * @param    mixed   starting stylsheet
    * @return   void
    * @access   public
    */
    function Transformer(&$mxdSrc,&$mxdStyle) {
        
                                        // set up source XML document
        if(is_string($mxdSrc)) {

            $objDomFactory =& new DomFactory($mxdSrc);
            
            if(strlen($objDomFactory->strErrorMsg)) {
                $this->strDebugData = $objDomFactory->strDebugData;
                $this->Throw(
                    $objDomFactory->strErrorMsgFull,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->objSrc =& $objDomFactory->objGetObjDoc();
            }
        }
        elseif(is_object($mxdSrc)) {
            $ndTest = $mxdSrc->document_element()
                OR $this->Throw(
                    "Transformer: Object is not a PHP DOM XML object.",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            if(is_object($ndTest)) {
                $this->objSrc =& $mxdSrc;
                //var_dump($ndTest);
                //die();
            }
            else {
                $this->Throw(
                    "Transformer: DOM XML object does not have a root element",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
        }
        else {
            if(is_null($mxdSrc)) {
                $this->Throw(
                    "Transformer: NULL source XML argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->Throw(
                    "Transformer: Invalid source XML argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
        }
        
                                        // set up transformation document
        if(is_string($mxdStyle)) {
            
            $objDomFactory =& new DomFactory($mxdStyle);
            
            if(strlen($objDomFactory->strErrorMsg)) {
                $this->strDebugData = $objDomFactory->strDebugData;
                $this->Throw(
                    $objDomFactory->strErrorMsgFull,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->objStyle =& $objDomFactory->objGetObjDoc();
            }
            $this->_uriStyleSheet = $objDomFactory->uriContextFile;
        }
        elseif(is_object($mxdStyle)) {
            $this->objStyle =& $mxdStyle;
        }
        else {
            if(is_null($mxdStyle)) {
                $this->Throw(
                    "Transformer: NULL stylesheet argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->Throw(
                    "Transformer: Invalid stylesheet argument",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
        }
        
        if(is_object($this->objStyle)) {
            $this->ndStyleRoot =& $this->objStyle->document_element();
        }
        
        if(is_object($this->objSrc)) {
            $this->ndSrcRoot =& $this->objSrc->document_element();
        }
    }
    
    function Throw($strErrMsg,$arrAttribs = null) {
        parent::Throw($strErrMsg,$arrAttribs);
                                        // This is probably the ONLY place you 
                                        // will see a die() statement. This is
                                        // because normally, errors are 
                                        // displayed by way of a stylsheet 
                                        // template. However if transformation
                                        // cannot take place, then the exception
                                        // will never be displayed.
                                        // Exceptions should never be dealt with
                                        // like this. This is a last resort.
        if(strlen(trim($this->strDebugData))) 
            die("<br />\n".$this->strError.$this->strDebugData);
    }
    
    function SetXslParam($strName,$strValue) {
                                        // This test should be improved to use
                                        // a regex which properly check for 
                                        // stylesheet param name legality
        if($this->blnTestSafeName($strName)) {
            $this->arrXslParams[$strName] = $strValue;
        }
        else {
            $this->Throw(
                "Please specify a valid XSL parameter name. "
                ."Multiline, beginning with numbers, non-alpha-numeric "
                ."characters are NOT allowed. An underscore is allowed.",
                $this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
        }
    }
    /**
    * Transform contents using stylsheet in $this->_uriStylSheet
    *
    * This function calls the Transformer class which takes a DomDoc in the
    * first argument to the constructor as a reference. The Transformer object 
    * records any errors using the passed DomDoc's Throw() method (so the 
    * errors are bound to that source document). This function makes some 
    * decisions on how errors are released based on options set in $this 
    * DomDoc instance. Since a transformation result isn't always well-formed 
    * XML, the result is returned as a string. Here are the steps taken:
    *  1) check for the existance of a stylsheet
    *  2) route the request to the chosen XSLT processor member function
    *  3) return the result.
    *
    * @return   string  The contents of the transformation result or false 
    *                   on failure
    * @access   public
    */
    function Transform() {
                                        // provide a handy alternative for
                                        // client side access to server-side
                                        // XSL parameters.
        if(count($this->arrXslParams)) {
                                        // establish XAO namespace prefix
            $strPfx = "";
            if(strlen($this->strXaoNamespacePrefix))
                $strPfx = $this->strXaoNamespacePrefix.":";
                                        // set up container element for all 
                                        // params
            $ndParams = $this->objSrc->create_element($strPfx."xslParams");
            $ndParams = $this->ndSrcRoot->append_child($ndParams);
                                        // create each param in this container
            foreach($this->arrXslParams AS $strName => $strVal) {
                $ndParam = $this->objSrc->create_element($strPfx."param");
                $ndParam = $ndParams->append_child($ndParam);
                $ndParam->set_attribute("name", $strName);
                $ndParam->set_content($strVal);
            }
        }
        
        $blnSuccess = false;
        switch($this->strXsltProcessor) {
            case "SABLOTRON": 
                $blnSuccess = $this->_TransformWithSablotron(); 
            break;
            case "DOMXML": 
                $blnSuccess = $this->_TransformWithDomxml(); 
            break;
            default:
                $this->Throw(
                    "Transform: ".$this->strXsltProcessor
                    .": Not a valid XSLT processor.",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
        }
        
        return $blnSuccess;
    }
    
    /**
    * DOMXML XSLT implementation
    * 
    * This processor implementation uses the native PHP DOM XML processor. At 
    * the time or writing, it is experimental. I noticed that it fails on very 
    * basic functionality and that the error reporting has not yet been refined
    * The major advantage with this processor is that it is bundled with PHP and
    * no additional PHP extensions need to be enabled. It is not ready for use
    * though - neither in a production environement or for development (due to
    * poor error reporting). SABLOTRON is reccommended but requires the XSLT
    * extension to be enabled.
    * 
    * @return   boolean   Success or failure (true/false).
    * @access   private
    */
    function _TransformWithDomxml() {
                                        // adding XSL params from the server-
                                        // side has important subtlties. The
                                        // server should only *replace* existing
                                        // xsl params, and only ones that
                                        // exist immediately below the root.
                                        // This behaviour is at least consistent
                                        // with the Sablotron processor.
                                        // Also, any existing "name" attrib
                                        // should be removed since *either* the
                                        // "name" attrib *or* the content is
                                        // populated. We populate the element
                                        // content because it's easier than
                                        // having to create single-quoted 
                                        // strings. It's probably safer anyway.
        $strPrfx = $this->ndStyleRoot->prefix();
        if(strlen($strPrfx)) $strPrfx .= ":";
        $strPName = $strPrfx."param";
        $arrExistParamNames = array();
        $arrNdExists =& $this->ndStyleRoot->child_nodes();
        foreach($arrNdExists AS $ndExists) {
            if($ndExists->node_type() == "domelement") {
                if($ndExists->node_name() == $strPName) {
                    $strParamName = $ndExists->get_attribute("name");
                    if(isset($this->arrXslParams[$strParamName])) {
                        $ndExists->set_content(
                            $this->arrXslParams[$strParamName]
                        );
                        $ndExists->remove_attribute("select");
                    }
                }
            }
        }
                                        // convert the DOM doc to a stylsheet
                                        // doc
        if(!$objStylesheet = domxml_xslt_stylesheet_doc($this->objStyle)) {
            $this->Throw($this->_uriStyleSheet." 
                is an XML document but it is not a valid not a stylesheet.",
                $this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
            return false;
        }
                                        // Do the transformation and trap any
                                        // error output.
        ob_start();
            $objDomResult =& $objStylesheet->process($this->objSrc);
            $this->strDebugData = ob_get_contents();
        ob_end_clean();
                                        // extract the results
        if(is_object($objDomResult)) {
            $this->strXsltResult =& $objDomResult->dump_mem(true);
            return true;
        }
        else {
            $strErrMsg = "There were errors while trying to transform the 
                document <strong>".$this->_uriStyleSheet."</strong> 
                using the <strong>DOMXML</strong> processor. Be warned that this
                processor is EXPERIMENTAL and should not be used. Error output
                (below) not likely to be of much use.\n\n".$phpErr;
            $this->Throw($strErrMsg,$this->arrSetErrFnc(__FUNCTION__,__LINE__));
            return false;
        }
    }
    
    /**
    * Use the built-in SABLOTRON processor to perform XSLT
    *
    * The Sablotron XSLT processor (from gingerall.com) is an extremely fast and
    * stable transformation engine. The class sets the default processor to
    * SABLOTRON but this assumes that the server has the XSLT extension to PHP
    * enabled. If it is absolutely not possible to have this extension enabled,
    * then your application class (which should inherit DomDoc) can set this to
    * DOMXML in the constructor. There are a number of reasons why you do not
    * want to do this - see the API documentation for _TransformWithDomxml()
    *
    * @return   boolean Sucess or failure (true/false).
    * @access   private
    */
    function _TransformWithSablotron() {
        if(!extension_loaded("xslt")) $this->Throw(
            "_TransformWithSablotron: This PHP server does have the XSLT "
            ."extension enabled.",$this->arrSetErrFnc(__FUNCTION__,__LINE__)
        );
        $xt = @xslt_create() OR $this->Throw(
            "_TransformWithSablotron: Could not create an XSLT processor.",
            $this->arrSetErrFnc(__FUNCTION__,__LINE__)
        );
        xslt_set_error_handler($xt,array(&$this,"_SablotronErrorTrap"));
        
                                            // Sablotron has the ability to
                                            // register a base path location
                                            // which it can use to resolve
                                            // external entities such as import
                                            // and include directives.
                                            // Here, we provide the facility
                                            // but can only do so if the style-
                                            // sheet was specified via a URI - 
                                            // in which case $this->_uriStyleSheet
                                            // is populated in the constructor.
        if(file_exists($this->_uriStyleSheet)) {
                                            // sablotron requires a URI protocol
                                            // identifier for it's base path.
            $uriBaseDir = "file://";
                                            // determine if the style URI is 
                                            // relative or absolute.
            if(
                   substr($this->_uriStyleSheet,0,1) != "/"
                && substr($this->_uriStyleSheet,1,3) != ":/"
            ) {
                                            // if relative, use the contextual
                                            // path prepended to the style uri path
                $uriBaseDir .= 
                    str_replace(
                        "\\",
                        "/",
                        dirname(realpath($_SERVER["PATH_TRANSLATED"]))
                    )."/";
            }
                                            // ends with style path
            $uriBaseDir .= dirname($this->_uriStyleSheet)."/";
                                            // apply the base path
            xslt_set_base($xt,$uriBaseDir);
        }
        
        $args = array(
            '/_xml' => $this->objSrc->dump_mem(), 
            '/_xsl' => $this->objStyle->dump_mem()
        );

        $this->strXsltResult = @xslt_process(
            $xt,
            "arg:/_xml",
            "arg:/_xsl",
            null,
            $args,
            $this->arrXslParams
        );
        
        if(strlen($this->strXsltResult)) return true;
        
        return false;
    }
    
    function _SablotronErrorTrap(
        $resSab,
        $intSabErr,
        $strSabLvl,
        $arrSabErrData
    ) {
        $strMsg = "Sablotron XSLT error ";
                                        // if applicable, reveal the location
                                        // of the stylsheet in use.
        if(strlen($this->_uriStyleSheet)) {
            $strMsg .= " while transforming file <b>"
                .$this->_uriStyleSheet."</b>";
        }
                                        // default debug text is stylsheet
        $xslData =& $this->objStyle->dump_mem(true);
                                        // assume that $xalData is the data in
                                        // context for the Sablotron error.
        $blnHaveTheRightFile = true;
                                        // cater for errors occuring in 
                                        // external files processed by Sablotron
        if(isset($arrSabErrData["URI"])) {
            if($arrSabErrData["URI"] != "arg:/_xsl") {
                                        // The data in context is different
                $blnHaveTheRightFile = false;
                $strMsg .= "<div>Actual error occured while processing external"
                ." entity: <b>".$arrSabErrData["URI"]."</b></div>";
                if(preg_match("/file\:\/\//i",$arrSabErrData["URI"]))
                    $arrSabErrData["URI"] = 
                        substr($arrSabErrData["URI"],strlen("file://"));
                if(file_exists($arrSabErrData["URI"])) {
                                        // change the debug text to the
                                        // external file contents.
                    $xslData = implode("",file($arrSabErrData["URI"]));
                                        // we've managed to obtain the correct
                                        // data in context
                    $blnHaveTheRightFile = true;
                }
            }
        }
                                        // dirty great red error message
        $strMsg .=
            "<div style=\"font-weight: bold; background: red; color: yellow; padding: 10px;\">"
            .$arrSabErrData["msg"]."</div>";
                                        // supplentary Sablotron debug info
        foreach($arrSabErrData AS $strSabErrFld=>$strSabErrVal) {
            if($strSabErrFld != "msg")
            $strMsg .= $strSabErrFld.":".$strSabErrVal." &nbsp; &nbsp; ";
        }
                                        // do a text debug dump if we have a
                                        // line number from sablotron. Don't
                                        // populate this if the data is not in
                                        // the context of the Sablotron error
                                        // (avoid confusion).
            if(isset($arrSabErrData["line"]) && $blnHaveTheRightFile) {
                $objDebugData =& 
                    new TextDebugger($xslData,$arrSabErrData["line"]);
                                        // populate the all-important debug data
                $this->strDebugData =& $objDebugData->strGetHtml();
            }
            elseif(preg_match("/XML parser error/i",$arrSabErrData["msg"])) {
                $objDebugData =& 
                    new TextDebugger($this->objSrc->dump_mem(true));
                                        // populate the all-important debug data
                $this->strDebugData =& $objDebugData->strGetHtml();
            }
            else {
                $this->strDebugData = "<div><b>No debug output available.</b></div>";
            }
        
        $this->Throw($strMsg,$this->arrSetErrFnc(__FUNCTION__,__LINE__));
    }

}
?>