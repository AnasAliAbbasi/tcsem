<?php
session_start();
include_once __DIR__.'../includes/functions.php';

$data = [];
$topicId = '31';
$question_id = $_REQUEST['id'];

$questionData = getQuestionData($question_id);
$question = $questionData[0]['question'];


$data = checkLogCreated($topicId , $question_id );


if(empty($data)) {
    $ticketId = createTicket( "Faq  - ". $question ." - PCB Ticket", 'Auto Ticket', $topicId, $data);
    $ticketId = str_pad($ticketId, 6, "0", STR_PAD_LEFT);
    generateFaqLog($ticketId , $topicId , $question_id);
    $id = getTicketIDFromNum($ticketId);
    $_SESSION['flash_message'] = "Ticket created with mentioned ticket number: <a href='tickets.php?id=" . htmlspecialchars($id[0]['ticket_id']) . "'>00" . htmlspecialchars($id[0]['ticket_id']) . "</a>.";
    header("Location:../scp/faq.php?id=".$question_id.'&msg='.$_SESSION['flash_message']);
}else{

    $response = getTicketData($data);
    if($response[0]['status_id'] == '3'){
        updateTicketStatus($response[0]['ticket_id']);
        $_SESSION['flash_message'] = "Ticket was created and closed. Open again on the mentioned ticket number: <a href='tickets.php?id=" . htmlspecialchars($response[0]['ticket_id']) . "'>00" . htmlspecialchars($response[0]['ticket_id']) . "</a>.";

        header("Location:../scp/faq.php?id=".$question_id.'&msg='.$_SESSION['flash_message']);
    }else{  
        $_SESSION['flash_message'] = "Ticket is already open on the mentioned ticket number: <a href='tickets.php?id=" . htmlspecialchars($response[0]['ticket_id']) . "'>00" . htmlspecialchars($response[0]['ticket_id']) . "</a>.";

        header("Location:../scp/faq.php?id=".$question_id.'&msg='.$_SESSION['flash_message']);

    }

}

function generateFaqLog($ticketId, $topicId , $question_id)
{
    $query = sprintf('INSERT INTO `_faqs_doc_logs` values (NULL, %1$d, \'%2$s\', %3$d, UTC_TIMESTAMP())', $question_id , $ticketId, $topicId );
    /* echo $query; */
    $result = executeQuery($query);
}

function checkLogCreated($topicId , $question_id) {
    $query = sprintf('select * from _faqs_doc_logs a where a.topic_id = "%1$s" 
            and a.q_id = "%2$s" ', $topicId , $question_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function getQuestionData($question_id) {
    $query = sprintf('select * from sem_faq a where a.faq_id = "%1$s"', $question_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


function getTicketData($data) {
    $query = sprintf('select * from sem_ticket a where a.number = "%1$s"', $data[0]['ticket_id']);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


function updateTicketStatus ($ticket_id) {

    $query = sprintf('update sem_ticket set status_id=1 where ticket_id=%1$d', $ticket_id);
    $result = executeQuery($query);
    return $result;
}

function getTicketIDFromNum ($number) {
    $query = sprintf('select ticket_id from sem_ticket a where a.number = "%1$s"', $number);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}