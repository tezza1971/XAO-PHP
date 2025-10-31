<?php
// public/demo/crm/get_contacts.php

use Xao\DomDoc;

$contacts_file = __DIR__ . '/contacts.json';
$contacts = json_decode(file_get_contents($contacts_file), true);

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
