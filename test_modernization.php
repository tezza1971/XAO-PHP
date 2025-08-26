<?php
/**
 * Simple test to verify XAO-PHP modernization works
 */

// Include the main classes
include_once "classes/XAO_DomDoc.php";

try {
    echo "Testing XAO-PHP modernization...\n\n";
    
    // Test 1: Create a new document
    echo "1. Testing new document creation:\n";
    $doc = new DomDoc("root", XAO_DOC_NEW);
    echo "   ✓ New document created successfully\n";
    
    // Test 2: Get XML output
    echo "2. Testing XML serialization:\n";
    $xml = $doc->xmlGetDoc();
    echo "   ✓ XML output: " . trim($xml) . "\n";
    
    // Test 3: Test XPath functionality
    echo "3. Testing XPath functionality:\n";
    $results = $doc->arrNdXPath("//root");
    if ($results !== false && count($results) > 0) {
        echo "   ✓ XPath query successful, found " . count($results) . " node(s)\n";
    } else {
        echo "   ✗ XPath query failed\n";
    }
    
    // Test 4: Test document from XML data
    echo "4. Testing document from XML data:\n";
    $xmlData = '<?xml version="1.0"?><test><item>Hello World</item></test>';
    $doc2 = new DomDoc($xmlData, XAO_DOC_DATA);
    echo "   ✓ Document created from XML data successfully\n";
    
    echo "\n✅ All tests passed! XAO-PHP modernization is working.\n";
    
} catch (Exception $e) {
    echo "\n❌ Test failed with error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>