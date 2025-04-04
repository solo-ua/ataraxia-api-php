<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

include_once('../connect.php');

$data = json_decode(file_get_contents('php://input'), true);
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if (isset($data['idSS'], $data['fieldsToUpdate'], $data['newValues'])) {
        $id = intval($data['idSS']);
        $fieldsToUpdate = $data['fieldsToUpdate']; // Expected as an array
        $newValues = $data['newValues']; // Expected as an array

        // Whitelist of allowed fields to prevent SQL injection
        $allowedFields = ['name', 'idStation', 'idHostedBy', 'streamUrl'];
        $updateParts = [];
        //make sure fieldsToUpdate and newValues are the same length
        if (count($fieldsToUpdate) !== count($newValues)) {
            echo json_encode(["error" => "Mismatched fields and values"]);
            exit;
        }

        //make sure the field name is the same as whitelisted ones
        foreach ($fieldsToUpdate as $key => $field) {
            if (!in_array($field, $allowedFields)) { 
                echo json_encode(["error" => "Invalid field name: " . htmlspecialchars($field)]);
                exit;
            }

            //ensure the datatype of value is appropriate
            $value = $newValues[$key];
            if (in_array($field, ['idSS', 'idHostedBy', 'idStation'])) {
                $valueDT = intval($value);
            } else {
                $valueDT = "'" . $value . "'";
            }

            $updateParts[] = "`$field` = $valueDT";
        }

        //join the array into executable string 
        $updateString = implode(", ", $updateParts);

        // Construct and execute the SQL query
        $sql = "UPDATE `ss_stations` SET $updateString WHERE `idSS` = $id";

        if (mysqli_query($conn, $sql)) {
            echo json_encode(["success" => "Record updated successfully"]);
        } else {
            echo json_encode(["error" => "Error updating record: " . mysqli_error($conn)]);
        }
    } else {
        echo json_encode(["error" => "Required data not provided"]);
    }
}
// Close the connection
mysqli_close($conn);
?>
