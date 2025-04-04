<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0"); 
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
include_once('../connect.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   $data = json_decode(file_get_contents('php://input'), true);
   
   if (isset($data['ids']) && is_array($data['ids'])) {
       $ids = $data['ids'];
       $placeholders = str_repeat('?,', count($ids) - 1) . '?';
       
       $sql = "SELECT id, username, nickname, dateJoined, about FROM user WHERE id IN ($placeholders)";
       
       try {
           $stmt = mysqli_prepare($conn, $sql);
           $types = str_repeat('i', count($ids));
           mysqli_stmt_bind_param($stmt, $types, ...$ids);
           
           mysqli_stmt_execute($stmt);
           
           $results = mysqli_stmt_get_result($stmt);
           $rows = [];
           
           while ($row = mysqli_fetch_assoc($results)) {
               $rows[] = $row;
           }

           if (count($rows) > 0) {
               echo json_encode([
                   "status" => "success",
                   "message" => "Users found",
                   "users" => $rows,
               ]);
           } else {
               echo json_encode([
                   "status" => "error", 
                   "message" => "No users found",
               ]);
           }
           
           mysqli_free_result($results);
           mysqli_stmt_close($stmt);
           
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