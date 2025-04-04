<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

//TODO double check for post method
include_once('../connect.php');

$requesjson = file_get_contents('php://input');
$data = json_decode($requesjson, true);

if($data !== null){
    $username = addslashes(strip_tags($data['username']));
    $nickname = addslashes(strip_tags($data['nickname']));
    //add later on about & avatar src
    $sql =  "UPDATE `user` SET `nickname`= ? WHERE username =?";
    try{
        //verify the statements to protect code from injective attacks
        $verifiedStatement = mysqli_prepare($conn,$sql);
        mysqli_stmt_bind_param($verifiedStatement, 'ss', $nickname,$username);
        //now you execute it
        if(mysqli_stmt_execute($verifiedStatement)){
            echo 'Nickname updated successfully';
            //learn how to throw an error code here
        }
        else{
            echo 'Invalid format';
        }
    }catch(Exception $e){
        die ($e->getMessage());
    }

}      
else{
    echo 'error';
}       
mysqli_close($conn);
?>