<?php
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");
    header("Content-Type: application/json");

    include_once('../connect.php');

    $response = [];

    try {
        $profileQuery = "SELECT * FROM profile";
        $profileResult = $conn->query($profileQuery);

        $profiles = [];

        while ($profile = $profileResult->fetch_assoc()) {
            $profileId = $profile['id'];

            // Fetch genres
            $genresQuery = "SELECT genre FROM profile_musicGenre WHERE id = '$profileId'";
            $genresResult = $conn->query($genresQuery);
            $genres = [];
            while ($genre = $genresResult->fetch_assoc()) {
                $genres[] = $genre['genre'];
            }

            // Fetch hobbies
            $hobbiesQuery = "SELECT hobby FROM profile_hobbies WHERE id = '$profileId'";
            $hobbiesResult = $conn->query($hobbiesQuery);
            $hobbies = [];
            while ($hobby = $hobbiesResult->fetch_assoc()) {
                $hobbies[] = $hobby['hobby'];
            }

            // Fetch avatar details
            $avatarQuery = "SELECT pfp, avatar FROM profile_avatar WHERE id = '$profileId'";
            $avatarResult = $conn->query($avatarQuery);
            $avatars = $avatarResult->fetch_assoc();

            // Organize data
            $profiles[] = [
                'id' => $profile['id'],
                'fieldOfStudyWork' => $profile['fieldOfStudyWork'],
                'aim' => $profile['aim'],
                'otherInterests' => $profile['otherInterests'],
                'avatars' => $avatars,
                'hobbies' => $hobbies,
                'genres' => $genres
            ];
        }

        $response = ["success" => true, "profiles" => $profiles];
    } catch (Exception $e) {
        $response = ["success" => false, "message" => $e->getMessage()];
    }

    echo json_encode($response);
?>
