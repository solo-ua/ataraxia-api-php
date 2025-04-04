<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php');

//get json containing multiple inserts of hosts
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['hosts'])) {
    $hosts = $data['hosts']; 
    $allowedFields = ['name', 'country', 'getTrackInfoUrl', 'trackTitle', 'trackArtist', 'trackCover'];
    $insertValues = [];

    //here we make sure every value is set accordingly to the allowed fields
    foreach ($hosts as $host) {
        //make sure that the required fields are present
        if (!isset($host['name'],$host['country'],$host['getTrackInfoUrl'],$host['trackTitle'],$host['trackArtist'],$host['trackCover'])) {
            echo json_encode(["error" => `Missing required fields.`]);
            exit;
        }
        $name = $host['name'];
        $country = $host['country'];
        $getTrackInfoUrl = $host['getTrackInfoUrl'];
        $trackTitle = $host['trackTitle'];
        $trackArtist = $host['trackArtist'];
        $trackCover = $host['trackCover'];

        //add values to the insert values array
        $insertValues[] = "('$name','$country','$getTrackInfoUrl','$trackTitle','$trackArtist','$trackCover')";
    }

    //combine
    if (count($insertValues) > 0) {
        $valuesString = implode(", ", $insertValues);
        $sql = "INSERT INTO `ss_radiohost`(`name`, `country`, `getTrackInfoUrl`,`trackTitle`,`trackArtist`,`trackCover`) VALUES $valuesString";

        //yipee
        if (mysqli_query($conn, $sql)) {
            echo json_encode(["success" => "Radio Hosts inserted successfully"]);
        } else {
            echo json_encode(["error" => "Error inserting Radio Hosts: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["error" => "No valid hosts to insert"]);
    }
} else {
    echo json_encode(["error" => "Required data not provided"]);
}

// Close the connection
mysqli_close($conn);
?>
