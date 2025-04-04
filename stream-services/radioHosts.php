<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");
    include_once('../connect.php');
    
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        $sql = 'SELECT * FROM ss_radiohost';
        try{
            $verifiedStatement = mysqli_prepare($conn,$sql);
            mysqli_stmt_execute($verifiedStatement);
            $results = mysqli_stmt_get_result($verifiedStatement);
            if ($row = mysqli_fetch_assoc($results)) {
                $radioHosts = array();
                do {
                    // Add each row to the array
                    $radioHosts[] = array(
                        'idHost'         => $row['idHost'],
                        'name'           => $row['name'],
                        'country'        => $row['country'],
                        'fetchTrackInfo' => $row['getTrackInfoUrl'],
                        'trackTitle'     => $row['trackTitle'],
                        'trackArtist'         => $row['trackArtist'],
                        'albumCover'          => $row['trackCover'],
                    );
                } while ($row = mysqli_fetch_assoc($results)); // Fetch remaining rows inside the loop
            
                echo(json_encode([
                        'status' => 'success',
                        'hosts' => $radioHosts
                ]));
            }
            else{
   			 echo json_encode(['status' => 'error', 'message' => 'No hosts available']);
            }
        }
        catch(Exception $e){
    		echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);        
        }
    }
    mysqli_close($conn);

?>