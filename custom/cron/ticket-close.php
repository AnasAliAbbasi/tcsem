<?php

include_once '../includes/functions.php';

deleteTickets();

function deleteTickets()
{
    list($subject, $msg, $customdata) = setCustomData();

    if (isValidArray($customdata)) {
        foreach ($customdata as $cs) {

            $updateTicket = updateTicketStatus($cs['wo_number'], $cs['number']);
            if ($updateTicket) { 
                updateEvents($cs);
                generateWOLog($cs);
            }
            echo  "Ticket ID: ".$cs['ticket_id']." is updated to closed.\n";
        }
    }else{
        echo "No OPen Tickets Found";
    }
}



function setCustomData()
{
    $subject = 'Ticket No: ';
    $msg = '';
    $arr = getDataFromDB();
    return array($subject, $msg, $arr);
}

// CREATE INDEX idx_wo_number ON _wo_cron_logs(wo_number);ALGORITHM=INPLACE;
// CREATE INDEX idx_ticket_id ON _wo_cron_logs(ticket_id);ALGORITHM=INPLACE;
// CREATE INDEX idx_wo_number_status ON manex_work_orders(WONumber, WOStatus);ALGORITHM=INPLACE;
// CREATE INDEX idx_ticket_status ON sem_ticket(number, status_id);ALGORITHM=INPLACE;


// ALTER TABLE _wo_cron_logs
//ADD COLUMN padded_ticket_id VARCHAR(255) AS (LPAD(ticket_id, 6, '0')) STORED;




function getDataFromDB($wo_no = '')
{   
    $fields = 'l.wo_number, l.ticket_id , m.WOStatus, s.status_id , s.number , s.dept_id , s.staff_id , s.topic_id , s.user_id' ;
    $query = sprintf('select %1$s from _wo_cron_logs l 
    join manex_work_orders m on l.wo_number=m.WONumber 
    join sem_ticket s on CONCAT("00",l.ticket_id)=s.number
    where m.WOStatus in ("Closed","Cancel") and s.status_id<3', $fields);

    echo $query;exit;
    
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function getTechReviewTicketsOrders() {

}


function generateWOLog($data)
{
    $query = sprintf('INSERT INTO `_wo_cron_ticket_close_logs` values (NULL, %1$d, %2$d, \'%3$s\', \'%4$s\', UTC_TIMESTAMP())', $data['ticket_id'], $data['wo_number'], ''.$data['WOStatus'].'', $data['status_id']);
    /* echo $query; */
    $result = executeQuery($query);
}

function updateEvents($data)
{
    $query = sprintf(
        'INSERT INTO `sem_thread_event` (`thread_id`, `thread_type`, `event_id`, `staff_id`, `team_id`, `dept_id`, `topic_id`, `data`, `username`, `uid`, `uid_type`, `annulled`, `timestamp`) 
        VALUES (%1$d, \'%2$s\', %3$d, %4$d, %5$d, %6$d, %7$d, \'%8$s\', \'%9$s\', %10$d, \'%11$s\', %12$s, UTC_TIMESTAMP())',
        $data['ticket_id'],      
        'T',    
        '2',       
        $data['staff_id'],      
        0,        
        $data['dept_id'],        
        $data['topic_id'],      
        "",           
        'Auto',       
        '',            
        'S',       
        0,      
        date('Y-m-d H:i:s')
    );
    /* echo $query; */
    $result = executeQuery($query);
}

function updateTicketStatus ($wo_number, $ticket_id) {

    $query = sprintf('update sem_ticket set status_id=3 where number=%1$d', $ticket_id);
    $result = executeQuery($query);
    return $result;
}