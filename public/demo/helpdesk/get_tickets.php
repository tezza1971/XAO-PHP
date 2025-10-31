<?php
// public/demo/helpdesk/get_tickets.php

use Xao\DomDoc;

$tickets_file = __DIR__ . '/tickets.json';
$tickets = json_decode(file_get_contents($tickets_file), true);

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
