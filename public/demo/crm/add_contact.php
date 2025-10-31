<?php
// public/demo/crm/add_contact.php

use Xao\DomDoc;

$contacts_file = __DIR__ . '/contacts.json';

// Get the current contacts
$contacts = json_decode(file_get_contents($contacts_file), true);

// Create the new contact
$new_contact = [
    'id' => count($contacts) + 1,
    'name' => $_POST['name'] ?? 'No Name',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'interactions' => [],
];

// Add the new contact to the list
$contacts[] = $new_contact;

// Save the updated list
file_put_contents($contacts_file, json_encode($contacts, JSON_PRETTY_PRINT));

// Now, generate the HTML for the updated contact list
$doc = new DomDoc('div');

foreach (array_reverse($contacts) as $contact) {
    $contact_div = $doc->ndAppendToRoot('div');
    $contact_div->setAttribute('class', 'contact');

    $doc->ndAppendToNode($contact_div, 'h3', htmlspecialchars($contact['name']));
    $doc->ndAppendToNode($contact_div, 'p', 'ID: ' . htmlspecialchars($contact['id']));
    $doc->ndAppendToNode($contact_div, 'p', 'Email: ' . htmlspecialchars($contact['email']));
    $doc->ndAppendToNode($contact_div, 'p', 'Phone: ' . htmlspecialchars($contact['phone']));
}

echo $doc->htmlGetFrag();
