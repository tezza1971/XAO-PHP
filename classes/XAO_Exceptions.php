<?php
/**
* XAO_Exceptions.php
* 
* This script provides exception-specific messaging for content based classes.
* It is designed to be used in conjunction with DomDoc based classes. There is
* no need for the developer to concern themselves with this class since DomDoc
* uses it in DomDoc::Throw(). However, it is possible to
* use this class from anywhere - even outside XAO. It is well encapsulated and
* decoupled.
*
* @see      DomDoc::Throw()
*/

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
* Utility for managing massages relating to exceptional conditions (errors)
*
* This class is used to produce and manage error messages for exceptional 
* conditions as XML data in a DomDoc based object. It also stores debugging 
* data wich may optionally be displayed by an appropriate stylesheet. All data
* is managed directly on the DOM tree (rather than maintaining a dedicated 
* stack) of the calling object to ensure it's availability without having to 
* notify a separate stack manager when the application needs the data.
* As such, this class requires a stub (node) on the DOM tree represending the
* "errors" element where it will mainatain error data.
*
* @author       Terence Kearns
* @version      0.0
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @package      XAO
* @link         https://github.com/tezza1971/XAO-PHP
*/
class Exceptions extends XaoRoot {
    
    var $objDoc;
    var $ndErrors;
    
    var $blnCreateStackTrace = true;

    /**
    * XML Namespace Prefix
    *
    * Element name to use for each exception.
    * 
    * @access   public
    * @var      string  
    */
    var $strElName;
    
    /**
    * Hash list of metadata to provide supportive context for error messages
    *
    * This is intended to be used by methods that override $this->Throw().
    *
    * @access   public
    * @var      array  
    */
    var $arrErrAttribs = array();

    /**
    * Exceptions constructor
    * 
    * Simply obtain references to the calling document and keep them locally 
    * (global to the class).
    * 
    * @param   dom ref     document where the error data is to be grafted.
    * @param   node ref    the stub onto which the error grafted
    * @return  void
    * @access  public
    */
    function Exceptions(
        &$objDoc,
        &$ndErrors,
        $strElName = "exception", 
        $idNamespace = ""
    ) {
        $this->objDoc             =& $objDoc;
        $this->ndErrors           =& $ndErrors;
        $this->strElName          = $strElName;
    }
    
    /**
    * Exceptional message setter method
    * 
    * @param   string     current message for current exceptional condition
    * @return  void
    * @access  public
    */
    function SetMessage($strMsg) {
        $strMsg = trim($strMsg);
        if(strlen($strMsg)) {
            $this->strError = $strMsg;
        }
        else {
            $this->strError = 
                "Exceptions::SetMessage - The error message was empty.";
        }
    }
    
    /**
    * Exceptional message setter method
    *
    * This method resets the current array of error message attributes to the
    * associative array passed in the first argument. The array is NOT
    * appended.
    * 
    * @param   array     copy associative array of error message attributes
    * @return  void
    * @access  public
    */
    function SetMsgAttribs($arrAttribs) {
        $this->arrErrAttribs = $arrAttribs;
    }
    
    /**
    * General purpose attribute setting utility
    * 
    * @param   string     name of the attribute to be set
    * @param   string     value of the attribute to be set
    * @return  void
    * @access  private
    */
    function SetMsgAttrib($strAttName,$strAttVal) {
        $strAttVal = trim($strAttVal);
        if(strlen($strAttVal)) $this->arrErrAttribs[$strAttName] = $strAttVal;
    }
    
    /**
    * Execution method for created the actual error data
    * 
    * @return  node    A reference to the new DOM node created.
    * @access  public
    */
    function &ndCreateError() {
                                        // create the document node for this 
                                        // error
        $elError =& $this->objDoc->create_element($this->strElName);
        $ndError =& $this->ndErrors->append_child($elError);
        $elMsg   =& $this->objDoc->create_element("msg");
        $ndMsg   =& $ndError->append_child($elMsg);
                                        // populate it with the main message        
        if(strlen($this->strError)) {
            $ndMsg->set_content($this->strError);
        }
        else {
            $strMsg = 
                "Exceptions::ndCreateError - No error message has been set.";
            $ndMsg->set_content($strMsg);
        }
                                        // set up any attributes
        foreach($this->arrErrAttribs AS $attName => $attVal) {
            if(strlen($this->arrErrAttribs[$attName])) {
                $ndError->set_attribute($attName,$attVal);
            }
        }
                                        // include stack trace if required
        if($this->blnCreateStackTrace) $this->_CreateStackTrace($ndError);
        
        return $ndError;
    }
    
    /**
    * Method for creating verbose stack trace data on DOM tree
    * 
    * @return  node    A reference to the error DOM node.
    * @return  void
    * @access  public
    */
    function _CreateStackTrace(&$ndErr) {
        $elBt =& $this->objDoc->create_element("stack");
        $ndBt =& $ndErr->append_child($elBt);
        $arrBt = debug_backtrace();
                                    // this will create a nested call
                                    // element for each function call stored 
                                    // in the backtrace array.
        foreach($arrBt AS $arrCall) {
            $elCall =& $this->objDoc->create_element("call");
            foreach($arrCall AS $attCall=>$valCall) {
                if(is_array($valCall)) {
                    if(count($valCall)) {
                        $elArgs =& $this->objDoc->create_element($attCall);
                        $ndArgs =& $elCall->append_child($elArgs);
                        foreach($valCall AS $attArg=>$valArg) {
                            if(!is_string($valArg))
                                    // delve no further
                                $valArg = "[".@(string)$valArg."]";
                            if(strlen(trim($valArg)) && $valArg != "[]") {
                                    // attempt to show content of argument
                                $elItem = 
                                    $this->objDoc->create_element("item");
                                if(strlen($valArg) > 256)
                                    $valArg = substr($valArg,0,255)."...";
                                $elItem->set_content($valArg);
                                $ndItem =& $ndArgs->append_child($elItem);
                            }
                        } // end foreach on call arguments
                    }
                }
                else {
                                    // translate backtrace array keys/vals
                                    // to atribute name/vals for each CALL
                                    // element
                    $elCall->set_attribute($attCall,$valCall);
                }
            } // end foreach on call attributes
                                    // attache the CALL element to the
                                    // stack element.
            if(isset($ndCall)) {
                                    // nest this element under the previous
                                    // CALL element if one already exists
                $ndCall =& $ndCall->append_child($elCall);
            }
            else {
                                    // create the start CALL element node.
                $ndCall =& $ndBt->append_child($elCall);
            }
        } // end backtrace foreach on calls
    }
}
?>