<?php
/**
* XAO_DomDoc.php
*
* This script provides the class definition for DomDoc. Since the
* DomDoc class provides the basis for XAO, all the requirements checks for XAO
* are done first up in this script. In general, however, all the code in XAO is
* object oriented. For more information on the DomDoc class itself, see the doc
* comment directly preceding the class declaration.
*
* @author       Terence Kearns
* @version      1.0 alpha
* @copyright    Terence Kearns 2003
* @license      Apache License, Version 2.0 (see http://www.apache.org/licenses/LICENSE-2.0 )
* @link         https://github.com/tezza1971/XAO-PHP
* @package      XAO
*/

include_once "XAO_XaoRoot.php";
include_once "XAO_Exceptions.php";
include_once "XAO_DomFactory.php";

define("XAO_DOC_NEW",10);
define("XAO_DOC_READFILE",20);
define("XAO_DOC_REFERENCE",50);
define("XAO_DOC_DATA",60);

class DomDoc extends XaoRoot {

    /** Error helper instance (lazy) */
    public $objErr;

    /** Last/current error element (DOM) */
    public ?DOMElement $ndErr = null;

    /** Native PHP DOMDocument instance */
    public DOMDocument $objDoc;

    /** Document root node */
    public DOMElement $ndRoot;

    /** Instantiation mode */
    private int $_intMode;

    /** Queue of element names -> callback function */
    private array $_arrCustomTagNames = array();

    /** Queue of XPath queries -> callback function */
    private array $_arrCustomTagQueries = array();

    /** PHP 8 constructor; delegates to legacy constructor for BC */
    public function __construct($mxdData, int $intUse = XAO_DOC_NEW) {
        $this->DomDoc($mxdData, $intUse);
    }

    /** Legacy constructor kept for backward compatibility */
    function DomDoc($mxdData,$intUse = XAO_DOC_NEW) {

        $this->_intMode = $intUse;
        if($this->_intMode === XAO_DOC_NEW) {
            $this->objDoc = new DOMDocument('1.0', 'UTF-8');
            $elRoot = $this->objDoc->createElement((string)$mxdData);
            $this->ndRoot = $this->objDoc->appendChild($elRoot);
        }
        elseif($this->_intMode === XAO_DOC_READFILE || $this->_intMode === XAO_DOC_DATA) {
            $objDomFactory = new DomFactory((string)$mxdData);
            if(strlen($objDomFactory->strErrorMsgFull)) {
                $this->_AbortDocument($objDomFactory->strErrorMsgFull);
                die($objDomFactory->strError);
            }
            else {
                $doc = $objDomFactory->objGetObjDoc();
                if (!$doc instanceof DOMDocument) {
                    $this->_AbortDocument("Failed to obtain a DOMDocument from DomFactory.");
                } else {
                    $this->objDoc = $doc;
                }
            }
            $this->ndRoot = $this->objDoc->documentElement;
        }
        elseif($this->_intMode === XAO_DOC_REFERENCE) {
            $this->objDoc = $mxdData;
            $this->ndRoot = $mxdData->documentElement
                ?? $this->_AbortDocument(
                    "The reference document object is not a valid native PHP DOM XML document."
                );
        }
        else {
            $this->_AbortDocument(
                "The second argument to DomDoc constructor is invalid."
            );
        }
    }


