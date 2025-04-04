<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT"); // Add PUT here
header("Access-Control-Allow-Headers: Content-Type, Authorization");
//TODO update to PDO
include ('../connect.php'); //include it once later on

if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
	$variables = json_decode(file_get_contents("php://input"), true); //decode JSON input

    if (isset($variables['id'])) {
        //for building the SQL query
        $fields = [];
        $params = [];

        //map variables and check if they exist
        if (!empty($variables['name'])) {
            $fields[] = 'name = ?';
            $params[] = $variables['name'];
        }
        if (!empty($variables['country'])) {
            $fields[] = 'country = ?';
            $params[] = $variables['country'];
        }
        if (!empty($variables['fetchTrackInfoUrl'])) {
            $fields[] = 'getTrackInfoUrl = ?';
            $params[] = $variables['fetchTrackInfoUrl'];
        }
        if (!empty($variables['trackTitle'])) {
            $fields[] = 'trackTitle = ?';
            $params[] = $variables['trackTitle'];
        }
        if (!empty($variables['trackArtist'])) {
            $fields[] = 'trackArtist = ?';
            $params[] = $variables['trackArtist'];
        }
        if (!empty($variables['trackAlbum'])) {
            $fields[] = 'trackCover = ?';
            $params[] = $variables['trackAlbum'];
        }
        //only proceed if there are fields to update
        if (!empty($fields)) {
            // add the ID parameter to the params
            $params[] = $variables['id'];

            //create the final SQL query
            $sql = 'UPDATE `ss_radiohost` SET ' . implode(', ', $fields) . ' WHERE `idHost` = ?';
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, str_repeat('s', count($params) - 1) . 'i', ...$params);

            if ($stmt->execute($params)) {
                echo json_encode(['status' => 'success', 'message' => 'Host updated successfully.']);
            } else {
                // Error occurred
                echo json_encode(['status' => 'error', 'message' => 'Error occurred: could not update the host.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No fields provided for update']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'ID not provided']);
    }
}
mysqli_close($conn);
?>