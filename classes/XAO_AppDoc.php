<?php
/**
* XAO_AppDoc.php
*
* This script provides the class definition for AppDoc. AppDoc
* creates the framework for XAO to be used in framwork mode. AppDoc inherits
* DomDoc and AppDoc is intended to be inherited by the end user 
* - to provide the
* user with an "Application Object" that is customised by them. See the doc 
* comments directly preceeding the class declaration for more information.
* It is advisable to consult the tutorials shipped with XAO to obtain an
* understanding of how this class is to be used.
*
* @author       Terence Kearns
* @version      1.0 alpha
* @copyright    Terence Kearns 2003
* @license      LGPL (see http://www.gnu.org/licenses/lgpl.txt )
* @link         http://xao-php.sourceforge.net
* @package      XAO
* @see          class DomDoc
*/

/**
* Import XAO base class - DomDoc
* 
* The AppDoc framework is extends DomDoc which extends XaoRoot.

* 
* @import Transformer
*/
include_once "XAO_DomDoc.php";

/**
* Import Transformer class for use by association
* 
* This class encapsulates all XSLT functionality.
* 
* @import Transformer
*/
include_once "XAO_Transformer.php";

/**
* XML Application Object
*
* AppDoc is the "Application Object" part of the XAO acronym. It represents the
* "Application Document" in the context of XML being a document. So the contents
* delivered to the user by the "Application" is contentained in the "Document"
* held as a DOM XML document by AppDoc::objDoc. the objDoc property is inherited
* from the DomDoc class. The AppDoc class provides extra functionality on 
* DomDoc that is specifically needed for typical "framework mode" usage of XAO. 
* Usage of this class assumes that the user will be employing XAO in framework 
* mode. In short, this is the framework class for XAO. It is advisable to 
* consult the tutorials shipped with XAO to obtain an understanding of how this 
* class is intended to be used.
*
* @package      XAO
*/
class AppDoc extends DomDoc {
    
    /**
    * Cache parameters for the XSLT tranformation result
    *
    * In the standard XAO framework, caching can be done at two stages. The 
    * first is at the content generation stage which uses $arrDocCacheParams,
    * the second is at the transformation results stage which uses this array.
    * The array has the same requirements and is used in exactly the same way
    * as the $arrDocCacheParams array. It is passed to the Transformer class
    * (by $this->Transform method) which then uses CacheMan to implement it. 
    * Also see doc comments at XaoRoot::arrCacheParams for more info.
    * 
    * @access   public
    * @var      array  
    */
    var $arrXsltCacheParams = array();

    /**
    * Stylesheet parameters
    * 
    * See documentation in the Transformer class on the variable with the
    * same name.
    *
    * @access   public
    * @var      array
    */
    var $arrXslParams = array();
    
    /**
    * Storage slot for alternative payload
    *
    * If this variable is populated, then this is all that is sent to the UA
    * via the AppDoc::Send method. If it is left empty, then the AppDoc::Send
    * method will send the serialised content of DomDoc::objDoc
    * This method is provided as a method of short-circuiting the default
    * behavior of AppDocc::Send - thereby allowing AppDoc::Send to be the 
    * single/only way for which the payload is transmitted. This is very 
    * important because it allows XAO framework to sentralise control script 
    * completion and payload transmission.
    * An example of where an alternate payload is needed is an XSLT 
    * transformation result. Other instances, where the well-formedness of a 
    * payload cannot be garenteed, will also require the use of this string
    * variable.
    * 
    * @access   public
    * @var      string  
    */
    var $strAltPayload = false;

    /**
    * Debug data used when $this->blnDebug option is set to true
    * 
    * This variable will contain extra diagnostic information as well as 
    * standard errors under certain circumstances. It is only outputted if
    * $this->blnDebug is set.
    *
    * @access   private
    * @var      string  
    */
    var $_strDebugData;

    /**
    * Force client-side XSL Transformation attempt.
    *
    * This causes the TransformSend() method to bypass server-side 
    * transformation and send the source document directly to the client. If 
    * the stylsheet PI is set, then the client should find it and perform it's 
    * own transformation.
    * 
    * @access   public
    * @var      boolean  
    */
    var $blnClientSideTransform = false;

