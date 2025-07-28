<?php

include_once dirname(__FILE__) . '/includes/functions.php';

$thread_id = $ticket->getId();
$staff_id = $thisstaff->getId();

$id = $_GET['key'];

summrizeThreadDisscussion($thread_id);

if($id == 'postThread'){
 
    $summary = $_SESSION['summary'];
    $summary = $summary['decoded_response'];

    postThread($summary , $thread_id , $staff_id);

}
function summrizeThreadDisscussion($ticket_id) {

    $threadDetails = getThreadId($ticket_id);
    if(!empty($threadDetails)){
        $threadChat = [];
        $attachments = [];
        $tasks = [];
        $threadList = getThreadList($threadDetails);
        $getAttachment = getAttachment($ticket_id);
        $getTasks = getTaskThread($ticket_id);
        

        foreach($threadList as $index => $item){
               
            $threadChat[$index]['title'] = $item['title'];
            $threadChat[$index]['poster'] = $item['poster'];
            $threadChat[$index]['body'] = $item['body'];
            $threadChat[$index]['created'] = $item['created']; 

        }

        foreach($getAttachment as $index => $item){
            
            $attachments[$index]['document_thread'] = $item['body'];
            $attachments[$index]['document_name'] = $item['name'];
            $attachments[$index]['document_date'] = $item['created'];
        }

        foreach($getTasks as $index => $item){
           
            $tasks[$index]['task_title'] = $item['title'];
            $tasks[$index]['task_date'] = $item['created'];
        }

        $allData = array_merge($threadChat, $attachments, $tasks);

        if (isset($_SESSION['summary']) && $_SESSION['ticket_id'] == $ticket_id) {
            $summary = $_SESSION['summary'];
        } else {
            $summary = callOpenAI($allData);
            $_SESSION['summary'] = $summary;
            $_SESSION['ticket_id'] = $ticket_id; 
        }

        $id = $_GET['key'];
        if($id == 'refresh') {
            $summary = callOpenAI($allData);
            $_SESSION['summary'] = $summary; 
            $_SESSION['ticket_id'] = $ticket_id;
            echo '<script>window.location.href="tickets.php?id=' . $ticket_id . '#summrizeThread";</script>';
        }

        callHTML($summary , $ticket_id);

        
    
    }else{
        echo "thread details not found";
    }
    
}


function getThreadId($ticket_id) {

    $query = sprintf('select * from sem_thread a where a.object_id = %1$d', $ticket_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function getThreadList ($threadObject) {
    $thread_id = $threadObject[0]['id'];

    $query = sprintf('select * from sem_thread_entry a where a.thread_id = %1$d', $thread_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


function callOpenAI ($chat) {

    $cleanedChat = strip_tags(json_encode($chat) , '<a><div><p>');
    

    $apiKey = "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"; // Replace this
     
    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => $cleanedChat ." summrize in paragraph form this is chat of one ticket title is name of ticket and other are chat. dont say hidden message type thing please"]
        ]
    ];

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, "https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);

    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        return [
            'status' => '500',
            'error' => curl_error($ch)
        ]; 
    } else {
        $result = json_decode($response, true);
        return [
            'status' => '200',
            'response' => $result,
            'decoded_response' => htmlspecialchars($result['choices'][0]['message']['content'])
        ]; 
    }

    curl_close($ch);

    
} 

function getAttachment ( $ticket_id ) {
    
    $query = sprintf('SELECT * FROM (
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
            ORDER BY object_id DESC, name ASC', $ticket_id );

    $result = executeQuery($query);
    return getDataFromResultSet($result);

    
}

function getTaskThread( $ticket_id ) {
    $query = sprintf('select * from sem_task a 
        inner join sem_task__cdata b on a.id = b.task_id
    where a.object_id = %1$d', $ticket_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function callHTML($summary , $ticket_id) {
    echo "<!DOCTYPE html>
                <html lang='en'>
                <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Ticket Chat Summary</title>
    <!-- Bootstrap CSS -->


    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1150px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h5 {
            font-weight:bold;
            text-align: left;
            margin-bottom: 20px;
            text-decoration:underline !important;
        }
        p {
            font-size: 17px;
            color: #555;
            line-height: 1.6;
        }
        .summary-content {
            background-color: #fafafa;
            padding: 10px;
            border-left: 4px solid #4CAF50;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class='container'>";
// if($_SESSION['flash'] == 'true'){
//     echo '<div class="success-banner">';
//     echo "Thread Has Been Posted";
//     echo '</div>';

//     unset($_SESSION['flash']);

// }


echo "<h5 ><strong>AI Generated Summary</strong></h5>
    <h6 class='editText' style='font-size: 16px;font-weight: bold;'> </h6>
    <div class='summary-content'>
";

if($_SESSION['ticket_id'] == $ticket_id ){
    echo"
         <p>{$summary['decoded_response']}</p>
 ";
 
}
echo "
 </div>
 <div class='text-center mt-4'>

    <button class='btn btn-outline-primary mt-4' id='refreshSummary' data-ticket='".$ticket_id."' > Refresh Summary </button> 
   
    <button type='button' class='btn btn-outline-primary mt-4' id='showModal'>
        Edit Summary
    </button>

    <button type='button' class='btn btn-primary mt-4 d-none'  id='hideModal'>
        Save Summary
    </button>

    <button type='button' class='btn btn-outline-primary mt-4' data-ticket_id='".$ticket_id."' id='postThread'>
        Post Thread
    </button>
</div>
</div>

</body>


</html>
";
}


function postThread($summary , $ticket_id , $staff_id){

    $getThreadId = getThreadId($ticket_id);
    $thread_id = $getThreadId[0]['id'];

    $query = sprintf(
        'INSERT INTO `sem_thread_entry` (`thread_id`, `staff_id` ,`type`, `flags`, `poster`, `body`, `format`, `ip_address` , `created`) 
        VALUES (%1$d, \'%2$s\', \'%3$s\', \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\' ,  \'%8$s\' ,  \'%9$s\')',
        $thread_id,   
        $staff_id,   
        'R',    
        '576',       
        'Ahmar',      
        '<p> '.$summary.' </p>',        
        'html',        
        '::1',  
        date('Y-m-d H:i:s')
    );

    /* echo $query; */
    $result = executeQuery($query);


    $query = sprintf(
        'INSERT INTO `sem_thread_event` (`thread_id`, `thread_type`, `event_id`, `staff_id`, `team_id`, `dept_id`, `topic_id`, `data`, `username`, `uid`, `uid_type`, `annulled`, `timestamp`) 
        VALUES (%1$d, \'%2$s\', %3$d, %4$d, %5$d, %6$d, %7$d, \'%8$s\', \'%9$s\', %10$d, \'%11$s\', %12$s, UTC_TIMESTAMP())',
        $thread_id,      
        'T',    
        '1',       
        0,      
        0,        
        '1',        
        0,      
        "",           
        'SYSTEM',       
        32,            
        'U',       
        0,      
        date('Y-m-d H:i:s')
    );
    /* echo $query; */
    $result = executeQuery($query);

    $_SESSION['flash'] = 'true';
    echo '<script>window.location.href="tickets.php?id=' . $ticket_id . '#ticket_thread";</script>';
  
} 

?>