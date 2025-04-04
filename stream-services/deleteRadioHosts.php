<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['hosts']) && is_array($data['hosts'])) {
    $hosts = $data['hosts']; //an array of idHost to delete
    $deleteCount = 0;
    foreach ($hosts as $idHost) {
        $idHost = intval($idHost); //make sure its an int
        $sql = "DELETE FROM `ss_radiohost` WHERE `idHost` = $idHost";
        if (mysqli_query($conn, $sql)) {
            $deleteCount++;
        } else {
            $errors[] = "Error deleting radio host with idHost = $idHost: " . mysqli_error($conn);
        }
    }

    if ($deleteCount > 0) {
        echo json_encode(["success" => "$deleteCount hosts deleted successfully"]);
    } else {
        echo json_encode(["error" => "No radio hosts deleted", "Causes:" => $errors]);
    }
} else {
    echo json_encode(["error" => "Required data not provided"]);
}

mysqli_close($conn);
?>