    /**
    * Abort document initialisation and instantiate an error document instead.
    *
    * If something goes wrong in the initialisation process, the creation of a
    * document is aborted and a token error document is initialised instead.
    * Ordinarily, the $this->Throw() method is used to raise errors, however
    * if the initialisation process is not complete, then $this->Throw() will
    * not work. This function ensures that a document is always created and
    * then it calls the throw function.
    *
    * @param   string   Error message to be contained in the error root element
    * @return  void
    * @access  private
    */
    function _AbortDocument($strErrMsg) {
        // produce a basic document so that we have enough to throw an error.
        $this->objDoc = new DOMDocument('1.0', 'UTF-8');
        $this->ndRoot = $this->objDoc->createElement('error');
        $this->objDoc->appendChild($this->ndRoot);
        $this->ndRoot->nodeValue = (string)$strErrMsg;
    }

    
    /**
    * Base error logger
    * 
    * All DomDoc based objects should use this method to raise errors. The 
    * method will not stop execution. It will create elements on the DomDoc
    * tree containing all the error data available. It is up to the stylsheet
    * to extract and render error information through an appropriate template.
    * Users should note the ability to define a custom call-back function which
    * may be created as a method in the child object. To do this, populate
    * $this->strErrCallbackFunc with the name of your custom error method.
    * To find out more about how the exception elements are populated, check
    * out the documentation in the Exceptions class.
    * 
    * @param   string  Main error message for display
    * @param   array   A hash of attributes/values to include in error element
    * @return  void
    * @access  public
    */
    public function Throw(string $strErrMsg, ?array $arrAttribs = null): void {
        if (is_null($arrAttribs)) {
            $arrAttribs = [];
        }
        parent::Throw($strErrMsg, $arrAttribs);

        // obtain singleton error object if it
        // does not already exist.
        if (!isset($this->objErr)) {
            // set up the error node to pass to the
            // Exceptions constructor. Ensure that
            // all the contents have a default
            // namespace in XAO
            $ndExceptions = $this->ndAppendToRoot("exceptions");
            if ($ndExceptions instanceof DOMElement) {
                $ndExceptions->setAttribute("xmlns", $this->idXaoNamespace);
            }
            $this->objErr =
                new Exceptions($this->objDoc, $ndExceptions, "exception");
        }

        // the Exceptions class is not much use
        // without populating this.
        $this->objErr->setMessage($this->strError);

        // optional extras go here.
        $this->objErr->setMsgAttribs($arrAttribs);

        // This is where all the action occurs
        // in the Exceptions class. See the Doc
        // comments in that class for details.
        $this->ndErr = $this->objErr->ndCreateError();
    }

        
    /**
     * Serialise and return the entire document object as stand-alone XML.
     *
     * This is used when the entire XML document is required in ASCII format.
     *
     * @return  string  XML document
     * @access  public
     */
    function xmlGetDoc(): string {
        $this->_TestForConstuctor();
        return $this->objDoc->saveXML();
    }

