<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once('../connect.php');

$requestJson = file_get_contents('php://input');
$data = json_decode($requestJson, true);
$detectedAccount;

if ($data !== null) {
    $username = addslashes(strip_tags($data['username']));
    $password = addslashes(strip_tags($data['password'])); // received raw

    // Get account info:
    if (isset($username)) {
        try {
            // Verify the statements to protect from injection attacks
            $sql = "SELECT hashedPassword, salt, nickname, about, dateJoined, accessToken FROM user WHERE username = ?";
            $verifiedStatement = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($verifiedStatement, 's', $username);
            // Now you execute it
            mysqli_stmt_execute($verifiedStatement);
            // Fetch the result
            $results = mysqli_stmt_get_result($verifiedStatement);
            $row = mysqli_fetch_assoc($results);
            // Check if user was found
            if ($row) {
                // User found so grab the salt and hashed password
                $detectedAccount = $row;
                $storedHashedPassword = $detectedAccount['hashedPassword'];
                $storedSalt = $detectedAccount['salt'];
                $accessToken = $detectedAccount['accessToken'];
                
                // Now hash the provided password using the same salt
                $hashedInputPassword = hash('sha256', $password . $storedSalt);  

                // Check if the hashed password matches the one in the database
                if ($hashedInputPassword === $storedHashedPassword) { // Password matches, return success response

                    // Data that will be sent back to the logged in User:
                    $nickname = $detectedAccount['nickname'];
                    $about = $detectedAccount['about'];
                    $dateJoined = $detectedAccount['dateJoined'];

                    echo json_encode([
                        'message' => 'Login successful', 
                        'username' => $username, 
                        'nickname' => $nickname, 
                        'about' => $about, 
                        'dateJoined' => $dateJoined,
                        'token' => $accessToken,
                    ]);
                } else {
                    // Password doesn't match
                    echo json_encode(['error' => 'Invalid username or password']);
                }
            } else {
                echo json_encode(['error' => 'Invalid username or password']);
            }

            mysqli_free_result($results);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
