<?php
    //These headers ensure responses are not stored locally, forcing the cache to not be collected
    //It also ensures the requests are up-to-date
    //the reason there are three headers: One is modern, second is for explorer, and third is for http 1
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0");
    header("Pragma: no-cache");
	header("Access-Control-Allow-Origin: *");

    include_once('../connect.php');
    //specify the statement:
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
        if(isset($_GET['nickname'])){
            $nickname = $_GET['nickname'];
            $sql = 'SELECT username, nickname, dateJoined, about FROM user WHERE nickname = ?';
            try{
                //verify the statements to protect code from injective attacks
                $verifiedStatement = mysqli_prepare($conn,$sql);
                mysqli_stmt_bind_param($verifiedStatement, 's', $nickname);
                //now you execute it
                mysqli_stmt_execute($verifiedStatement);
                //fetching the resulted row
                $results = mysqli_stmt_get_result($verifiedStatement);
                //check if user was detected in result
                while ($row = mysqli_fetch_assoc($results)) {
                    // User found so return the user data as JSON
                    $resultArray[] = $row;
                } 
                echo(json_encode($resultArray));
                mysqli_free_result($results);
            }catch(Exception $e){
                echo($e->getMessage());            
            }
        }
    }        
    mysqli_close($conn);

?>