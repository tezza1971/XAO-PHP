<?php
// public/demo/helpdesk/add_ticket.php

use Xao\DomDoc;

$tickets_file = __DIR__ . '/tickets.json';

// Get the current tickets
$tickets = json_decode(file_get_contents($tickets_file), true);

// Create the new ticket
$new_ticket = [
    'id' => count($tickets) + 1,
    'subject' => $_POST['subject'] ?? 'No Subject',
    'message' => $_POST['message'] ?? '',
    'status' => 'open',
    'created_at' => (new DateTime())->format(DateTime::ATOM),
];

// Add the new ticket to the list
$tickets[] = $new_ticket;

// Save the updated list
file_put_contents($tickets_file, json_encode($tickets, JSON_PRETTY_PRINT));

// Now, generate the HTML for the updated ticket list
$doc = new DomDoc('div');

foreach (array_reverse($tickets) as $ticket) {
    $ticket_div = $doc->ndAppendToRoot('div');
    $ticket_div->setAttribute('class', 'ticket');

    $doc->ndAppendToNode($ticket_div, 'h3', htmlspecialchars($ticket['subject']));
    $doc->ndAppendToNode($ticket_div, 'p', 'ID: ' . htmlspecialchars($ticket['id']));
    $doc->ndAppendToNode($ticket_div, 'p', 'Status: ' . htmlspecialchars($ticket['status']));
    $doc->ndAppendToNode($ticket_div, 'small', (new DateTime($ticket['created_at']))->format('Y-m-d H:i:s'));
}

echo $doc->htmlGetFrag();
