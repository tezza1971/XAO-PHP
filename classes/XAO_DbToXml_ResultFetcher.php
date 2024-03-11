<?php
/**
* Import the basic XaoRoot class for inheritance.
* 
* This class is based on XaoRoot and hence supports all of it's functionality,
* including it's ability to be consumed by another DomDoc based object.
* 
* @import XaoRoot
*/
include_once "XAO_XaoRoot.php";

/**
* Results fetcher for various Database Results classes
*
* There are a number of class libraries for querying databases. This class is
* designed to provide wrappers to most of those with the express purpose of
* getting an array of associative arrays. It's this 2D array that DbToXml needs
* to produce XML. This class is only instantiated if such an array is not passed
* to the DbToXml constructor in the first place. This class first checks to see
* which (if any) type of DbResult class is being used, then it calls a dedicated 
* method to extract the 2D array. Note that we choose not to support native
* database result resources since we are not developing/supporting yet another 
* database abstraction layer library. This is a just a little
* icing on te cake to eliminate the extra steps needed for obtaining, checking
* for errors, and formatting the actual data into an array of associative 
* arrays. It means that users of database abstraction layer classes need not
* concern themselves with the format that DbToXml expects.
*
* @author       Terence Kearns
* @version      1.0
* @copyright    Terence Kearns 2003
* @license      LGPL
* @package      XAO
* @link         http://xao-php.sourceforge.net
*/
class ResultFetcher extends XaoRoot {
    
    var $arr2D = array();
    
    var $objResult;
    
    function ResultFetcher(&$objResult) {
        if(is_object($objResult)) {
            $this->objResult =& $objResult;
        }
        else {
            $this->Throw(
                "ResultFetcher: Only [result] objects are supported."
                ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
            return;
        }
        
                                        // PEAR DB
        if(is_a($objResult,"DB_Result")) {
            $this->MkPearResult();
        }
        // insert more elseif() tests (more supported objects)
        else {
            $this->Throw(
                "ResultFetcher: The DB result object you passed to DbToXml "
                ."returned an error:\n".$objDBResult->getMessage() 
                //.":\n".$objDBResult->getUserinfo()
                ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
        }
    }
    
    function &arrGetResult() {
        if(is_array($this->arr2D)) return $this->arr2D;
        return array();
    }
    
    function MkPearResult() {
        if(DB::isError($this->objResult)) {
            $this->Throw(
                "ResultFetcher: The DB result object you passed to DbToXml "
                ."returned an error:\n".$this->objResult->getMessage() 
                //.":\n".$objDBResult->getUserinfo() // security issue
                ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
        }
        else {
                                        // fetch the result data (list of rows) 
                                        // into an associative array.
            while($this->arr2D[] = $this->objResult->fetchRow(DB_FETCHMODE_ASSOC));
            array_pop($this->arr2D);
        }
    }
}

?>