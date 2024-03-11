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


/**
* Import the root (base) XAO class.
* 
* All classes in the XAO library should inherit this class. See documentation on
* the class itself for more details.
*
* @import XaoRoot
*/
include_once "XAO_XaoRoot.php";

/**
* Import the Exceptions utility class.
* 
* This is only instantiated if $this->Throw() is called. This object 
* encapsulates error data management. It keeps all the error data on the
* referenced DOM doc rather than an internal stack.
*
* @import Exceptions
*/
include_once "XAO_Exceptions.php";

/**
* Import Dom Factory for parsing/obtaining DOM objects.
* 
* This is a general purpose class providing comprehensive parsing options when
* obtaining DOM object. Using this class provides centralised DOM object 
* management across the whole library.
*
* @import Exceptions
*/
include_once "XAO_DomFactory.php";

/**
* New dom document from from scratch
* 
* This constant represents a mode of the DomDoc which causes it to create a
* new document on instatiation - using the starter as the name of the root
* element for the new document.
*/
define("XAO_DOC_NEW",10);

/**
* Dom document from local file for reading only.
* 
* This constant represents a mode of the DomDoc which causes it to use an
* existing XML file as the basis of the DomDoc document on instatiation - 
* using the starter to determin the location of the local file. It treats
* the file as read-only so none of the write methods will work. It uses a
* non-exclusive read lock when opening the file.
*/
define("XAO_DOC_READFILE",20);

/**
* Dom document from existing PHP DOM object instance.
* 
* This constant represents a mode of the DomDoc which causes it to use an
* existing PHP DOM XML object instance for this DomDoc instance. This has the
* effect of adding functionality from this class to an existing DOM object.
*/
define("XAO_DOC_REFERENCE",50);

/**
* Dom document from existing XML data in a variable.
* 
* This constant represents a mode of the DomDoc which causes it to use
* existing XML data as the basis for a new DomDoc object. Obviously the
* XML data needs to be well-formed.
*/
define("XAO_DOC_DATA",60);



/**
* General purpose DOM class
*
* This class provides three forms of functionality. 1) shortcut functions to 
* operations made tedious by the DOM API. 2) additional features not supported
* by the DOM API. 3) a thread-safe way of interacting with files associated 
* with the class' DOM document.
*
* @package      XAO
*/
class DomDoc extends XaoRoot {

    /**
    * Singleton instance of Exceptions object.
    *
    * This is only instantiated if $this->Throw() is called. This object 
    * encapsulates error data management. It keeps all the error data on the
    * referenced DOM doc rather than a stack local to this class.
    * 
    * @access   public
    * @var      object  
    */
    var $objErr;

    /**
    * Element containing the last(current) error message.
    *
    * This is populated by $this->Throw() and is always appended to the root
    * node. in order for consumed DOM documents to have their errors displayed
    * the consume function of the context DomDoc needs to search for these and 
    * copy them to the root node of itself.
    * 
    * @access   public
    * @var      node  
    */
    var $ndErr;

    /**
    * An instance of the main DOM XML object.
    * 
    * The native PHP DOM XML object is kept here. Any PHP DOM methods may
    * be accessed directly from this object. For instance, 
    * $objMy->objDoc->get_element_by_id(); The user can also pass this member
    * to functions requiring a native PHP DOM XML object. It is important to
    * note that the XAO API in no way limits the user's access to PHP's built-in
    * functions.
    *
    * @access   public
    * @var      object  
    */
    var $objDoc;

    /**
    * Document root node
    * 
    * This object variable conains a reference to the root element node
    * of this DomDoc. It's a handy shortcut to $this->objDoc->document_root()
    * because it is used a lot.
    *
    * @access   public
    * @var      node  
    */
    var $ndRoot;
        
    /**
    * How has the document been instantiated.
    * 
    * This attribute remembers the value of the mode constant that was 
    * used to instantiate this DomDoc object.
    *
    * @access   private
    * @var      integer from constant  
    */
    var $_intMode;
    
    /**
    * Queue of element objects to be procssed
    * 
    * Users can use the SetCustomTagName() function to nominate elements by
    * name to be kept in this list. The method also requires the name of a 
    * valid function to do the processing.
    *
    * @access   private
    * @var      integer from constant  
    */
    var $_arrCustomTagNames = array();
    
    /**
    * Queue of query result node objects to be procssed
    * 
    * Users can use the SetCustomTagQuery() function to find nodes to be kept 
    * in this list. The method also requires the name of a valid function to do 
    * the processing.
    *
    * @access   private
    * @var      integer from constant  
    */
    var $_arrCustomTagQueries = array();