    /**
    * Dedicated Error output display template
    *
    * Under certain conditions, the availability of a dedicated error template
    * will cause an exceptional condition to output the error DOM object (with
    * all it's errors so far) using the same rules as $this->Send(). An example
    * is the Transform object which uses it when $this->blnDebug is turned off.
    * If no dedicated error stylsheet is supplied, then it behaves differently.
    * It is empty by default in case it proposes any security issues.
    *
    * @access   public
    * @var      uri  
    */
    var $uriErrorStyle;

    /**
    * Internal error style for debug output
    *
    * When $this->blnDebug is enabled, then the error document is appended
    * using this a transformation with this error stylsheet. THIS IS NOT
    * IMPLEMENTED AT THE MOMENT. IT MAY NEVER BE.
    *
    * @access   private
    * @var      uri  
    */
    var $_uriXaoErrorStyle = "XAO_errors.xsl";

    /**
    * Debug option
    *
    * Designed to be used during development, this object will cause error
    * output to be more verbose unser certain conditions. It may also be used
    * by the developer to output diagnostic information at run-time. It is kept 
    * off by default in case it proposes any security issues.
    * 
    * @access   public
    * @var      boolean  
    */
    var $blnDebug = false;
    
    /**
    * Debug option
    *
    * Designed to be used during development, this object will cause error
    * output to be more verbose unser certain conditions. It may also be used
    * by the developer to output diagnostic information at run-time. It is kept 
    * off by default in case it proposes any security issues.
    * 
    * @access   public
    * @var      boolean  
    */
    var $strForceContentType;

    /**
    * Current stylesheet URI
    * 
    * This represents the current stylesheet that is used by this DomDoc.
    * If a user overrides the $_uriStyleSheet member variable with a populated
    * version, this->ndSetStylePI() is called with it in the contructor.
    *
    * @access   private
    * @var      uri  
    */
    var $_uriStyleSheet;

    /**
    * Stylesheet processing instruction node
    * 
    * This is the node object representing the stylesheet PI. It is set using 
    * the ndSetStylePI() method which only matains one PI node for the 
    * stylesheet. If a user overrides the $_uriStyleSheet member variable with 
    * a populated version, this ndSetStylePI() is called with it in the 
    * contructor.
    *
    * @access   public
    * @var      node  
    */
    var $ndStylePi;

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
    var $strXsltProcessor = "SABLOTRON"; // becuase the DOMXML one is buggy
    
    /**
    * Stylesheet native PHP DOM XML object
    * 
    * This variable is populated if the specified stylsheet is successfully
    * opened as an XML document.
    *
    * @access   public
    * @var      object
    */
    var $objXsl;
    
    /**
    * AppDoc constructor
    *
    * This method runs the parent constructor and sets up the xao namespace.
    * There is no way to detect if a namespace declaration exists 
    * (to prevent duplicates). At the moment, one is inserted regardless!!!
    * This is absolutely neccesary due to their usage by exceptions.
    * WARNING::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    * THIS  MEANS THAT YOU CANNOT IMPORT EXISTING XML FILES WHICH HAVE A
    * THE NAMESPACE xmlns:xao ALREADY DECLARED IN THE ROOT ELEMENT.
    * The current DOMXML extension allows multiple attributes of the same name 
    * to be inserted (not good). It's namespace functions don't allow a 
    * [non-default] namespace declaration to be inserted without changing the 
    * prefix of the tag in context - so we're forced to use the dubious
    * "set_attribute()" method instead.
    *
    * @param    mixed   starting data
    * @param    integer how to use the staring data
    * @return   void
    * @access   public
    */
    function AppDoc(&$mxdStarter,$intUse = XAO_DOC_NEW) {
        $this->DomDoc($mxdStarter,$intUse);
                                        // automatically inject the XAO 
                                        // namespace into the document root
        /* 
        */
        
        if(is_object($this->ndRoot)) {
            $this->ndRoot->set_attribute(
                "xmlns:".$this->strXaoNamespacePrefix,
                $this->idXaoNamespace
            );
        }
    }
    
