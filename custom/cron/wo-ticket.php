<?php

include_once '../includes/functions.php';
$settings['topicId'] = array(
    15 => 'MPI PK', /* MPI Ticket */
    30 => 'MPI US', /* MPI Ticket */
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
                25,
                20,
		        26
            ),
            'Yes' => array( /* Revision */
                25,
                20,
		        26
            )
        ),
        'Yes' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                20,
		        26
            ),
        )
    ),
    'Consignmnt' => array( /* SaleType */
        'No' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                'PACIFIC_INSTRUMENTS' => array( /* company */
                    25,
                    20,
		            22
                ),
                'Other' => array(
                    20,
		            22
                )
            ),
            'Yes' => array( /* Revision */
                20,
		        22
            )
        ),
        'Yes' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                20,
                22
            ),
        )
    ),
    'RMA' => array( /* SaleType */
        'No' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                20,
            ),
            'Yes' => array( /* Revision */
                20,
            )
        ),
        'Yes' => array( /* RepeatOrderFlag */
            'No' => array( /* Revision */
                20,
            ),
        )
    ),

);

processWOTickets($settings, $woticketcondition);

function processWOTickets($settings, $woticketcondition)
{
    try{
        list($subject, $msg, $customdata) = setCustomData();
    
        if (isValidArray($customdata)) {

            /* duplicate tickets run time check */
                $processedCombinations = [];
            /* duplicate tickets run time check */

            foreach ($customdata as $cs) {
                $revision = 'No';
                $company = ($cs['_cus_name'] == 'PACIFIC INSTRUMENTS' ? 'PACIFIC_INSTRUMENTS' : 'Other' );
                
                if($cs['_wo_saletype'] == 'Consignmnt' && $cs['_repeat_flag'] == 'No'){
                    $topicIds = $woticketcondition[$cs['_wo_saletype']][$cs['_repeat_flag']][$revision][$company];
                }else{
                    $topicIds = $woticketcondition[$cs['_wo_saletype']][$cs['_repeat_flag']][$revision];
                }

                foreach ($topicIds as $topicId) {
                    if($topicId == 25 && $cs['_rma_flag'] == 1){
                        echo "RMA flag is 1 (active) Not Create BOM " . $cs['won'];
                    }else{

                        $currentCombination = $cs['won'] . '-' . $topicId;
                        if (!in_array($currentCombination, $processedCombinations)) {

                            array_push($processedCombinations , $currentCombination);
                            $document = getDocumentAvailable($cs['won']);
                            $cs['_utc_time'] = (!empty($document) ? $document[0]['Document_Folder'] : 'Docment Not Available Now' ); 
                            $topicTitle = $settings['topicId'][$topicId];

                            sleep(1);

                            $logCheckPoint = checkLogCreated($topicId , $cs['won']);
                            if(empty($logCheckPoint)){
                                $response = checkAlreadyCreated($topicId , $cs['won']);
                                if(empty($response)) {
                                    HitLogsToFile("creating ticket", $topicId . '-' . $topicTitle, $cs['won'] , $processedCombinations);
                                    $ticketId = createTicket( 'Auto '.' (' . $topicTitle . ') '. $cs['won'] .' '. $cs['_custpn_revision'] , $msg, $topicId, $cs);
                                    /* Insert In Log Table For Reference */
                                    if ($ticketId) {
                                        generateWOLog($ticketId, $topicId, $cs);
                                    }
                                }else{
                                    echo "ticket already created with given topic and wo number".$topicId .'-'.$cs['won'].'---';
                                }
                            }else{
                                echo "ticket already created with given topic and wo number".$topicId .'-'.$cs['won'].'---';
                            }
                        }else{
                            HitLogsToFile("skipped duplicate ticket creation", $topicId . '-' . $topicTitle, $cs['won'] , $processedCombinations);
                        }

                      
                    }
                }
            }
        }else{
            echo "No Work Orders Found";
        }
    }catch(Exception $e) {
        logErrorToFile("EXCEPTION : ".$e , json_encode($customdata) , '', '');
        echo "ERROR: ". $e;
    }
    
}




function setCustomData()
{
    $subject = 'Automated WO: ';
    $msg = '';
    $arr = getDataFromDB();
    return array($subject, $msg, $arr);
}


