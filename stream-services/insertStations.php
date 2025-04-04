<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php');

//get json containing multiple inserts of stations
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['stations'])) {
    $stations = $data['stations']; // Expected as an array of station data

    $allowedFields = ['idHostedBy', 'idStation', 'name', 'streamUrl'];
    $insertValues = [];

    //here we make sure every value is set accordingly to the allowed fields
    foreach ($stations as $station) {
        //make sure that the required fields are present
        if (!isset($station['idHostedBy'], $station['idStation'], $station['name'], $station['streamUrl'])) {
            echo json_encode(["error" => `Missing required fields.`]);
            exit;
        }
        $idHostedBy = $station['idHostedBy'];
        $idStation = $station['idStation'];
        $name = $station['name'];
        $streamUrl = $station['streamUrl'];

        //add values to the insert values array
        $insertValues[] = "($idHostedBy, $idStation, '$name', '$streamUrl')";
    }

    //combine
    if (count($insertValues) > 0) {
        $valuesString = implode(", ", $insertValues);
        $sql = "INSERT INTO `ss_stations`(`idHostedBy`, `idStation`, `name`, `streamUrl`) VALUES $valuesString";

        //yipee
        if (mysqli_query($conn, $sql)) {
            echo json_encode(["success" => "Records inserted successfully"]);
        } else {
            echo json_encode(["error" => "Error inserting records: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["error" => "No valid stations to insert"]);
    }
} else {
    echo json_encode(["error" => "Required data not provided"]);
}

// Close the connection
mysqli_close($conn);
?>
