<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");
    include_once('../connect.php');
    
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(isset($_GET['searchQuery'])){
			$searchTerm = '%' . $_GET['searchQuery'] . '%';
            $sql = 'SELECT * FROM ss_radiohost
            WHERE idHost LIKE ? OR name LIKE ? OR country LIKE ? OR getTrackInfoUrl LIKE ? OR trackTitle LIKE ? OR trackArtist LIKE ? OR trackCover LIKE ?';
            try{
                $verifiedStatement = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($verifiedStatement, 'sssssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
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
                            'trackArtist'    => $row['trackArtist'],
                            'albumCover'     => $row['trackCover'],
                        );
                    } while ($row = mysqli_fetch_assoc($results)); 
                
                    echo(json_encode($radioHosts));
                }
            }
            catch(Exception $e){
                echo($e->getMessage());            
            }
        }
    }
mysqli_close($conn);
?>