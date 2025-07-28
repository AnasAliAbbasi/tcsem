<?php
include_once '../../custom/includes/functions.php';

header('Content-Type: application/json');

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword === '') {
    echo json_encode(['data' => []]);
    exit;
}

$escapedKeyword = mysqli_real_escape_string(getConnection(), "%$keyword%");

$query = "
    SELECT 'FAQ' as type, f.faq_id as id, f.question as title 
    FROM sem_faq f 
    WHERE f.ispublished = 1 AND f.question LIKE '$escapedKeyword'

    UNION

    SELECT 'Ticket' as type, t.ticket_id as id, cd.subject as title
    FROM sem_ticket t
    JOIN sem_ticket__cdata cd ON cd.ticket_id = t.ticket_id 
    WHERE cd.subject LIKE '$escapedKeyword'

    UNION

    SELECT 'Task' as type, task.id as id, cdata.title as title
    FROM sem_task task
    JOIN sem_task__cdata cdata ON cdata.task_id = task.id
    WHERE cdata.title LIKE '$escapedKeyword'
";

$result = executeQuery($query);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $link = '#';
    if ($row['type'] === 'FAQ') {
        $link = "/scp/faq.php?id=" . $row['id'];
    } elseif ($row['type'] === 'Ticket') {
        $link = "/scp/tickets.php?id=" . $row['id'];
    } elseif ($row['type'] === 'Task') {
        $link = "/scp/tasks.php?id=" . $row['id'];
    }

    $data[] = [
        'type' => $row['type'],
        'title' => htmlspecialchars($row['title']),
        'id' => $row['id'],
        'link' => $link
    ];
}

echo json_encode(['data' => $data]);
