<?php
/**
* XAO_DomFactory.php
*
* This script provides the class definition for DomFactory - a class which is
* used to safely return DOM XML objects.
*
* @author       Terence Kearns
* @version      0.2
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @link         https://github.com/tezza1971/XAO-PHP
* @package      XAO
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
* Import the text debugging facility.
* 
* This class provides an HTML output facility for displaying a portion of text
* with one line highlighted. It is used by DomFactory when it throws
* an exception.
* 
* @import TextDebugger
*/
include_once "XAO_TextDebugger.php";

/**
* Quick XML Parser and Debugger
*
* This main job of this class is to test for XML well-formedness. Secondly, it
* is to provide highly useful debugging output in the event that the input text
* fails the wellformedness test.
*
* @package      XAO
*/
class DomFactory extends XaoRoot {
    
    var $objDoc;
    
    var $intErrorLine;
    
    var $uriContextFile;
    
    var $strErrorMsg;
    
    var $strErrorMsgFull;
    
    function DomFactory($strTarget) {
        if(strstr($strTarget,"\n") === false) {
            if(file_exists($strTarget)) {
                    $this->objDoc = $this->_objDomParseFile($strTarget);
                    return;
            }
        }
        $this->objDoc =& $this->_objDomParseData($strTarget);
    }
    
    function &objGetObjDoc() {
        return $this->objDoc;
    }

    function &_objDomParseFile($uriSrc) {
                                            // assume that the file does not exist
            $this->uriContextFile = null;
        if(file_exists($uriSrc)) {
                                            // populate $this->uriContextFile for
                                            // use later in _objDomParseData()
                $this->uriContextFile = $uriSrc;
                                            // try to keep all file access thread-
                                            // safe when obtaining the content.
                                            // This is the main reason we don't use
                                            // domxml_open_file() - it does not
                                            // participate in flock() co-operative
                                            // locking.
            $fp = fopen($uriSrc,"r")
                OR $this->Throw(
                    "\nCould not open ".$uriSrc,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            flock($fp,LOCK_SH)
                OR $this->Throw(
                    "\nCould not get a shared lock on ".$uriSrc.".",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
                $strFileData = fread($fp,filesize($uriSrc));
            flock($fp,LOCK_UN);
            fclose($fp);
                                        // We now have the content. Parse it.
                                        // And exit the fuction.
            return $this->_objDomParseData($strFileData);
        }
        else {
            $this->Throw(
                "\nFile (".$uriSrc.") not found.",
                $this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
        }
        return false;
    }
    
    function &_objDomParseData($strSrc) {
                                            // Attempt a new DOM object using 
                                            // supplied data. Suppress errors.
       $objDoc = @domxml_open_mem($strSrc);
                                        // test for success. if parse fails, we 
                                        // are obligated to produce error 
                                        // information.
       if(!is_object($objDoc)) {
                                            
            $strFile = "";
            if(strlen($this->uriContextFile)) 
                $strFile = " in file ".$this->uriContextFile." ";
                                            // We only go to the bother of 
                                            // performing another [sax] parse if
                                            // we failed the initial DOM parse.
            if($this->blnSaxParse($strSrc)) {
                                            // DOM parse failed, SAX parse succeded.
                                            // We need DOM parsing to succeed.
                                            // Throw appropriate error.
                $this->Throw(
                    "The XML data ".$strFile
                    ."was parsed by PHP's XML parser but not by "
                    ."PHP's DOM XML domxml_open_mem() method. No details of the "
                    ."error can be extracted from domxml_open_mem(). Sorry.",
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                                            // While we expected the SAX parse to
                                            // fail also, it did the job of
                                            // providing the error information that
                                            // we could not extract from DOM
                $this->Throw(
                    $this->strErrorMsgFull.$this->strDebugData,
                    $this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            return false;
        }
        return $objDoc;
    }
    
    function blnSaxParse($strData) {
        $xp = xml_parser_create();
        //xml_set_object($xp, $this);
        $xpRes = xml_parse($xp,$strData);
        if(!$xpRes) {
            $this->strErrorMsg = xml_error_string(xml_get_error_code($xp));
            $this->intErrorLine = xml_get_current_line_number($xp);
            
            $this->strErrorMsgFull = "The following parse error occured";
            if($this->intErrorLine !== false) {
                $this->strErrorMsgFull .= 
                    " on or near line ".$this->intErrorLine;
            }
            if(strlen($this->uriContextFile)) {
                $this->strErrorMsgFull .= 
                    " in the file ".$this->uriContextFile;
            }
            $this->strErrorMsgFull .= ":\n ".$this->strErrorMsg."\n";
            
            if(is_int($this->intErrorLine)) {
                $objDebugData =& TextDebugger(
                    $strData,
                    $this->intErrorLine
                );
                $this->strDebugData = $objDebugData->strGetHtml();
            }
        }
        xml_parser_free($xp);
        return $xpRes;
    }

    function Throw($strErrMsg,$arrErrAttribs) {
        if($this->intErrorLine) $arrErrAttribs["line"] = $this->intErrorLine;
        parent::Throw($strErrMsg,$arrErrAttribs);
    }
}

?>