<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once('../connect.php');

//get the input as json
$requestJson = file_get_contents('php://input');
$data = json_decode($requestJson, true);

if ($data !== null) {
    $email = addslashes(strip_tags($data['email']));
    $password = addslashes(strip_tags($data['password'])); 

    if ($email && $password) {
        try {
            $sql = "SELECT idAdmin, admin_username, admin_password FROM ataraxia_admins WHERE admin_email = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);

            $result = mysqli_stmt_get_result($stmt);
            $admin = mysqli_fetch_assoc($result);

            if ($admin) {
                if ($password === $admin['admin_password']) {
                    echo json_encode([
                        'message' => 'Login successful',
                        'idAdmin' => $admin['idAdmin'],
                        'admin_username' => $admin['admin_username'],
                        'admin_email' => $email
                    ]);
                } else { //wrong pass
                    echo json_encode(['error' => 'Invalid email or password']);
                }
            } else { //wrong email
                echo json_encode(['error' => 'Invalid email or password']);
            }
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['error' => 'Email and password are required']);
    }
}
?>
