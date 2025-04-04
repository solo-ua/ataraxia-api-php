<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");
    include_once('../connect.php');
    
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $sql = 'SELECT * FROM ss_stations';
        try{
            $verifiedStatement = mysqli_prepare($conn,$sql);
            mysqli_stmt_execute($verifiedStatement);
            $results = mysqli_stmt_get_result($verifiedStatement);
            if ($row = mysqli_fetch_assoc($results)) {
                $stations = array();
                do {
                    // Add each row to the array
                    $stations[] = array(
                        'idHostedBy'    => $row['idHostedBy'],
                        'idStation'     => $row['idStation'],
                        'name'          => $row['name'],
                        'url'           => $row['streamUrl'],
                    );
                } while ($row = mysqli_fetch_assoc($results)); // Fetch remaining rows inside the loop
            
                echo(json_encode([
                        'status'    => 'success',
                        'stations'  => $stations
                ]));
            }
            else{
   			 echo json_encode(['status' => 'error', 'message' => 'No stations available']);
            }
        }
        catch(Exception $e){
    		echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);        
        }
    }
    mysqli_close($conn);

?>