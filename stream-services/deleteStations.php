<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['stations']) && is_array($data['stations'])) {
    $stations = $data['stations']; //an array of idSS to delete
    $deleteCount = 0;
    foreach ($stations as $idSS) {
        $idSS = intval($idSS); //make sure its an int
        $sql = "DELETE FROM `ss_stations` WHERE `idSS` = $idSS";
        if (mysqli_query($conn, $sql)) {
            $deleteCount++;
        } else {
            $errors[] = "Error deleting station with idSS = $idSS: " . mysqli_error($conn);
        }
    }

    if ($deleteCount > 0) {
        echo json_encode(["success" => "$deleteCount stations deleted successfully"]);
    } else {
        echo json_encode(["error" => "No stations deleted", "Causes:" => $errors]);
    }
} else {
    echo json_encode(["error" => "Required data not provided"]);
}

mysqli_close($conn);
?>
