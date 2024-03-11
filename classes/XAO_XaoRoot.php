<?php
/** 
* XAO_DomDoc.php
*
* This script provides base class for alll the classes in the XAO
* library. Some of the methods need to be overriden. It serves to provide the
* minimum features for any XAO class.
*/

/**
* XAO REQUIREMENTS CHECKS
*
* XAO cannot work without first meeting some basic requirements. This script is
* used to test these requirements as it is the base class and is therefore 
* inevitably included before any XAO classes can be used.
*/
function XAO_CHECK_MINIMUM_REQUIREMENTS() {
    $arrPhpVer = explode(".",phpversion());
    if($arrPhpVer[0] != 4) die("Currently XAO only supports PHP version 4");
    
    if(extension_loaded("domxml")) {
        if(!function_exists("domxml_new_doc"))
            die(
                "The version of DOMXML that you are using is too outdated for XAO. "
                ."Please upgrade to a newer version of PHP4."
            );
    }
    else {
        die("The DOMXML extension to PHP is required for XAO functionality.");
    }
    
    if(!extension_loaded("xml"))
        die("XAO requires the XML extension to be loaded.");
}
XAO_CHECK_MINIMUM_REQUIREMENTS();


/** 
* Base class from which all XAO classes are inherited,
*
* This class is previded mainly for the purposes of centralisation so that any
* properties and methods required by all classes in the framework, can easily
* be added at this one central point. It provides crude error 
* handling capabilties which are extended for  content-based 
* classes.
*/
class XaoRoot {

    /**
    * XAO XML Namespace Identifier
    *
    * This is used to separate XAO generated XML data from the user's XML data.
    * 
    * @access   public
    * @var      string  
    */
    var $idXaoNamespace = "http://github.com/tezza1971/XAO-PHP/schema/xao_1-0.xsd";
    
    /**
    * XAO XML Namespace Prefix
    *
    * This is an arbitary string whos default is set here. If the users wishes
    * to change it, they need to do so before the DomDoc constructor is called.
    *
    * @access   public
    * @var      string  
    */
    var $strXaoNamespacePrefix = "xao";
    
    var $strDebugData;

    /**
    * All error information is kept here
    *
    * This is usually populated by the class which overrides the $Throw()
    * method. It's worth checking out the way DomDoc overrides this, any class
    * that inherits DomDoc (and there are a lot of them) will obviously use it's
    * version.
    *
    * @access   public
    * @var      array  
    */
    var $strError;

    /**
    * Name of user-defined callback function to handle all errors.
    *
    * The user must then define a member function using the name specified in 
    * this string variable. The function must implement the signature like so
    * function myHardErrorChucker($strMsg,$intLevel,$strCode) {...}
    * assuming they did $this->strErrCallbackFunc = "myHardErrorChucker";
    * in their child class constructor. Suggest checking out the source code for
    * $this->Throw();
    * The idea behind this facility is that you do not need to override Throw()
    * in order to implement custom logging etc.
    * 
    * @access   public
    * @var      string  
    */
    var $fncErrCallbackFunc = "";
    
    /**
    * Paramters used to cache work done by the class.
    *
    * This is an associative array with three different types of potential
    * keys:
    * 1) "key" - the cache key
    * 2) "ttl" - time to live (seconds).
    * 3) "exp" - a unix timestamp when when the object expires
    * To use the cache, "key" is required. In addition, either "ttl" or "exp"
    * need to be populated. This array is passed to the contructor of the
    * CacheMan class for further action. see documentation in CacheMan for more
    * information.
    *
    * @access   public
    * @var      array
    */
    var $arrCacheParams = array();
    
    /**
    * Generic/default error handler.
    *
    * This is an associative array with three different types of potential
    * keys:
    * 1) "key" - the cache key
    * 2) "ttl" - time to live (seconds).
    * 3) "exp" - a unix timestamp when when the object expires
    * To use the cache, "key" is required. In addition, either "ttl" or "exp"
    * need to be populated. This array is passed to the contructor of the
    * CacheMan class for further action. see documentation in CacheMan for more
    * information.
    *
    * @param    string  user-defined error message
    * @param    array   hash list of metadata to provide supportive context
    * @access   public
    * @return   void
    */
    function Throw($strErrMsg,$arrAttribs = null) {
        if(is_null($arrAttribs)) $arrAttribs = array();
                                        // clear any previous errors
        $this->strError = "";
        
        if(
            isset($arrAttribs["class"]) 
            && isset($arrAttribs["function"])
            && isset($arrAttribs["line"])
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
            if(method_exists($this,$this->fncErrCallbackFunc)) {
                $this->$fncErrCallbackFunc($strErrMsg);
            }
        }
    }
    
    function arrSetErrFnc($fcnCurrent,$intLine) {
        $arrErrAttribs["class"] = get_class($this);
        $arrErrAttribs["function"] = $fcnCurrent;
        $arrErrAttribs["line"] = $intLine;
        return $arrErrAttribs;
    }
    
    function blnTestSafeName($strSubject) {
                                        // a multi-line string is not safe
        if(strstr($strSubject,"\n")) return false;
                                        // begining with a digit is not safe
        if(preg_match("/^\d/",$strSubject)) return false;
                                        // non-word characters are not safe
                                        // underscores "_" are allowed
        if(preg_match("/\W/",$strSubject)) return false;
        return true;
    }
}

?>