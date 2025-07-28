<?php

include_once dirname(__FILE__) . '/includes/functions.php';

 

$thread_id = $ticket->getId();

renderTicketsSection($thread_id);

 

function renderTicketsSection($thread_id)

{

   

    $wo_arr = getWorKOrderNo($thread_id);

    if(!empty($wo_arr)){

        $ticket_list = getRelatedTickets($wo_arr[0]['keyword'] , $thread_id , $wo_arr);

        include_once 'templates/ticketlist.tmp.php';

    }else{

        echo "No Part No Found";

    }

 

   

}

 

function getWorKOrderNo ($thread_id) {

    $thread_id = intval($thread_id);

 

    if ($thread_id) {

 

        $query = sprintf('SELECT DISTINCT

            a.*,

            d.value AS keyword,

            c.id AS id

            FROM sem_ticket a

            INNER JOIN sem_help_topic_form b ON a.topic_id = b.topic_id

            INNER JOIN sem_form_entry c ON b.form_id = c.form_id AND a.ticket_id = c.object_id

            INNER JOIN sem_form_entry_values d ON c.id = d.entry_id

            WHERE a.ticket_id = %1$d

            AND b.form_id = "2"

            AND d.field_id = "46"', $thread_id);

 

        $result = executeQuery($query);

        return getDataFromResultSet($result);

    }

}

 

function getRelatedTickets($wo_number , $thread_id , $arr )

{

    $wo_number = $wo_number;

   

    if ($wo_number) {

       

            $query = sprintf("

 

                SELECT

                a.*,

                sts.name,

                _su.name AS holder_name,

                CONCAT(_ss.firstname, ' ', _ss.lastname) AS assignee,

                a.topic_id,

                a.number AS ticket_no,

                fv.value AS part_no

            FROM sem_ticket a

            INNER JOIN sem_ticket_status sts ON a.status_id = sts.id

            INNER JOIN sem_user _su ON _su.id = a.user_id

            LEFT JOIN sem_staff _ss ON _ss.staff_id = a.staff_id

 

            -- Strict filter on both ticket_id and topic_id

            INNER JOIN (

                SELECT

                    c.object_id AS ticket_id,

                    b.topic_id,

                    d.value

                FROM sem_help_topic_form b

                JOIN sem_form_entry c ON b.form_id = c.form_id

                JOIN sem_form_entry_values d ON c.id = d.entry_id

                WHERE b.form_id = 2

                AND d.field_id = 46

                AND d.value LIKE '%%%s%%'

            ) AS fv ON fv.ticket_id = a.ticket_id AND fv.topic_id = a.topic_id;", $wo_number);

 

        $result = executeQuery($query);

        return getDataFromResultSet($result);

    }

}