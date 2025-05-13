<?php
include_once dirname(__FILE__) . '/includes/functions.php';

$thread_id = $ticket->getId();
renderAttachmentsSection($thread_id);

function renderAttachmentsSection($thread_id)
{
    $attachments = getAttachmentsByThreadId($thread_id);
    include_once 'templates/attachments.tmpl.php';
}


function getAttachmentsByThreadId($thread_id)
{
    $thread_id = intval($thread_id);
    if ($thread_id) {
        $query = sprintf(
            'SELECT * FROM (
                SELECT 
                    t.number, f.name, f.key, f.signature, a.id, 
                    f.id AS fid, f.size, e.created, a.object_id 
                FROM sem_ticket t 
                LEFT JOIN sem_thread th ON (t.ticket_id = th.object_id) 
                LEFT JOIN sem_thread_entry e ON (e.thread_id = th.id) 
                LEFT JOIN sem_attachment a ON (e.id = a.object_id) 
                LEFT JOIN sem_file f ON (a.file_id = f.id) 
                WHERE t.ticket_id = %1$d AND f.key IS NOT NULL
        
                UNION ALL
        
                SELECT 
                    t.number, f.name, f.key, f.signature, a.id, 
                    f.id AS fid, f.size, e.created, a.object_id 
                FROM sem_ticket t 
                LEFT JOIN sem_task st ON t.ticket_id = st.object_id 
                LEFT JOIN sem_thread th ON (st.id = th.object_id) 
                LEFT JOIN sem_thread_entry e ON (e.thread_id = th.id) 
                LEFT JOIN sem_attachment a ON (e.id = a.object_id) 
                LEFT JOIN sem_file f ON (a.file_id = f.id) 
                WHERE t.ticket_id = %1$d AND f.key IS NOT NULL
            ) AS combined
            ORDER BY object_id DESC, name ASC',
            $thread_id
        );
        $result = executeQuery($query);
        return getDataFromResultSet($result);
    }
}
