<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    include_once('../connect.php');
$inputData = json_decode(file_get_contents("php://input"), true);

if (isset($inputData['id'], $inputData['fieldsToUpdate'])) {
    $id = intval($inputData['id']);
    $fieldsToUpdate = $inputData['fieldsToUpdate'];

        // Whitelists for each table to prevent SQL injection
        $allowedFieldsProfile = ['fieldOfStudyWork', 'aim', 'otherInterests'];
        $allowedFieldsAvatar = ['pfp', 'avatar'];
        $allowedFieldsHobbies = ['hobby'];
        $allowedFieldsGenres = ['genre'];

        $queries = [];

        foreach ($fieldsToUpdate as $table => $updates) {
            foreach ($updates as $field => $value) {
                if (is_array($value)) {
                    // Handle multi-valued fields (e.g., hobbies, genres, avatars)
                    foreach ($value as $multiValue) {
                        $escapedValue = mysqli_real_escape_string($conn, $multiValue);

                        if ($table === 'profile_hobbies' && $field === 'hobby') {
                            $queries[] = "INSERT INTO profile_hobbies (id, hobby) VALUES ($id, '$escapedValue') ON DUPLICATE KEY UPDATE hobby = '$escapedValue'";
                        } elseif ($table === 'profile_musicGenre' && $field === 'genre') {
                            $queries[] = "INSERT INTO profile_musicGenre (id, genre) VALUES ($id, '$escapedValue') ON DUPLICATE KEY UPDATE genre = '$escapedValue'";
                        } elseif ($table === 'profile_avatar' && in_array($field, $allowedFieldsAvatar)) {
                            $checkIfExists = mysqli_query($conn, "SELECT id FROM profile_avatar WHERE id = $id");
                            if (mysqli_num_rows($checkIfExists) === 0) {
                                $queries[] = "INSERT INTO profile_avatar (id, $field) VALUES ($id, '$escapedValue')";
                            } else {
                                $queries[] = "UPDATE profile_avatar SET $field = '$escapedValue' WHERE id = $id";
                            }
                        } else {
                            echo json_encode(["error" => "Invalid field or table name"]);
                            exit;
                        }
                    }
                } else {
                    // Handle single-valued fields (e.g., profile fields)
                    $escapedValue = mysqli_real_escape_string($conn, $value);
                    if ($table === 'profile' && in_array($field, $allowedFieldsProfile)) {
                        $queries[] = "UPDATE profile SET $field = '$escapedValue' WHERE id = $id";
                    } else {
                        echo json_encode(["error" => "Invalid field or table name"]);
                        exit;
                    }
                }
            }
        }

        $success = true;
        foreach ($queries as $query) {
            if (!mysqli_query($conn, $query)) {
                $success = false;
                $error = mysqli_error($conn);
                break;
            }
        }

        if ($success) {
            echo json_encode(["status" => "success", "message" => "Fields updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message"=> "Failed to update fields: $error"]);
        }
    } else {
        echo json_encode(["status" => "error", "message"=> "Required data not provided"]);
    }

    mysqli_close($conn);
?>
