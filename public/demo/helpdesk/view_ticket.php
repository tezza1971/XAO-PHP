<?php
// public/demo/helpdesk/view_ticket.php

use Xao\DomDoc;

$tickets_file = __DIR__ . '/tickets.json';
$tickets = json_decode(file_get_contents($tickets_file), true);

$ticket_id = $_GET['ticket_id'] ?? null;

$ticket_to_view = null;
foreach ($tickets as $ticket) {
    if ($ticket['id'] == $ticket_id) {
        $ticket_to_view = $ticket;
        break;
    }
}

$doc = new DomDoc('div');

if ($ticket_to_view) {
    $doc->ndAppendToRoot('h3', htmlspecialchars($ticket_to_view['subject']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', 'ID: ' . htmlspecialchars($ticket_to_view['id']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', 'Status: ' . htmlspecialchars($ticket_to_view['status']));
    $doc->ndAppendToNode($doc->ndRoot, 'p', htmlspecialchars($ticket_to_view['message']));
    $doc->ndAppendToNode($doc->ndRoot, 'small', (new DateTime($ticket_to_view['created_at']))->format('Y-m_d H:i:s'));
} else {
    $doc->ndAppendToRoot('p', 'Ticket not found.');
}

echo $doc->htmlGetFrag();