function getDataFromDB()
{

    $today = date('Y-m-d');
	$today = date('Y-m-d', strtotime($today . ' - 5 days'));


    $fields = '_wo.WONumber  as won, _wo.UNIQ_KEY as uniq_key, _wo.SaleType as _wo_saletype, _wo.WOStatus as _wo_status, if(_wo.RepeatOrderFlag = \'Repeat\', "Yes", "No") as _repeat_flag, DATE_FORMAT(_wo.WorkOrderDate, "%d/%m/%y") as _wo_create_date,  DATE_FORMAT(_wo.StartDate, "%d/%m/%y") as _wo_start_date  ,DATE_FORMAT(_wo.DueDate, "%d/%m/%y")  as _wo_due_date, DATE_FORMAT(_wo.ScheduledCompleteDate, "%d/%m/%y")  as _scheduled_complete_date, DATE_FORMAT(_wo.PlannedCompleteDate, "%d/%m/%y") as _wo_complete_planned_date, DATE_FORMAT(_wo.ReleaseDate, "%d/%m/%y") as _release_date, DATE_FORMAT(_wo.CompleteDate, "%d/%m/%y") as _wo_complete_date, _wo.WOQty as _wo_quantity, _wo.WOCompleteQty as _wo_complete_quantity, _wo.WORemainingQty as _wo_balanace_quantity, _wo.Customer as _cus_name, _wo.CustomerPONumber as _cus_po, _mi.ItemPartNo as _cus_pn, _mi.ItemRevision as _cus_pn_rev , CONCAT(_mi.ItemPartNo , " " , _mi.ItemRevision) as _custpn_revision , if(_wo.SaleType = \'Consignmnt\', "Yes", "No") as _is_consigned , if(_wo.TestRequiredFlag = \'Test\', "Yes", "No") as _test_flag , _wo.Lead_Requirement as _lead_requirement , _wo.Clean_Processing as _clean_processing , _wo.Turn_Time as _tt , _wo.RMAFlagCode as _rma_flag , _wo.Customer as _company';

    $query = sprintf('SELECT %1$s FROM manex_work_orders AS _wo INNER JOIN manex_items AS _mi ON _wo.UNIQ_KEY = _mi.UNIQ_KEY LEFT JOIN _wo_cron_logs AS _log ON _wo.WONumber = _log.wo_number WHERE _wo.SaleType IS NOT NULL AND _wo.WoNumber>17959 AND _log.wo_number IS NULL  
AND DATE(_wo.WorkOrderDate) >= "%2$s" ORDER BY _wo.WONumber DESC', $fields , $today);
    $result = executeQuery($query);
    return getDataFromResultSet($result);

}

function checkLogCreated($topicId , $wo_number) {
    $query = sprintf('select * from _wo_cron_logs a where a.topic_id = "%1$s" 
            and a.wo_number = "%2$s" ', $topicId , $wo_number);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function checkAlreadyCreated ($topicId , $wo_number) { 
    $fields = 'a.ticket_id , a.topic_id , b.object_id ,  b.form_id ,  c.value ';
    $query = sprintf('select %1$s from sem_ticket a
                    inner join sem_form_entry b ON a.ticket_id = b.object_id
                    inner join sem_form_entry_values c ON b.id = c.entry_id
                    where a.topic_id = "%2$s"
                    and c.value = "%3$s" ', $fields, $topicId , $wo_number);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function generateWOLog($ticketId, $topicId, $data)
{
    $query = sprintf('INSERT INTO `_wo_cron_logs` values (NULL, %1$d, %2$d, %3$s, \'%4$s\', \'%5$s\', \'%6$s\', \'%7$s\', UTC_TIMESTAMP())', $ticketId, $topicId, $data['won'], $data['uniq_key'], $data['_cus_pn'], $data['_cus_pn_rev'], json_encode($data));
    /* echo $query; */
    $result = executeQuery($query);
}

function getDocumentAvailable ($wo_number){
    $fields = '_wd.Document_Folder';
    $query = sprintf('select %1$s from manex_work_order_documents _wd where _wd.WONumber = '.$wo_number.' limit 1', $fields);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}

function HitLogsToFile($message, $topicId = null, $woNumber = null , $processed = null) {
    $logFile = __DIR__ . '/wo_ticket.log'; // You can change the path as needed

    $time = date('Y-m-d H:i:s');
    $logEntry = "[$time]";

    if ($topicId !== null) {
        $logEntry .= " [Topic ID: $topicId]";
    }

    if ($woNumber !== null) {
        $logEntry .= " [WO: $woNumber]";
    }

    if ($processed !== null) {
        $logEntry .= " [PROCESSED: ".json_encode($processed)."]";
    }
    
    $logEntry .= " $message" . PHP_EOL;

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

function logErrorToFile($message, $topicId = null, $woNumber = null , $ticketId = '') {
    $logFile = __DIR__ . '/wo_error_log.log'; // You can change the path as needed

    $time = date('Y-m-d H:i:s');
    $logEntry = "[$time]";

    if ($topicId !== null) {
        $logEntry .= " [Topic ID: $topicId]";
    }

    if ($woNumber !== null) {
        $logEntry .= " [WO: $woNumber]";
    }

    if ($ticketId !== null) {
        $logEntry .= " [Ticket ID: $ticketId]";
    }
    
    $logEntry .= " $message" . PHP_EOL;

    file_put_contents($logFile, $logEntry, FILE_APPEND);
}