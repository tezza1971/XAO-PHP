<?php
// public/demo/crm/add_interaction.php

use Xao\DomDoc;

$contacts_file = __DIR__ . '/contacts.json';

// Get the current contacts
$contacts = json_decode(file_get_contents($contacts_file), true);

$contact_id = $_POST['contact_id'] ?? null;
$notes = $_POST['notes'] ?? '';

// Find the contact and add the interaction
foreach ($contacts as &$contact) {
    if ($contact['id'] == $contact_id) {
        $contact['interactions'][] = [
            'date' => (new DateTime())->format(DateTime::ATOM),
            'notes' => $notes,
        ];
        break;
    }
}

// Save the updated list
file_put_contents($contacts_file, json_encode($contacts, JSON_PRETTY_PRINT));

// Now, redirect back to the view_contact.php to show the updated details
$_GET['contact_id'] = $contact_id;
include 'view_contact.php';