    /**
    * Insert stylesheet processing instruction
    *
    * This processing instruction is used by the transform() method if a
    * stylesheet URI is not specifically provided to it. This method will
    * automatically be called in the constructor if the user overrides the 
    * $this->_uriStyleSheet member attribute. It may, however, be called at any
    * time by the user. Only one xsl stylsheet PI is maintained in the 
    * document. If it was already set at the time of the call to this method, 
    * then the new stylsheet URI will _replace_ the one in the existing PI.
    *
    * @param    uri     path to XSL stylesheet
    * @param    boolean Whether or not to check(parse) the file.
    * @return   bool    success
    * @access   public
    */
    function &ndSetStylePI($uriStyleSheet,$blnCheck = true) {
        $this->_TestForConstuctor();
        if($blnCheck) {
            if(!file_exists($uriStyleSheet)) {
                $this->Throw(
                    "ndSetStylePI: The stylsheet you specified: <strong>"
                    .$uriStyleSheet."</strong> does not exist. Set local file "
                    ."checking (parsing) to false in the second argument of "
                    ."DomDoc::ndSetStylePI() if the file exists remotely or "
                    ."you want to override checking.",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__),
                    true
                );
                return false;
            }
        }
        $this->_uriStyleSheet = $uriStyleSheet;
        $strPiCont = ' type="text/xsl" href="'.$this->_uriStyleSheet.'"';
        $strPiTarget = 'xml-stylesheet';
          $piStyle = $this->objDoc->create_processing_instruction(
            $strPiTarget, $strPiCont
        );
        $this->_uriStyleSheet = str_replace("\\","/",$this->_uriStyleSheet);
        if(!is_object($piStyle)) {
            $this->Throw(
                "ndSetStylePI: Unable to create processing instruction using "
                ."target of ".$strPiTarget." and content of ".$strPiCont,
                $this->arrSetErrFnc(__FUNCTION__,__LINE__),true
            );
            return false;
        }
        else{
                                        // if one exists, replace it
            if(is_object($this->ndStylePi)) {
                $this->ndStylePi->replace_node($piStyle);
            }
                                        // otherwise create it
            else {
                $this->ndStylePi = $this->objDoc->insert_before(
                    $piStyle, $this->ndRoot
                );
            }
        }
        if(is_object($this->ndStylePi)) {
            return $this->ndStylePi;
        }
        else {
            return false;
        }
    }

    /**
    * Get the absolute system location of the internal error stylsheet.
    *
    * This method is needed to enforce a location that is relative to 
    * XAO_AppDoc.php and not the last file in the call stack. See notes on
    * $this->SetInternalErrorStyle();
    *
    * @return   uri        Absolute path to internal error stylsheet.
    * @access   public
    */
    function uriGetInternalErrorStyle() {
        $this->_TestForConstuctor();
        return dirname(__FILE__)."/".$this->_uriXaoErrorStyle;
    }
    
    /**
    * Set the name of the internal error stylsheet using in debug mode
    *
    * This shouldn't need to be changed. However, if it is not appropriate
    * for your circumstances, you may change it. Bear in mind that the uri
    * must be specified as a relative path to the physical location of the
    * xao DomDoc.php file.
    *
    * @param   uri     Relative path to internal error stylesheet
    * @return  void
    * @access  public
    */
    function SetInternalErrorStyle($uri) {
        $this->_uriXaoErrorStyle = $uri;
    } 

    
    /**
    * Prepares $this->strAltPayload with XSLT transformation result data
    *
    * This function is usually called just prior to $this->Send()
    * It is used when XSLT tranformations are required. It short-circuits the
    * behaviour of $this->Send by populating $this->strAltPayload the results
    * of the transformation. Note that this method requires a cirtain amount of
    * preparation work by the user - ie. a stylsheet must be set using
    * $this->ndSetStylePi()
    *
    * @return   void
    * @access   public
    */
    function Transform($arrCacheParams = null) {
    $arrCacheParams = array();
        $this->_TestForConstuctor();
                                        // $this->strAltPayload is not modified
                                        // if the browser is to transform the
                                        // contents of $this->objDoc
        if(!$this->blnClientSideTransform) {
        
                                        // Associate a new transformer
            $objXT =& new Transformer($this->objDoc,$this->_uriStyleSheet);
                                        // pass on transform properties
            $objXT->strXsltProcessor      = $this->strXsltProcessor;
            $objXT->strXaoNamespacePrefix = $this->strXaoNamespacePrefix;
            $objXT->arrCacheParams        = $this->arrXsltCacheParams;
            $objXT->arrXslParams          = $this->arrXslParams;
            if(count($arrCacheParams)) $objXT->arrCacheParams = $arrCacheParams;

                                        // set up namespaces in stylsheet 
            /* 
              can't do namespaces because there is no way to detect if a
              namespace declaration exists (to prevent duplicates)
              WARNING:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
              THIS  MEANS THAT YOU HAVE TO DECLARE THE xao NAMESPACE YOURSELF
              (MANUALLY) IN ALL YOUR STYLESHEET (IF YOU WANT TO USE TEMPLATES
              THAT MATCH ANY XAO STUFF LIKE xao:exceptions).
            */
            
            /*
            if(isset($objXT->ndStyleRoot)) {
                if(is_object($objXT->ndStyleRoot)) {
                    var_dump($objXT->ndStyleRoot->attributes());
                    die();
                    if(
                                        // to do: This detection does not work.
                        !$objXT->ndStyleRoot->has_attribute(
                            "xmlns:".$this->strXaoNamespacePrefix
                        )
                    ) {
                        //var_dump($objXT->objStyle->dump_node($objXT->ndStyleRoot));
                        //die("<br />We did not detect the namespace.");
                        $objXT->ndStyleRoot->set_attribute(
                            "xmlns:".$this->strXaoNamespacePrefix,
                            $this->idXaoNamespace
                        );
                    }
                }
            }
            */
                                        // See if there were any problems 
                                        // reported by the Transfromer instance
            if(strlen($objXT->strError)) {
            	    $this->strAltPayload = $objXT->strError;
                /*
                $this->Throw(
                    $objXT->strError,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
                */
            }
            else {
                                        // perform the actual transformation and
                                        // pass the results to 
                                        // $this->strAltPayload
                if($objXT->Transform()) {
                    $this->strAltPayload = $objXT->strXsltResult;
                    if($this->blnDebug) {
                        global $_GET;
                        if(isset($_GET["xao:XSL"])) {
                            $this->strAltPayload = 
                                $objXT->objStyle->dump_mem(true);
                        }
                    }
                }
                else {
                    $this->Throw(
                        $objXT->strError,
                        $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                    );
                }
            }
        }
    }
    

    /**
    * Send the serialised XML content of this object to the client
    *
    * This function will emit the contents of this XML document as a string to
    * the user-agent. It checks to see if the option was set to bind the error
    * data [built up from usage of $this->Throw()] to the content. It also
    * signals the user agent to expect text in XML format.
    * When XAO is used as a framework, then some sort of send
    *
    * @param    string alternate payload which will be sent
    * @access   public
    */
    function Send($strAlt = "") {
        
        $this->_TestForConstuctor();
                                        // This method may well be the last one
                                        // that is called by the host PHP script
                                        // in which case, try and collect all
                                        // user errors.
        $this->_TrapErrors();
        
        if(strlen($strAlt)) $this->strAltPayload = $strAlt;
                                        // If this object is running in debug
                                        // mode, then special XAO URL directives
                                        // can be acted on.
        if($this->blnDebug) {
            global $_GET;
                                        // this debug directive causes a source
                                        // dump of the XML content regardless of
                                        // any alternate payload.
            if(isset($_GET["xao:XML"])) {
                //$this->strAltPayload = $this->xmlGetDoc();
                header("Content-Type: text/plain");
                die($this->xmlGetDoc());
            }
            elseif(isset($_GET["xao:Text"])) {
                $this->strForceContentType = "text/plain";
            }
        }
        
        if(strlen($this->strAltPayload)) {
            if(strlen($this->strForceContentType))
                header("Content-Type: ".$this->strForceContentType);
            elseif(substr($this->strAltPayload,1,4) == "?xml")
                header("Content-Type: text/xml");
            echo $this->strAltPayload;
        }
        else {
            if(strlen($this->strForceContentType))
                header("Content-Type: ".$this->strForceContentType);
            else
                header("Content-Type: text/xml");
            echo $this->xmlGetDoc();
        }
    }
    
    /**
    * Return data that would be destined for the client (in current state)
    *
    * This function is specific to AppDoc and not DomDoc because of the
    * strAltPayload member attribute. This function can be used by the 
    * RpcController class if the user bases their request class on AppDoc. For
    * instnace, they may want to take advantage of AppDoc's transform
    * capabilities to convernt proprietary content into another format such
    * as RSS.
    *
    * @return   string
    * @access   public
    */
    function strGetPayload() {
        if(strlen($this->strAltPayload)) return $this->strAltPayload;
        return $this->xmlGetDoc();
    }
    
    /**
    * Wrapper for parent::throw() function adding ability to abort script
    *
    * This function basically calls the parent function of the same name but
    * also allows the caller to optionally abort the script if the last
    * argument is set to true.
    *
    * @param    string Main error message
    * @param    array A hash of name/vals which will be attributes in the 
    *           exception tag
    * @access   public
    */
    function Throw($strErrMsg,$arrAttribs = null,$blnDie = false) {
        if(is_null($arrAttribs)) $arrAttribs = array();
        parent::Throw($strErrMsg,$arrAttribs);
        if($blnDie) die("<br />\n".$this->strError.$this->strDebugData);
    } 

    /**
    * Try to trap any user-triggered errors for handling by DomDoc::throw
    *
    * This function is a partial solution for general PHP error handling based
    * on PHP's own error management capabilities. Unfortunately, PHP will only
    * allow your error handling call-back function to process what it calls
    * "USER" errors - errors that are triggered using the trigger_error() or
    * user_error() functions. PHP does not let you manage PHP generated errors.
    * Furthermore, this function is only useful when people use XAO in framework
    * mode and neccesarily name their Application document object $objAppDoc
    *
    * @access   private
    */
    function _TrapErrors() {
                                        // this method is only useful if you 
                                        // name your Application document object
                                        // $objAppDoc
        global $objAppDoc;
                                        // custom error handler cannot be set 
                                        // from within an object. It also needs
                                        // the handler to be a router to the
                                        // application object (if found in the
                                        // global scope).
            if(is_object($objAppDoc)) {
                set_error_handler("ErrorRouter");
            }
    }
} // END CLASS


/**
* Custom error handler function
*
* Normally everything in XAO adheres strictly to being coded as object oriented,
* however, since that's not how PHP was designed, exceptions have to be made.
* The following function is referred to by the AppDoc::_TrapErrors() method
* as specified by the set_error_handler("ErrorRouter") function. The call-back
* specified in set_error_handler() is not able to exist inside a class 
* definition if it is to work as intended with PHPs custom error handling. It 
* is only effective if the user instantiates $objAppDoc as part of a 
* conventional XAO methodology.
*
* @param    string  error code
* @param    string  error message
* @param    string  error location of context script
* @param    integer line number of context script
* @param    array   any arguments involved in an errored function
* @access   private
*/
function ErrorRouter($strErrCode, $strErrMsg, $uriContext, $intLine, $mxdArgs) {
                                        // This is the sort of nonsense required
                                        // by a non-oo approach.
    global $objAppDoc;
    $arrAttribs = array(
        "code"    => $strErrCode,
        "file"    => $uriContext,
        "line"    => $intLine,
        "phpArgs" => serialize($mxdArgs)
    );
                                        // ATM, error reports require an XML
                                        // aware user-agent.
    $objAppDoc->Throw("PHP ERROR: ".$strErrMsg,$arrAttribs);
}
?>
