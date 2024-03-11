<?php
/**
* XAO_DbToXml.php
*
* This script is pivotal to developers who wish to retrieve content from a 
* relational database. It's primary function is to convert tabular results sets
* to XML format. It does more than that however, see the class doc comments and
* tutorials for information on how to leverage it's awsome power.
*
* @author       Terence Kearns
* @version      1.0 alpha
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @package      XAO
* @link         https://github.com/tezza1971/XAO-PHP
*/

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
* Import the result fetcher helper object.
* 
* This class is dedicated to fetching the appropriate 2D array from query
* result objects (of which there are quite a few). This is a just a little
* icing on te cake to eliminate the extra steps needed for obtaining, checking
* for errors, and formatting the actual data into an array of associative 
* arrays. It means that users of database abstraction layer classes need not
* concern themselves with the format that DbToXml expects.
* 
* @import ResultFetcher
*/
include_once "XAO_DbToXml_ResultFetcher.php";

/**
* Convert a result table from an SQL query into basic XML.
*
* Very simple conversion of SQL query results table to XML data which may then
* be transformed into a suitable structure. See the <b>basics/dbAccess></b> 
* tutorial for more information on usage. Optionally more complext usage can
* include the use of callback functions through the optional arguments to the 
* constructor. See the constructor method documentation for details. This class
* extends the DomDoc content class and therefore inherits all DomDoc 
* capabilities.
*
* @package      XAO
*/

class DbToXml extends DomDoc {
    
    /**
    * The stub node onto which the result nodes will be grafted.
    * 
    * This is specified in the constructor (if used) where it is employed by 
    * the execute() method as the place to append the row nodes.
    * Unless a stub node is specified in the constructor, it will be
    * assigned the instance $this->ndRoot (the root/result node).
    *
    * @access   public
    * @var      node  
    * @see      DomDoc::ndRoot
    */
    var $ndStub;

    /**
    * The name of the result element (root element of result set).
    *
    * @access   public
    * @var      string  
    */
    var $strResEl               = "result";
    
    /**
    * The name of each row element.
    *
    * @access   public
    * @var      string  
    */
    var $strRowEl               = "row";
    
    /**
    * 2D array containing the rows and colums of the result returned from the 
    * passed DB result object.
    *
    * @access   public
    * @var      object  
    */
    var $arrResult              = array();

    /**
    * Field-name/Element-name function mapping.
    *
    * This member is populated by the optional second argument to the 
    * constructor function. It is an associative array with a list of columns
    * (the array keys) and their associated handlers/call-back-functions (the
    * array values).
    *
    * @access   public
    * @var      array  
    */
    var $arrCallBacks;
    
    /**
    * List of columns which the result tree is to be grouped into
    *
    * This is an ordered array of column names which are to be used to group 
    * (nest) the XML output element. This class attribute is populated by an
    * argument to the $this->GroupBy() method.
    *
    * @access   public
    * @var      array  
    */
    var $arrGroupByCols = array();
    
    /**
    * Constructor method 
    *
    * Perform requirements checks and initialised resources.
    *
    * @param    object  An instance of a PEAR DB result object or a 2d
    *                   associative array.
    * @param    array   An associative array mapping result column names to
    *                   methods (call back functions) of the class.
    * @param    object  a reference to an existing DOM document (cannot use &)
    * @param    object  a DOM node in the referenced document where the results
    *                   from this calss will be appended.
    * @return   void
    */
    
