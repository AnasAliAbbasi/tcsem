<?php

// require_once '../../include/class.file.php';

include_once '../includes/functions.php';

updateFaqsDocument();

function updateFaqsDocument()
{
    list($subject, $msg, $customdata) = setCustomData();

    if (isValidArray($customdata)) {

        foreach ($customdata as $index => $document){
            $getDocumentDetails = getThreadDocumentDetails($document);
            
            if(!empty($getDocumentDetails)){
                $getFaqLogDetails = getFaqLogsDetails($getDocumentDetails);
                $response = updateFaqsAttachedDocument($getFaqLogDetails , $getDocumentDetails);
                echo "faqs document has updated";
            }else{
                echo "thread not found";
            }
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


function getDataFromDB($wo_no = '')
{   
    $today = date('Y-m-d');
    $fields = 'l.wo_number, l.ticket_id , m.WOStatus, s.status_id , s.number , s.dept_id , s.staff_id , s.topic_id , s.user_id' ;
    $query = sprintf('select * from _faqs_doc_logs l 
    join sem_ticket s on l.ticket_id=s.number
    where s.status_id=3
    and DATE(s.closed) = "%2$s"
    ', $fields , $today);
    
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


function getThreadDocumentDetails ($data){

    $ticket_id = $data['ticket_id'];
    $today = date('Y-m-d');
   
    $fields = 't.number, f.name, f.key, f.signature, a.id, f.id as `fid`, f.size, f.type , f.ft ,f.bk, e.created, a.object_id' ;
    $query = sprintf('SELECT %1$s
        FROM sem_ticket t
        LEFT JOIN sem_thread th ON (t.ticket_id = th.object_id)
        LEFT JOIN sem_thread_entry e ON (e.thread_id = th.id)
        LEFT JOIN sem_attachment a ON (e.id = a.object_id)
        LEFT JOIN sem_file f ON (a.file_id = f.id)
        WHERE t.ticket_id = "%2$d"  AND f.key <> "NULL" 
        and DATE(t.closed) = "%3$s"
        order by e.created desc limit 1', $fields , $ticket_id , $today);

    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


function  getFaqLogsDetails ($data){

    $number = $data[0]['number'];
    $query = sprintf('select a.* , b.question from _faqs_doc_logs a inner join sem_faq b on a.q_id = b.faq_id where a.ticket_id = %1$s' , $number);

    $result = executeQuery($query);
    return getDataFromResultSet($result);
    
}

function updateFaqsAttachedDocument ($logDetails , $docDetails) {

    $response = archiveOldDocument($logDetails , $docDetails);
    $downloadAbleLink = createDownloadAbleLink($response , $docDetails);

    // echo "<pre>";print_R($response);
    // echo "<pre>";print_R($docDetails);exit;
    
    if($response[0]['fid'] != $docDetails[0]['fid']){
        saveDocumentVersions($logDetails , $downloadAbleLink , $response , $docDetails);

        $question_id = $logDetails[0]['q_id'];
        $f_id = $docDetails[0]['fid'];
        $name = $docDetails[0]['name'];
        $prev_doc_id = $response[0]['id'];

        $query = sprintf('update sem_attachment a set a.file_id = "%1$s" , a.name = "%3$s"
            where a.object_id = "%2$s" and a.id = "%4$s" ', $f_id , $question_id , $name , $prev_doc_id);
        $result = executeQuery($query);

        return $result;

    }
}

function archiveOldDocument ($faqLogDetails , $ticketDocumentDetails) {

    $q_id = $faqLogDetails[0]['q_id'];
    $fields = 'f.name, f.key, f.signature, a.id, f.id as `fid`, f.size, a.object_id , f.name';
    $query = sprintf('select %2$s from sem_attachment a 
            inner join sem_file f on a.file_id = f.id
            where a.object_id = "%1$s" order by a.id desc limit 1;'   , $q_id , $fields);

    $result = executeQuery($query);
    return getDataFromResultSet($result); 
 
}


function createDownloadAbleLink ($previousDoc , $currentDoc) {

    $docs = [
        'previous' => $previousDoc,
        'current' => $currentDoc, 
    ];

    $downloadLinks = [];

    foreach ($docs as $index => $document) {
        $gmnow = time();
        $base = str_replace(array('\\', 'custom/templates'), array('/', ''), substr(dirname(__FILE__), strlen($_SERVER['DOCUMENT_ROOT'])));

        $expires = $gmnow + 86400 - ($gmnow % 86400);
        $document = $document[0];
        $name = $document['name'];
        $fid = $document['fid'];
        $id = $document['id'];
        $key = strtolower($document['key']);
        $hash = $document['signature'];
        $signature = createHash($fid, $key, $hash, $expires);
        $attach_link = sprintf('/file.php?key=%1$s&expires=%4$d&signature=%2$s&id=%3$d', $key, $signature, $id, $expires);
        
        $downloadLinks[$index] = $attach_link;
        
    }

    return $downloadLinks;
    
}


function createHash($fid, $key, $signature, $expires) {
     
    $pieces = array(
        'Host='.$_SERVER['HTTP_HOST'],
        'Path='.'/osticket/',
        'Id='.$fid,
        'Key='.strtolower($key),
        'Hash='.$signature,
        'Expires='.$expires,
    );

    return hash_hmac('sha1', implode("\n", $pieces), '602bASIxKa4xpWoz319AZsG07LMERYHN');

}

function saveDocumentVersions ($logDetails , $links , $prevDocument , $newDocument){

    $question_id = $logDetails[0]['q_id'];
    $old = $links['previous'];
    $new = $links['current'];
    $name = $logDetails[0]['question'];
    $prev_doc_name = $prevDocument[0]['name'];
    $new_doc_name = $newDocument[0]['name'];

    $query = sprintf('INSERT INTO `_faq_document_version` values (NULL, %1$d, \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\' , \'%6$s\' ,UTC_TIMESTAMP())', $question_id , $name, $old , $new , $prev_doc_name , $new_doc_name);
    /* echo $query; */
    $result = executeQuery($query);
}