<?php
// public/index.php

// The entry point for the XAO-PHP application.

require_once __DIR__ . '/../vendor/autoload.php';

use Xao\AppDoc;
use Xao\Transformer;

// 1. Create the main application document.
// This will hold the content and structure of our application.
$app = new AppDoc("<content/>");

// 2. Add some data to the document.
// In a real application, this data would come from a database, an API,
// or user input.
$app->ndAppendToRoot("title", "Welcome to XAO-PHP!");
$app->ndAppendToRoot("message", "The framework has been successfully modernized.");

// 3. Create a transformer to convert the XML to HTML.
// This uses an XSLT stylesheet to define the transformation rules.
$transformer = new Transformer($app, __DIR__ . '/../templates/main.xsl');

// 4. Set a parameter for the stylesheet.
// This shows how you can pass dynamic data to your templates.
$transformer->SetParam('CURRENT_YEAR', date('Y'));

// 5. Process the transformation and output the result.
echo $transformer->strProcess();
