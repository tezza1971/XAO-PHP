<?php
// public/demo/crm/view_contact.php

use Xao\DomDoc;

$contacts_file = __DIR__ . '/contacts.json';
$contacts = json_decode(file_get_contents($contacts_file), true);

$contact_id = $_GET['contact_id'] ?? null;

$contact_to_view = null;
foreach ($contacts as $contact) {
    if ($contact['id'] == $contact_id) {
        $contact_to_view = $contact;
        break;
    }
}

$doc = new DomDoc('div');

if ($contact_to_view) {
    $doc->ndAppendToRoot('h3', htmlspecialchars($contact_to_view['name']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', 'ID: ' . htmlspecialchars($contact_to_view['id']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', 'Email: ' . htmlspecialchars($contact_to_view['email']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', 'Phone: ' . htmlspecialchars($contact_to_view['phone']));

    $doc->ndAppendToRoot('h4', 'Interactions');
    $interactions_div = $doc->ndAppendToRoot('div');
    foreach ($contact_to_view['interactions'] as $interaction) {
        $interaction_div = $doc->ndAppendToNode($interactions_div, 'div');
        $interaction_div->setAttribute('class', 'interaction');
        $doc->ndAppendToNode($interaction_div, 'p', htmlspecialchars($interaction['notes']));
        $doc->ndAppendToNode($interaction_div, 'small', (new DateTime($interaction['date']))->format('Y-m-d H:i:s'));
    }

    $doc->ndAppendToRoot('hr');

    $doc->ndAppendToRoot('h4', 'Add Interaction');
    $form = $doc->ndAppendToRoot('form');
    $form->setAttribute('hx-post', 'add_interaction.php');
    $form->setAttribute('hx-target', '#contact-details');
    $form->setAttribute('hx-swap', 'innerHTML');

    $input_id = $doc->ndAppendToNode($form, 'input');
    $input_id->setAttribute('type', 'hidden');
    $input_id->setAttribute('name', 'contact_id');
    $input_id->setAttribute('value', $contact_to_view['id']);

    $div_notes = $doc->ndAppendToNode($form, 'div');
    $label_notes = $doc->ndAppendToNode($div_notes, 'label', 'Notes:');
    $label_notes->setAttribute('for', 'notes');
    $textarea_notes = $doc->ndAppendToNode($div_notes, 'textarea');
    $textarea_notes->setAttribute('id', 'notes');
    $textarea_notes->setAttribute('name', 'notes');
    $textarea_notes->setAttribute('required', 'true');

    $button = $doc->ndAppendToNode($form, 'button', 'Add Interaction');
    $button->setAttribute('type', 'submit');

} else {
    $doc->ndAppendToRoot('p', 'Contact not found.');
}

echo $doc->htmlGetFrag();
