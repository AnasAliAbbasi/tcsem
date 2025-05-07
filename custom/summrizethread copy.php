<?php

include_once dirname(__FILE__) . '/includes/functions.php';

$thread_id = $ticket->getId();

summrizeThreadDisscussion($thread_id);


function summrizeThreadDisscussion($ticket_id) {

    $threadDetails = getThreadId($ticket_id);
    if(!empty($threadDetails)){
        $threadChat = [];
        $threadList = getThreadList($threadDetails);
        
        if(!empty($threadList)) {
            
            foreach($threadList as $index => $item){
               
                $threadChat[$index]['title'] = $item['title'];
                $threadChat[$index]['poster'] = $item['poster'];
                $threadChat[$index]['body'] = $item['body'];
                $threadChat[$index]['created'] = $item['created']; 
            }

            $summary = callOpenAI($threadChat);


                  //get attachment 
           //push into tgread chat

           //get task
           //push task into thread chat
            echo "
            <!DOCTYPE html>
                <html lang='en'>
                <head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Ticket Chat Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 0;
            background-color: #f4f4f9;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            font-size: 24px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        p {
            font-size: 16px;
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

<div class='container'>
    <h1>Summarized Ticket Chat Thread</h1>
    <div class='summary-content'>
        <p>{$summary['decoded_response']}</p>
    </div>
</div>

</body>
</html>
";

        }else{
            echo "no thread chat found";    
        }

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


    $apiKey = "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"; // Replace this

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "user", "content" => json_encode($chat) ." summrize in paragraph form this is chat of one ticket title is name of ticket and other are chat."]
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


?>