    /**
     * Serialize and return the entire document as an XML fragment.
     *
     * This is used when an ASCII version of the XML document is required
     * _without_ any XML declaration or processing instructions. Everything
     * below and including the root element is serialized.
     *
     * @return string XML fragment
     */
    public function xmlGetFrag(): string
    {
        $this->_TestForConstructor();

        // saveXML on the document element omits XML declaration
        return $this->objDoc->saveXML($this->ndRoot) ?: '';
    }

        
    /**
    * mass storage serialisation
    *
    * This function will dump the ASCII version of this XML document [in it's
    * current state] to a specified file.
    *
    * @param    string  $uriDestination path to destination file
    * @return   void
    * @access   public
    */
    function CommitToFile(string $uriDestination): void {
        $this->_TestForConstuctor();

        $dir = dirname($uriDestination);
        if (!is_dir($dir)) {
            throw new Exception("CommitToFile: directory does not exist: " . $dir);
        }

        $fp = fopen($uriDestination, "w+") or
            throw new Exception("CommitToFile: could not open " . $uriDestination . " for writing");

        flock($fp, LOCK_EX) or
            throw new Exception("CommitToFile: Could not get an exclusive lock on " . $uriDestination . " for writing");

        $bytes = fwrite($fp, $this->xmlGetDoc());
        if ($bytes === false) {
            throw new Exception("CommitToFile: could not write to " . $uriDestination);
        }

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    
    /**
    * fetch a single element node by name
    *
    * A convenience function for fetching a node reference to an element by
    * specifying only its name.
    *
    * @param    string  $strName name of the element whose node is to be returned
    * @param    int     $intIdx index of which node to return (0 for first)
    * @return   DOMNode|null
    * @access   public
    */
    public function &ndGetOneEl(string $strName, int $intIdx = 0): ?DOMNode {
        $this->_TestForConstructor();

        $nodeList = $this->objDoc->getElementsByTagName($strName);
        if ($nodeList instanceof DOMNodeList && $nodeList->length > $intIdx) {
            $node = $nodeList->item($intIdx);
            return $node;
        }

        $null = null;
        return $null;
    }

    
    /**
    * quickly add a new element under the root element.
    *
    * This function is basically a shortcut for the common task of adding a new
    * element with some content under the root element of the document.
    *
    * @param    string  the name of the new element
    * @param    string  the content of the new element
    * @return   node    the newly added element node object
    * @access   public
    */
    /**
    * quickly add a new element under the root element.
    *
    * This function is basically a shortcut for the common task of adding a new
    * element with some content under the root element of the document.
    *
    * @param    string  the name of the new element
    * @param    string  the content of the new element
    * @return   DOMElement|null the newly added element node object
    * @access   public
    */
    public function &ndAppendToRoot(string $strElName, string $strCont = ""): ?DOMElement {
        $this->_TestForConstructor();

        if (!$this->blnTestXmlName($strElName)) {
            throw new Exception(
                "ndAppendToRoot: " . $strElName
                . " Is not a valid element name."
            );
        }

        $elNew = $this->objDoc->createElement($strElName);
        $ndNew = $this->ndRoot->appendChild($elNew);
        $ndNew->nodeValue = $strCont;

        return $ndNew instanceof DOMElement ? $ndNew : null;
    }


    /**
    * quickly add a new element under an exising element node.
    *
    * This function is basically a shortcut for the common task of adding a new
    * element with some content under an existing node of the document.
    *
    * @param    node    a reference to the exisitng element node
    * @param    string  the name of the new element
    * @param    string  the content of the new element
    * @return   node    the newly added element node object
    * @access   public
    */
    /**
    * quickly add a new element under an exising element node.
    *
    * This function is basically a shortcut for the common task of adding a new
    * element with some content under an existing node of the document.
    *
    * @param    DOMElement  a reference to the exisitng element node
    * @param    string  the name of the new element
    * @param    string  the content of the new element
    * @return   DOMElement|null the newly added element node object
    * @access   public
    */
    public function &ndAppendToNode(DOMElement $ndStub, string $strElName, string $strCont = ""): ?DOMElement {
        $this->_TestForConstructor();

        if (!$this->blnTestXmlName($strElName)) {
            throw new Exception(
                "ndAppendToNode: " . $strElName
                . " Is not a valid element name."
            );
        }

        $elNew = $this->objDoc->createElement($strElName);
        $ndNew = $ndStub->appendChild($elNew);
        $ndNew->nodeValue = $strCont;

        return $ndNew instanceof DOMElement ? $ndNew : null;
    }


    /**
    * Import a fragment from a foreign PHP DOM XML document
    *
    * This function will import a fragment from a foreign PHP DOM XML document
    * below the node specified in the first parameter. This function is 
    * especially used by the other Consume methods in this class.
    * At the moment it EXPLOITS the fact that node::replace_node() allows the
    * use of foreign DOM XML objects - this is not in the spec.
    * So this behaviour cannot be relied upon. It's worth noting that there
    * is an xinclude() function which looks like it might be the way to go but
    * documentation is vague http://www.xmlsoft.org/html/libxml-xinclude.html
    * http://www.php.net/manual/en/function.domdocument-xinclude.php
    * in any case, all maintenance for this functionality is centralised at this
    * one point in the XAO api. If neccesary, it may eploy different techniques
    * based on detecting which version of php/domxml is in use. Needless to say
    * that this function is PIVOTAL to the XAO framework concept which uses
    * aggregation to accumulate content through the CONSUME methods.
    *
    * @param    node    the node under which the fragment is to be grafted
    * @param    node    foreign node containing the fragment to be imported
    * @return   node    the newly added element node object
    * @access   public
    */
    public function &ndImportChildFrag(DOMNode $ndStub, DOMNode $ndNew): ?DOMNode {
        $this->_TestForConstructor();

        if (!$this->blnTestElementNode($ndStub)) {
            throw new Exception(
                "ndImportChildFrag: First argument is not a valid element node.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)
            );
        }

        if (!$this->blnTestElementNode($ndNew)) {
            throw new Exception(
                "ndImportChildFrag: Second argument is not a valid element node.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)  
            );
        }

        $ndTmp = $this->objDoc->createElement("tmp");
        $ndTmp = $ndStub->appendChild($ndTmp);
        $ndTmp->replaceChild($ndNew, $ndTmp);

        return $ndNew;
    }


    /**
    * Import a foreign PHP DOM XML document and append it below $this->ndRoot
    *
    * This function will consume the contents of an entire DOM document and
    * retain it below the root node of this DomDoc.
    *
    * @param    DomDoc  a reference to an exising PHP DOM XML document
    * @param    node    an optional stub node to which the new data is grafted
    * @access   public
    */
    function ndConsumeDoc(DomDoc $objDoc, ?DOMNode $ndStub = null): ?DOMNode {
        $this->_TestForConstructor();

        if (!$objDoc instanceof DomDoc) {
            throw new Exception(
                "ndConsumeDoc: No DomDoc object given", 
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)
            );
        }

        if (!isset($objDoc->ndRoot)) {
            throw new Exception(
                "ndConsumeDoc: No root node. First param must be an XAO "
                . "DomDoc, not just a basic PHP DOMXML object. Use the "
                . "DomFactory class if you need to convert an existing PHP "
                . "DOMXML object.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)  
            );
        }

        if (!$this->blnTestElementNode($ndStub)) {
            $ndStub = $this->ndRoot;
        }

        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    /**
    * Import an XML document from a file and append it below $this->ndRoot  
    *
    * This function will consume the contents of an entire XML document from a
    * file and retain it below the root node of this DomDoc.
    *
    * @param string $uri the location of the XML file
    */
    function ndConsumeFile(string $uri, ?DOMNode $ndStub = null): ?DOMNode
    {
        // If there are any parse errors, then
        // they will be included in the object
        // returned by DomDoc. It's up to the
        // stylesheet to extract them.
        $objDoc = new DomDoc($uri, XAO_DOC_READFILE);
        
        // The new DomDoc is inevitably grafted
        // on to this DomDoc - errors and all.
        if (!$this->blnTestElementNode($ndStub)) {
            $ndStub = $this->ndRoot;
        }

        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    /**
    * Import well-balanced XML data to append below $this->ndRoot
    *
    * This function will consume the contents of some XML data after wrapping
    * it in a root element whose name is specified in the second parameter. The  
    * content is then retained under $this->ndRoot
    *
    * @param string $xml Miscellaneous XML data 
    * @param string $strRoot The name of the root element
    */
    function ndConsumeFragData(string $str, string $strRoot, ?DOMNode $ndStub = null): ?DOMNode
    {
        $this->_TestForConstructor();
        
        if (!$this->blnTestXmlName($strRoot)) {
            throw new Exception(
                "ndConsumeFragData: " . $strRoot
                . " is an invalid name for root element.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)
            );
        }

        // wrap the fragment data in a basic
        // XML envelope
        $str = "<?xml version=\"1.0\"?>\n<" . $strRoot . ">"
            . $str . "</" . $strRoot . ">";

        // If there are any parse errors, then
        // they will be included in the object
        // returned by DomDoc. It's up to the
        // stylesheet to extract them.
        $objDoc = new DomDoc($str, XAO_DOC_DATA);

        // The new DomDoc is inevitably grafted
        // on to this DomDoc - errors and all.
        if (!$this->blnTestElementNode($ndStub)) {
            $ndStub = $this->ndRoot;
        }

        return $this->ndImportChildFrag($ndStub, $objDoc->ndRoot);
    }

    
    /**
    * Import well-balenced XML data to append below $this->ndRoot
    *
    * This function will consume the contents of an XML document.The
    * content is then retained under $this->ndRoot
    *
    * @param    xml     Miscellaneous XML data
    * @access   public
    */
    function ConsumeDocData($str) {
        $objDoc = new DomDoc($str,XAO_DOC_DATA);
        $this->ndImportChildFrag($this->ndRoot,$objDoc->ndRoot);
    }

    /**
    * Test to see if the DomDoc constructor has been run
    *
    * This needs to be done for the sake of developers who can't figure out why
    * their script dies when inheriting from DomDoc. If $this->DomDoc is 
    * not executed somewhere before one of the other methods on this class is 
    * called, then most of them won't work - including $this->Throw()!!!!!! 
    * This function is designed to check that and broadcast a dirty great
    * message announcing the fact. It's a bit of a hack but it's provided for
    * "extra" safety which should make life easier for the absent-minded
    * developer.
    *
    * @access   private
    * @return   void
    */

    // Keep the original misspelled method name for backward compatibility
    function _TestForConstuctor() {
        return $this->_TestForConstructor();
    }

    // Preferred correctly-spelled method name
    function _TestForConstructor() {
        // The existance of $this->objDoc is
        // garenteed. Even if the constructor
        // fails to initialise one, then
        // $this->_AbortDocument should be
        // called which provides a surrogate.
        if(!is_object($this->objDoc)) {
            $strThis = "DomDoc";
            // try to find out the names of classes
            // used to inherit DomDoc and use this
            // information to produce a [hopefully]
            // helpful warning.
            $strParent = get_parent_class($this);
            $strYoungest = get_class($this);
            $msg = "
            <h1>MASSAGE FOR THE PROGRAMMER: {$strThis} constructor not called!</h1>
            <p>You are trying to access methods on {$strThis} without running
            {$strThis}->DomDoc()</p>
            <p>The immediate parent to {$strThis} is {$strParent} . You probably
            need to call {$strThis}->DomDOc() in it's constructor. PHP
            does not automatically call the constructor of the superclass
            in a sub class's constructor.</p>
            ";
            if($strParent != $strYoungest) {
            $msg .= "
                <p>If you already called {$strThis}->DomDOc() from the
                constructor in {$strParent}, then you probably didn't call the
                constructor for {$strParent} in {$strYoungest}. Assuming that
                {$strYoungest} is indeed a child of {$strParent}.</p>
                <p>You're getting this ugly message because {$strThis}
                cannot handle exceptions nicely if it is not instantiated
                properly.</p>
                <p>Below is a debug_backtrace() which should help trace
                where the problem (method call) originated from.</p>
            ";
            }
            $arr = debug_backtrace();
            echo $msg."<pre>";
            var_dump($arr);
            echo("</pre>");
            die("<h3>Script execution terminated.</h3>");
        }
    }

    
    
    /**
    * Turn an associative array into attributes
    *
    * The hash keys are used for the attribute names and the values are used
    * for the attribute values.
    *
    * @param    node
    * @param    array
    * @access   public
    */
    function Arr2Atts(DOMElement $ndEl, array $arrAttribs): bool 
    {
        foreach($arrAttribs as $strName => $strValue) {
            $ndAttrib = $ndEl->setAttribute($strName, $strValue);
            if(!$ndAttrib instanceof DOMAttr) {
                throw new DOMException("Could not set attribute using NAME(\"{$strName}\") and VALUE(\"{$strValue}\").");
            }
        }
        return true;
    }

    /**
    * Use an XPath to nominate nodes for processing by a call-back function.
    *
    * This functionality is dubious when using namespaces. The experimental
    * nature of PHP's DOMXML extension makes it impossible to guarentee safe
    * usage.
    *
    * @param    string  XPath query
    * @param    string  name of user-defined callback function  
    */
    function SetCustomTagQuery(string $strQuery, string $fncName): void
    {
        if(method_exists($this, $fncName)) {
            $this->_arrCustomTagQueries[] = [$strQuery, $fncName];
        } else {
            throw new BadMethodCallException("Method {$fncName} is undefined.");
        }
    }
    
    /**
    * Process all nodes (domelements) due for processing.
    *
    * When the user has finished nominating all the nodes for processing using
    * either SetCustomTagName() or SetCustomTagQuery(), then this function can
    * be called. It's a good idea to make sure this is only called as many times
    * as it needs to be (once).
    *
    * @access   public
    */
    function ProcessCustomTags() {
        // process all tag-name call-backs
        foreach($this->_arrCustomTagNames as $elName => $fncName) {
            $arrNd = $this->objDoc->getElementsByTagName($elName);
            if(is_array($arrNd)) {
                foreach ($arrNd as $nd) {
                    $this->$fncName($nd);
                }
            } else {
                throw new Exception(
                    "ProcessCustomTags: there was an error searching for "
                    . $elName . " in the document."
                );
            }
        }
        
        // process all xpath query call-backs
        foreach($this->_arrCustomTagQueries as $arrQryFunc) {
            $strQry = $arrQryFunc[0];
            $fncName = $arrQryFunc[1];
            
            $arrNd = $this->arrNdXPath($strQry);

            if (is_array($arrNd)) {
                foreach ($arrNd as $nd) {
                    $this->$fncName($nd);
                }
            } else {
                throw new Exception(
                    "XPath query {$strQry} did not work. Unfortunately, the "
                    . "underlying DOMXML function does not provide error "
                    . "information. Sorry."
                );
            }
        }
    }

    
    /**
     * Return a list of nodes resulting from an XPath Query
     *
     * This function runs the XPath query and returns an array of nodes matching
     * the results. Unfortunately, xpath_eval() never divulges any error
     * information. I assume that $objRes->nodeset holds a false value if the
     * query errored.
     *
     * @param  string $strExpr The XPath query  
     * @return array|false Nodes matching the query or false on error
     */
    public function arrNdXPath(string $strExpr)
    {
        $xpath = new DOMXPath($this->objDoc);
        $nodeList = $xpath->query($strExpr);

        if ($nodeList === false || $nodeList->length === 0) {
            return false;
        }

        // Convert DOMNodeList to array for backward compatibility
        $nodes = array();
        foreach ($nodeList as $node) {
            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * Test if the supplied node is on object of type "domelement"
     *
     * This function is useful for testing variables that need to be accessed as
     * domelement objects.
     *
     * @param  mixed $ndEl The node to test
     * @return bool True if domelement, false otherwise
     */  
    public function blnTestElementNode($ndEl): bool
    {
        return $ndEl instanceof DOMElement;
    }

    /**
     * Do a reliable test for a valid element name
     *
     * This function tries to create an element using the supplied name. If it
     * fails, then the name is assumed to be invalid.
     *
     * @param  string $strName The name to test
     * @return bool True if valid element name, false otherwise
     */
    public function blnTestXmlName(string $strName): bool 
    {
        return $this->objDoc->createElement($strName) !== null;
    }

} // END CLASS
?>
