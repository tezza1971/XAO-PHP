<?php
// public/demo/guestbook/get_entries.php

use Xao\DomDoc;

$entries_file = __DIR__ . '/entries.json';
$entries = json_decode(file_get_contents($entries_file), true);

$doc = new DomDoc('div');

foreach (array_reverse($entries) as $entry) {
    $entry_div = $doc->ndAppendToRoot('div');
    $entry_div->setAttribute('class', 'guestbook-entry');

    $doc->ndAppendToNode($entry_div, 'h3', htmlspecialchars($entry['name']));
    $doc->ndAppendToNode($entry_div, 'p', htmlspecialchars($entry['message']));
    $doc->ndAppendToNode($entry_div, 'small', (new DateTime($entry['timestamp']))->format('Y-m-d H:i:s'));
}

echo $doc->htmlGetFrag();
