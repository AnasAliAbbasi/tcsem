<?php

include_once '../includes/functions.php';

$question_id = $_REQUEST['id'];

function showStates($question_id)
{
    $moneteringData = getOpenTicketsOfCloseWO($question_id);
    return $moneteringData;
}


function getOpenTicketsOfCloseWO ($question_id) {
    $query = sprintf('select * from _faq_document_version a where a.q_id = %1$s', $question_id);
    $result = executeQuery($query);
    return getDataFromResultSet($result);
}


?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Open Tickets</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>
    <style>
        /* Center the loader within the table */
      /* Loader Styles */
            .loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.8); /* Semi-transparent background */
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999; /* Ensure it stays on top */
            }

            .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 2s linear infinite;
            }

            /* Animation for spinning effect */
            @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
            }

    </style>
</head>
  <body>
    
    <div class="row mt-5">
        <div class="col-md-12">
            <h3 style="text-align:center;text-decoration:underline;">Faqs Document Tickets & Versions Report</h3>
        </div>
    </div>
   

    <!-- Loader Element -->
    <div id="loader" class="loader">
        <div class="spinner"></div>
    </div>


    <hr />
    <div class="row mt-5">
        <div class="col-md-12">
        <table class="table table-striped table-bordered" cellspacing="0" width="100%" id="wo-states">
            <thead>
                <tr>
               
                <th scope="col">Version</th>
                
                <th scope="col">Question</th>
                <th scope="col">Old Document</th>
                <th scope="col">New Document</th>
                <th scope="col">Created At</th>
                </tr>
            </thead>
            <tbody>
            <?php $monetringStates = showStates($question_id); ?>
                <?php $a = 1; if(!empty($monetringStates)){
                    foreach ($monetringStates as $index => $states){ ?>

                    <tr>
                        
                        <th scope="row"><?php echo  "Version - ". $a++; ?></th>
                            
                        <td><?php echo $states['q_name']; ?></td>
                        <td><a href="<?php echo $states['prev_doc'] ?>"><?php echo $states['prev_doc_name']; ?> </a></td>
                        <td><a href="<?php echo $states['new_doc'] ?>"><?php echo $states['new_doc_name']; ?></a></td>
                        <td><?php echo $states['created_at']; ?></td>

                    </tr>
                <?php }
            }else{
                //echo "<h3 style='color:red;'>NO Open Tickets Found</h3>";
            } ?>
            </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>

    $(document).ready(function () {
        // // Show the content and hide the loader after the page fully loads
        $("#loader").fadeOut(500, function () {
            $("#spinner").fadeIn(500);
        });

        // Show loader when the page is refreshed
        $(window).on('beforeunload', function () {
            $("#loader").fadeIn(500);
            $("#spinner").fadeOut(500);
        });

        $('#wo-states').DataTable();
      
    });
    </script>






</body>
</html>