    /**
    * Constructor method 
    *
    * Create the objDoc instance property and associated ndRoot property based
    * on the user-selected mode of document creation. 
    *
    * @param    mixed   information required to create a DOM document
    * @param    int     constant specifying how the document is to be created
    * @return   void
    * @access   public
    */
    function DomDoc($mxdData,$intUse = XAO_DOC_NEW) {

        $this->_intMode = $intUse;
                                    // for more info on each case block, see
                                    // comments in the constant definitions
                                    // at the top of this file.
        if($this->_intMode === XAO_DOC_NEW) {
            $this->objDoc = domxml_new_doc("1.0");
            $elRoot = $this->objDoc->create_element($mxdData);
            $this->ndRoot = $this->objDoc->append_child($elRoot);
        } 
        elseif(
            $this->_intMode === XAO_DOC_READFILE 
            || $this->_intMode === XAO_DOC_DATA
        ) {
            $objDomFactory = new DomFactory($mxdData);
            if(strlen($objDomFactory->strErrorMsgFull)) {
                $this->_AbortDocument($objDomFactory->strErrorMsgFull);
                die($objDomFactory->strError);
            }
            else {
                $this->objDoc = $objDomFactory->objGetObjDoc();
            }
            $this->ndRoot = $this->objDoc->document_element();
        }
        elseif($this->_intMode === XAO_DOC_REFERENCE) {
            $this->objDoc = $mxdData;
            $this->ndRoot = $mxdData->document_element()
                ?? $this->_AbortDocument(
                    "The reference document object is not a valid native 
                    PHP DOM XML document."
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
        // produce a basic documemnt so that we
        // have enough to throw an error.
        $this->objDoc = dom_import_simplexml(new \SimpleXMLElement('<root/>'));
        
        $arrErrAttribs = ['code' => 'DomDocInit'];
        $this->Throw(
            $strErrMsg,
            $arrErrAttribs,
            $this->arrSetErrFnc(__FUNCTION__, __LINE__)
        );
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
    function Throw($strErrMsg, $arrAttribs = null) {
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
            $ndExceptions->set_attribute("xmlns", $this->idXaoNamespace);
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
    * @return  xml     document
    * @access  public
    */
    function xmlGetDoc() {
        $this->_TestForConstuctor();
        return $this->objDoc->dump_mem(true);
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
        
        return $this->objDoc->dumpNode($this->ndRoot, true);
    }

        
    /**
    * mass storage serialisation
    *
    * This function will dump the ASCII version of this XML document [in it's
    * current state] to a specified file.
    *
    * @param    uri     path to destination file
    * @return   void
    * @access   public
    */
    function CommitToFile($uriDestination) {
        $this->_TestForConstuctor();
        
        if (!file_exists($uriDestination)) {
            throw new Exception("CommitToFile: " . $uriDestination . " was not found.");
        }
        
        $fp = fopen($uriDestination, "w+") or 
            throw new Exception("CommitToFile: could not open " . $uriDestination . " for writing");

        flock($fp, LOCK_EX) or
            throw new Exception("CommitToFile: Could not get an exclusive lock on " . $uriDestination . " for writing");

        fwrite($fp, $this->xmlGetDoc()) or
            throw new Exception("CommitToFile: could write to " . $uriDestination);

        flock($fp, LOCK_UN);
        fclose($fp);
    }

    
    /**
    * fetch a single element node by name
    *
    * A convenience function for fetching a node reference to an element by
    * specifying only it's name.
    *
    * @param    uri     name of the element whose node is to be returned
    * @param    integer index of which node to return (0 for first)
    * @return   node
    * @access   public
    */
    public function &ndGetOneEl(string $strName, int $intIdx = 0): ?DOMNode {
        $this->_TestForConstructor();
        
        $arrNds = $this->objDoc->getElementsByTagName($strName);
        
        if (isset($arrNds[$intIdx])) {
            return $arrNds[$intIdx];
        }
        
        return null;
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
    public function &ndAppendToRoot(string $strElName, string $strCont = ""): ?DOMNode {
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
        
        return $ndNew;
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
    public function &ndAppendToNode(DOMNode $ndStub, string $strElName, string $strCont = ""): ?DOMNode {
        $this->_TestForConstructor();

        if (!$this->blnTestElementNode($ndStub)) {
            throw new Exception(
                "ndAppendToNode: First argument is not a valid element node.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)
            );
        }

        if (!$this->blnTestXmlName($strElName)) {
            throw new Exception(
                "ndAppendToNode: " . $strElName
                . " Is not a valid element name.",
                $this->arrSetErrFnc(__FUNCTION__, __LINE__)
            );
        }

        $elNew = $this->objDoc->createElement($strElName);
        $ndNew = $ndStub->appendChild($elNew);
        $ndNew->nodeValue = $strCont;

        return $ndNew;
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
        $this->ImportChildFrag($this->ndRoot,$objDoc->ndRoot);
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

    function _TestForConstuctor() {
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
            
            $arrNd = $this->getXPathNodes($strQry);
            
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
        $objRes = xpath_eval(xpath_new_context($this->objDoc), $strExpr);
        
        if (!$objRes->nodeset) {
            return false; 
        }
        
        return $objRes->nodeset;
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
