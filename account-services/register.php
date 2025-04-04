<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once('../connect.php');

// Receive JSON data from the request
$requesjson = file_get_contents('php://input');
$data = json_decode($requesjson, true);
$aboutSuggestions = [
    'I feed kids rocks.',
    "Hi there!",
    'That which lies ahead, does not yet exist.',
    'Those who look with clouded eyes, see nothing but shadows.',
    'Beware, I have cravings.',
    'A touching reunion.',
];
$default_Aim = [
    'Become someone I can be proud of.',
    'Surround myself with honorable people.',
    'Create a less chaotic environment around me.',
    'Be there for my loved ones.',
    'Achieve academic excellence.',
    'Push beyond my limits.',
    'Create memories I can cherish.',
    'Form genuine, meaningful connections.',
    'Find my clear path.',
    'Learn from the mistakes of others.',
    'Refuse to follow the crowd blindly.',
    'Develop original ideas and innovative solutions.',
    'Expand my knowledge, skills, and abilities.',
    'Find people who resonate with my values and aspirations.',
    'Share my passions with others.',
    "Offer guidance on subjects I'm well informed.",
    'Put an end to procrastination.',
    'Finish what I start and check off tasks on my to-do list.'
];


if ($data !== null) {
    // Received data:
    $username = addslashes(strip_tags($data['username']));
    $rawPassword = addslashes(strip_tags($data['password']));

    // Check if username already exists
    $checkSql = 'SELECT * FROM user WHERE username = ?';
    $checkStmt = mysqli_prepare($conn, $checkSql);
    mysqli_stmt_bind_param($checkStmt, 's', $username);
    mysqli_stmt_execute($checkStmt);
    $result = mysqli_stmt_get_result($checkStmt);
    
    if (mysqli_num_rows($result) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        exit();
    }

    // Generate data:
    $salt = bin2hex(random_bytes(16)); // Generate a secure salt 
    $nickname = $username; // Set nickname same as username
    $dateJoined = date('Y-m-d H:i:s'); // Get current date and time
    $about = $aboutSuggestions[rand(0, count($aboutSuggestions) - 1)];
    $accessToken = hash('sha256', uniqid() . rand());

    // Hash the password with the salt
    $hashedPass = hash('sha256', $rawPassword . $salt);

    // SQL query to insert the user into the database
    $sql = 'INSERT INTO user (username, hashedPassword, nickname, about, dateJoined, salt, accessToken) VALUES (?, ?, ?, ?, ?, ?, ?)';
    
    try {
        // Prepare the statement to prevent SQL injection
        $verifiedStatement = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($verifiedStatement, 'sssssss', $username, $hashedPass, $nickname, $about, $dateJoined, $salt, $accessToken);

        // Execute the statement
        if (mysqli_stmt_execute($verifiedStatement)) {
            // Fetch the last inserted ID (auto-incremented user ID)
            $id = mysqli_insert_id($conn);


            
            // Insert into profile table with default values
            $profileSQL = 'INSERT INTO profile (id, fieldOfStudyWork, aim, otherInterests) VALUES (?, ?, ?, ?)';
            $defaultFieldOfStudyWork = 'Unspecified'; 
            $defaultAim = $default_Aim[rand(0,count($default_Aim))]; 
            $defaultOtherInterests = 'Unspecified'; 

            // Prepare and bind parameters for profile insertion
            $profileStmt = mysqli_prepare($conn, $profileSQL);
            mysqli_stmt_bind_param($profileStmt, 'isss', $id, $defaultFieldOfStudyWork, $defaultAim, $defaultOtherInterests);
            
            // Execute the profile insert
            if (mysqli_stmt_execute($profileStmt)) {
                // Respond with the user details and profile ID
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Registered successfully',
                    'id' => $id,
                    'username' => $username,
                    'nickname' => $nickname,
                    'about' => $about,
                    'dateJoined' => $dateJoined,
                    'token' => $accessToken,
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Error creating profile']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid format']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}

// Close the database connection
mysqli_close($conn);
?>
