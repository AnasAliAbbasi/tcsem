<?php

include_once '../includes/functions.php';
$settings['topicId'] = array(
    15 => 'Assembly MPI Valor (PK)', /* MPI Ticket */
    30 => 'Assembly MPI Validation (US)', /* MPI Ticket */
    20 => 'WO Form', /* WO Form */
    21 => 'Valor Processing', /* Technical Review  */
    29 => 'SMT Program', /* MPI Ticket */
    28 => 'Stencil', /* MPI Ticket */
    22 => 'Material Review Consigned', /* Material Review Consigned*/
    26 => 'Turnkey Material Management', /* Material Review Turnkey*/
    23 => 'Test Flag', /* Test Flag */
    24 => 'CS Planning', /* CS Planning */
    25 => 'BOM Load', /* BOM Load */
);

$woticketcondition = array(
    'Turnkey' => array( /* SaleType */
        'No' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                21,
                23,
                15,
                30,
            ),
            'Yes' => array( /* Revision */
                21,
                23,
                15,
                30,
            )
        ),
        'Yes' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                21,
                23,
                15,
                30,
            ),
        )
    )
    ,
    'Consignmnt' => array( /* SaleType */
        'No' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                23,
                21,
                15,
                30,
            ),
            'Yes' => array( /* Revision */
                21,
                15,
                23,
                30,
            )
        ),
        'Yes' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                23,
                15,
                21,
                30,
            ),
        )
    )
);


processWOTickets($settings, $woticketcondition);

function processWOTickets($settings, $woticketcondition)
{
    try{
        list($subject, $msg, $customdata) = setCustomData();

        if (isValidArray($customdata)) {
            foreach ($customdata as $cs) {
                $revision = 'No';
                $topicIds = $woticketcondition[$cs['_wo_saletype']][$cs['_repeat_flag']][$revision];
                foreach ($topicIds as $topicId) {
                    if ($topicId == 23 && (!isset($cs['_wo_test_flag']) || $cs['_wo_test_flag'] != 'Yes')) {
                        echo "test flag is empty" . $cs['won'];
                    }else{
                        $topicTitle = $settings['topicId'][$topicId];
			sleep(1);
                        $logCheckPoint = checkLogCreated($topicId , $cs['won']);
                        if(empty($logCheckPoint)){
                            $response = checkAlreadyCreated($topicId , $cs['won']);
                            if(empty($response)) {
                                $ticketId = createTicket( 'Auto '.' (' . $topicTitle . ') '. $cs['won'] .' '. $cs['_custpn_revision'] , $msg, $topicId, $cs);
                                /* Insert In Log Table For Reference */
                                if ($ticketId) {
                                    generateWOLog($ticketId, $topicId, $cs);
                                }
                            }else{
                                echo "ticket already created with given topic and wo number".$topicId .'-'.$cs['won'].'---';
                            }
                        }else{
                            echo "ticket already _wo_log created with given topic and wo number".$topicId .'-'.$cs['won'].'---';
                        }
                   
                    }
             
                }
            }
        }else{
            echo "No Work Orders Found";
        }
    }catch(Exception $e) {
        echo "ERROR: ". $e;

    }
    
}



function setCustomData()
{
    $subject = 'Automated WO: ';
    $msg = '';
    $tech_review_ticket_arr = getTechReviewTicketsOrders();
    $arr = getDataFromDB();
    return array($subject, $msg, $arr);
}

