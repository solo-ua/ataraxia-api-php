<?php
    // These headers ensure responses are not stored locally, forcing the cache to not be collected
    // It also ensures the requests are up-to-date
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0");
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");

    include_once('../connect.php');
    
    // Specify the statement:
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        if (isset($_GET['username'])) {
            $username = $_GET['username'];
            
            // Modify the query to look for the exact username match
            $sql = 'SELECT id, username, nickname, dateJoined, about FROM user WHERE username = ?';  // Exact match
            
            try {
                $verifiedStatement = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($verifiedStatement, 's', $username);  // No wildcards
                
                mysqli_stmt_execute($verifiedStatement);
                
                $results = mysqli_stmt_get_result($verifiedStatement);
                $rows = [];
                
                $rowCount = 0;
                while ($row = mysqli_fetch_assoc($results)) {
                    $rows[] = $row;
                    $rowCount++;
                }

                if (count($rows) > 0) {
                    // found users
                    echo json_encode([
                        "status" => "success",
                        "message" => "User found",
                        "users" => $rows,
                    ]);
                } else {
                    // no users found
                    echo json_encode([
                        "status" => "error",
                        "message" => "No user found",
                    ]);
                }

                mysqli_free_result($results);
            } catch (Exception $e) {
                echo json_encode([
                    "status" => "error",
                    "message" => $e->getMessage(),
                ]);
            }
        }
    }        
    
    mysqli_close($conn);
?>
