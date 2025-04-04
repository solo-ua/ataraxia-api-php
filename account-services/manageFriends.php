<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

include_once('./connect.php');

// Receive JSON data from the request
$requestjson = file_get_contents('php://input');
$data = json_decode($requestjson, true);

function filterGetList($requiredId, $rows) {
    $result = []; // Array to store matched friends
    foreach ($rows as $row) {
        // Check if $requiredId matches idUser1 or idUser2
        if (isset($row['idUser1']) && $row['idUser1'] === $requiredId) {
            $submitItem = [
                'matchedId' => $row['idUser2'], // The friend is the one in idUser2
                'isReceiver' => false
            ];
            $result[] = $submitItem;
        } elseif (isset($row['idUser2']) && $row['idUser2'] === $requiredId) {
            $submitItem = [
                'matchedId' => $row['idUser1'], // The friend is the one in idUser1
                'isReceiver' => true
            ];
            $result[] = $submitItem;
        }
    }
    return $result; // Return the array of matched friend IDs
}

function filter($requiredId, $rows) {
    $result = []; // Array to store non - matching IDs
    foreach ($rows as $row) {
        echo json_encode([$row]);
        // Check if $requiredId matches idUser1 or idUser2 in the row
        if (isset($row[1]) && $row[1] === $requiredId) {
            $submitItem = [
                'row' => $row,
                'matchedId' => $row[2], // The ID from the "other" column (idUser2)
                'isReceiver' => false // Since $requiredId was in idUser1, they are the sender
            ];
            $result[] = $submitItem;
        } elseif (isset($row[2]) && $row[2] === $requiredId) {
            $submitItem = [
                'row' => $row,
                'matchedId' => $row[1], // The ID from the "other" column (idUser1)
                'isReceiver' => true // Since $requiredId was in idUser2, they are the receiver
            ];
            $result[] = $submitItem;
        }
    }
    
    return $result; // Return the array of matched IDs
}
if ($data !== null) {
    // Sanitize inputs
    $action = $data['action'];
    $userId = $data['userId'];
    $friendId = isset($data['friendId']) ? $data['friendId'] : null;
    // Prepare SQL query based on action
    switch ($action) {
        case 'add':
            $uniqueId = "{$userId}-{$friendId}";
            $sql = "INSERT INTO `user_friends`(`idRelation`,`idUser1`, `idUser2`, `status`, `createdAt`, `updatedAt`) 
                    VALUES (?, ?, ?, 'pending', NOW(), NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sii', $uniqueId, $userId, $friendId);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Friend request sent']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to send friend request']);
            }
            break;

        case 'remove':
            $sql = "DELETE FROM `user_friends` WHERE `idRequest` = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'i', $uniqueId);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Friend removed']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to remove friend']);
            }
            break;

        case 'getPending':
            $sql = "SELECT * FROM `user_friends` WHERE (`idUser1` = ? OR `idUser2` = ?) AND `status` = 'pending'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId); 
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $pendingList = []; // Array to store all friends
        
            // Loop through all rows in the result set
            while ($row = mysqli_fetch_assoc($result)) {
                $pendingList[] = $row; // Add each row to the friend list array
            }
        
            if (count($pendingList) > 0) {
                // Pass the full list of friends to the filter function
                $filteredList = filterGetList($userId, $pendingList);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Pending list',
                    'data' => $filteredList
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'List Empty',
                ]);
            }            
        break;
        case 'getFriendList':
            $sql = "SELECT * FROM `user_friends` WHERE (`idUser1` = ? OR `idUser2` = ?) AND `status` = 'friend'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $userId); 
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            $friendList = []; // Array to store all friends
        
            // Loop through all rows in the result set
            while ($row = mysqli_fetch_assoc($result)) {
                $friendList[] = $row; // Add each row to the friend list array
            }
        
            if (count($friendList) > 0) {
                // Pass the full list of friends to the filter function
                $filteredList = filterGetList($userId, $friendList);
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Friend list',
                    'data' => $filteredList
                ]);
            } else {
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'List Empty',
                ]);
            }            
         break;
        case 'acceptRequest':
            $sql = "UPDATE `user_friends` SET `status` = 'friend', `updatedAt` = NOW() WHERE `idUser2` = ? AND `idUser1` = ? AND `status` = 'pending'"; // Since User2 is the reciever so they either accept or decline
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $friendId); // So userId is the one who will decline the idUser1 who is the same id as friendId
            if (mysqli_stmt_execute($stmt)) {
                // Fetch the updated row after the status change
                $fetchSql = "SELECT * FROM `user_friends` WHERE `idUser2` = ? AND `idUser1` = ?";
                $fetchStmt = mysqli_prepare($conn, $fetchSql);
                mysqli_stmt_bind_param($fetchStmt, 'ii', $userId, $friendId);
                mysqli_stmt_execute($fetchStmt);
                $result = mysqli_stmt_get_result($fetchStmt);
                $updatedRequest = mysqli_fetch_assoc($result);

                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Friend request approved',
                    'data' => $updatedRequest
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to approve friend request']);
            }
            break;

        case 'declineRequest':
            $sql = "DELETE FROM `user_friends` WHERE `idUser2` = ? AND `idUser1` = ? AND `status` = 'pending'";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'ii', $userId, $friendId);
            if (mysqli_stmt_execute($stmt)) {
                echo json_encode(['status' => 'success', 'message' => 'Friend request declined']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to decline friend request']);
            }
            break;
        default:
            echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
            break;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No data received']);
}

mysqli_close($conn);
?>