function checkAlreadyCreated ($topicId , $wo_number) { 
    $fields = 'a.ticket_id , a.topic_id , b.object_id ,  b.form_id ,  c.value ';
    $query = sprintf('select %1$s from sem_ticket a
                    inner join sem_form_entry b ON a.ticket_id = b.object_id
                    inner join sem_form_entry_values c ON b.id = c.entry_id
                    where a.topic_id = "%2$s"
                    and c.value like "%%%3$s%%" ', $fields, $topicId , $wo_number);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function checkLogCreated($topicId , $wo_number) {
    $query = sprintf('select * from _wo_cron_logs a where a.topic_id = "%1$s" 
            and a.wo_number = "%2$s" ', $topicId , $wo_number);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function getDataFromDB($wo_no = '')
{  
    $today = date('Y-m-d');
    $today = date('Y-m-d', strtotime($today . ' - 5 days'));
    
    $fields = '_wo.WONumber  as won, _wo.UNIQ_KEY as uniq_key, _wo.SaleType as _wo_saletype, _wo.WOStatus as _wo_status, if(_wo.RepeatOrderFlag = \'Repeat\', "Yes", "No") as _repeat_flag, DATE_FORMAT(_wo.WorkOrderDate, "%d/%m/%y")  as _wo_create_date , DATE_FORMAT(_wo.StartDate, "%d/%m/%y")  as _wo_start_date , DATE_FORMAT(_wo.DueDate, "%d/%m/%y")  as _wo_due_date, DATE_FORMAT(_wo.ScheduledCompleteDate, "%d/%m/%y")  as _scheduled_complete_date, DATE_FORMAT(_wo.PlannedCompleteDate, "%d/%m/%y")  as _wo_complete_planned_date, DATE_FORMAT(_wo.ReleaseDate, "%d/%m/%y")  as _release_date, DATE_FORMAT(_wo.CompleteDate, "%d/%m/%y") as _wo_complete_date, _wo.WOQty as _wo_quantity, _wo.WOCompleteQty as _wo_complete_quantity, _wo.WORemainingQty as _wo_balanace_quantity, _wd.Document_Folder as _utc_time, _wo.Customer as _cus_name, _wo.CustomerPONumber as _cus_po, _mi.ItemPartNo as _cus_pn, _mi.ItemRevision as _cus_pn_rev , CONCAT(_mi.ItemPartNo , " " , _mi.ItemRevision) as _custpn_revision , if(_wo.TestRequiredFlag = \'Test\', "Yes", "No") as _wo_test_flag , if(_wo.SaleType = \'Consignmnt\', "Yes", "No") as _is_consigned , if(_wo.TestRequiredFlag = \'Yes\', "Yes", "No") as _test_flag , _wo.Lead_Requirement as _lead_requirement , _wo.Clean_Processing as _clean_processing , _wo.RMAFlagCode as _rma_flag';
    $query = sprintf('select %1$s from manex_work_orders _wo 
    INNER JOIN manex_items as _mi On _wo.UNIQ_KEY = _mi.UNIQ_KEY
    INNER JOIN manex_work_order_documents as _wd On _wo.WONumber = _wd.WONumber
    INNER JOIN _wo_cron_logs as _wl ON _wo.WONumber = _wl.wo_number
    INNER JOIN sem_ticket as _st ON CONCAT("00",_wl.ticket_id) = _st.number
    INNER JOIN sem_form_entry as _sfe ON _st.ticket_id = _sfe.object_id
    INNER JOIN sem_form_entry_values as _sfev ON _sfe.id = _sfev.entry_id
    Where _wo.WONumber > 17959
    and _wl.topic_id = 27
    and _st.status_id = 3
    and DATE(_st.closed) >= "%2$s"
    and _sfev.value like "%%Yes%%";', $fields , $today);

    

    $result = executeQuery($query);
    return getDataFromResultSet($result);

}

function getTechReviewTicketsOrders() {

}


function generateWOLog($ticketId, $topicId, $data)
{
    $query = sprintf('INSERT INTO `_wo_cron_logs` values (NULL, %1$d, %2$d, %3$s, \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', UTC_TIMESTAMP())', $ticketId, $topicId, $data['won'], $data['uniq_key'], $data['_cus_pn'], $data['_cus_pn_rev'], json_encode($data));
    /* echo $query; */
    $result = executeQuery($query);
}
