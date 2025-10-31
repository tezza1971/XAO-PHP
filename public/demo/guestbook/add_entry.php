<?php
// public/demo/guestbook/add_entry.php

use Xao\DomDoc;

$entries_file = __DIR__ . '/entries.json';

// Get the current entries
$entries = json_decode(file_get_contents($entries_file), true);

// Create the new entry
$new_entry = [
    'name' => $_POST['name'] ?? 'Anonymous',
    'message' => $_POST['message'] ?? '',
    'timestamp' => (new DateTime())->format(DateTime::ATOM),
];

// Add the new entry to the list
$entries[] = $new_entry;

// Save the updated list
file_put_contents($entries_file, json_encode($entries, JSON_PRETTY_PRINT));

// Now, generate the HTML for the updated guestbook
$doc = new DomDoc('div');

foreach (array_reverse($entries) as $entry) {
    $entry_div = $doc->ndAppendToRoot('div');
    $entry_div->setAttribute('class', 'guestbook-entry');

    $doc->ndAppendToNode($entry_div, 'h3', htmlspecialchars($entry['name']));
    $doc->ndAppendToNode($entry_div, 'p', htmlspecialchars($entry['message']));
    $doc->ndAppendToNode($entry_div, 'small', (new DateTime($entry['timestamp']))->format('Y-m-d H:i:s'));
}

echo $doc->htmlGetFrag();