    function DbToXml(&$mxdResult,$arrCallBacks = array(),$objDocRef = null,$ndStub = null) {
        $this->arrCallBacks = $arrCallBacks;
        foreach($this->arrCallBacks AS $fncName) {
        	    if(!method_exists($this,$fncName)) {
                $this->Throw(
                    "DbToXml: Call-back function $fncName does not exist."
                    ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
                return;
        	    }
        }
                                        // run the parent constructor
        if(is_object($objDocRef) && is_object($ndStub)) {
                                        // check to see if this tree is being
                                        // grafted onto an existing node.
            $this->DomDoc($objDocRef,XAO_DOC_REFERENCE);
            $this->ndStub = $ndStub;
        }
        else {
            $this->DomDoc($this->strResEl);
            $this->ndStub = $this->ndRoot;
        }
                                        // populate the 2d associative array 
                                        // that will be used to build our XML
        if(is_array($mxdResult)) {
            $this->arrResult = $mxdResult;
        }
        elseif(is_object($mxdResult)) {
            $objFetcher = new ResultFetcher($mxdResult);
            if(strlen($objFetcher->strError)) {
                $this->Throw(
                    "DbToXml: ".$objFetcher->strError
                    ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
            }
            else {
                $this->arrResult =& $objFetcher->arrGetResult();
            }
        }
        else {
            $this->Throw(
                "DbToXml: Constructor expects a 2D array (or a supported result 
                object)."
                ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
        }
    }
    
    /**
    * Set the name for the results tag.
    *
    * @param    string  Name to use for the root result tag
    * @return   void
    * @access   public
    */
    function SetResultTagName($strName) {
        if(strlen($strName)) {
            $this->strResEl = $strName;
        }
    }

    /**
    * Set the name for all the row tags.
    *
    * @param    string  Name to use for the root result tag
    * @return   void
    * @access   public
    */
    function SetRowTagName($strName) {
        if(strlen($strName)) {
            $this->strRowEl = $strName;
        }
    }
    
    /**
    * Convert the RDBMS data into XML elements.
    *
    * Iterate over $this->arrResult to first obtain the rows and then the
    * fields which are created and appended as elements using DOM XML methods.
    *
    * @return   void
    * @access   public
    */
    function Execute() {
    	    if(!is_array($this->arrResult)) {
            $this->Throw(
                "DbToXml: For some reason, the result set is not formatted 
                [as an] array." ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
            );
            return;
    	    }
    	    
    	    if(is_object($this->ndStub)) {
    	    	    if(!$this->blnTestElementNode($this->ndStub)) {
    	    	    	    $this->Throw(
                    "DbToXml: The stub node is not a valid domelement."
                    ,$this->arrSetErrFnc(__FUNCTION__,__LINE__)
                );
                return;
    	    	    }
    	    }
                                        // iterate through the result list
        foreach($this->arrResult AS $arrRow) {
                                        // add a row element for each row in the 
                                        // result list
            $elRow = $this->objDoc->create_element($this->strRowEl);
            $ndRow = $this->ndStub->append_child($elRow);
            $this->RowConstructor($ndRow);
                                        // iterate through the fields in the row
            foreach($arrRow AS $fieldName => $fieldVal) {
                                        // DON'T CREATE EMPTY TAGS!
                if(strlen($fieldVal) && !is_int($fieldName)) {
                                        // add an element for each non-empty field
                    $elField = $this->objDoc->create_element($fieldName);
                    $ndField = $ndRow->append_child($elField);
                                        // CHECK FOR CALLBACKS
                    if(isset($this->arrCallBacks[$fieldName])) {
                        $funcName = $this->arrCallBacks[$fieldName];
                        $this->$funcName($this->objDoc,$ndField,$fieldVal);
                    }
                    else {
                        $ndField->set_content($fieldVal);
                    }
                }
            }
            $this->RowDestructor($ndRow);
        }
    }
    
    
    
    /**
    * Group records in the output tree by element corresponding to column.
    *
    * This is a very handy way to quickly group output elements by a selected
    * element.
    *
    * @param    mixed column(s) to use for grouping
    */
    function GroupBy($mxdCols) {
    	    if(is_array($mxdCols))  $this->arrGroupByCols = $mxdCols;
    	    if(is_string($mxdCols)) $this->arrGroupByCols[] = $mxdCols;
        // to do
    }
    
    /**
    * Clean up function to flush existing data.
    *
    * Calling this function is needed if the constructor is to be called more
    * than once.
    *
    * @return   void
    */
    function Reset() {
        $this->arrCallBacks=null;
        $this->arrResult=array();
        $this->ndStub=null;
        // now the constructor will need to be called again before this
        // object instance can be used further.
    }

    /**
    * Row Contructor
    *
    * This abstract method may be overridden by the child class in order to
    * perform an operation just _prior_ to the execution of a row in the 
    * execute() method of this class
    *
    * @param    node    a reference to the element node representing a row
    * @access   public
    * @return   void
    */
    function RowConstructor(&$ndRow){}

    /**
    * Row Destructor
    *
    * This abstract method may be overwritten by the child class in order to
    * perform an operation just _after_ the execution of a row in the 
    * execute() method of this class
    *
    * @param    node    a reference to the element node representing a row
    * @access   public
    * @return   void
    */
    function RowDestructor(&$ndRow){}

    /**
    * unix timestamp date call-back mutator function
    *
    * This call-back function is placed here for convenience and can be used to
    * produce an element with a more convenient schema for representing a date
    * from a unix timestamp. It also provides a useful example of the call-back
    * capability of this class. What it does is add a set of attributes to the
    * element in context which each represent a conventional date component.
    *
    * @param    object  a reference to the current PHP DOM XML object instance
    * @param    node    a reference to the element node representing a field
    * @param    string  a copy of the text content that would normally be 
    *                   assigned to this field elemeent.
    * @access   public
    * @return   void
    */
    function unixTsToReadable(&$ndField,$intTs) {
        $intTs = (integer)$intTs;
        if($intTs < 0) return;
        $ndField->set_attribute("unixTS",$intTs);
        $ndField->set_attribute("ODBCformat",date("Y-m-d H:i:s",$intTs));
        $ndField->set_attribute("year",date("Y",$intTs));
        $ndField->set_attribute("month",date("m",$intTs));
        $ndField->set_attribute("day",date("d",$intTs));
        $ndField->set_attribute("hour",date("H",$intTs));
        $ndField->set_attribute("min",date("i",$intTs));
    }

    /**
    * ODBC timestamp date call-back mutator function
    *
    * This call-back function is placed here for convenience and can be used to
    * produce create a unix timestamp from an ODBC compliant timestamp. This 
    * unix timestap is then sent to $this->unixTsToReadable where nice
    * attributes are added :)
    *
    * @param    object  a reference to the current PHP DOM XML object instance
    * @param    node    a reference to the element node representing a field
    * @param    string  a copy of the text content that would normally be 
    *                   assigned to this field elemeent.
    * @access   public
    * @return   void
    */
    function odbcToReadable(&$ndField,$odbcTs) {
        if(trim($odbcTs) == "") return;
        // example of MS SQL select snippet producing expected format.
        // CAST(DATEPART(yyyy,myDate) AS varchar(64))    + '-' +
        // CAST(DATEPART(mm,myDate) AS varchar(64))      + '-' +
        // CAST(DATEPART(dd,myDate) AS varchar(64))      + ' ' +
        // CAST(DATEPART(hh,myDate) AS varchar(64))      + ':' +
        // CAST(DATEPART(n,myDate) AS varchar(64)) AS myODBCDate,
        $arrTs = explode(" ",$odbcTs);
        $arrDate = explode("-",$arrTs[0]);
        $arrTime = explode(":",$arrTs[1]);
        $year   = $arrDate[0];
        $month  = $arrDate[1];
        $day    = $arrDate[2];
        $hour   = $arrTime[0];
        $min    = $arrTime[1];
        if($year != 1900) {
            $year = date("Y");
            $unixTs = mktime($hour,$min,0,$month,$day,$year);
            if($unixTs != -1) $this->unixTsToReadable($ndField,$unixTs);
            return $odbcTs;
        }
    }
}

?>