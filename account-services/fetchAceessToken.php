<?php
    // require 'vendor/autoload.php';
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Access-Control-Allow-Origin: *");

    include_once('../connect.php');
    
    if($_SERVER['REQUEST_METHOD']== 'POST' && isset($_POST['authKey'])){ //TODO fix 
        $accessToken = $_POST['authKey'];
        //get access token from db:
        $sql = 'SELECT accessToken FROM user where accessToken = ?';
        try{
            $verifiedStatement =mysqli_prepare($conn,$sql);
            mysqli_stmt_bind_param($verifiedStatement, 's', $accessToken);
            mysqli_stmt_execute($verifiedStatement);
            $results = mysqli_stmt_get_result($verifiedStatement);
            $row = mysqli_fetch_assoc($results);
            if ($row) {
                $filePath = $_POST['filePath'];
                $fileName = $_POST['fileName'];

                // Call the Node.js API
                $nodeApiUrl = 'https://your-node-app.herokuapp.com/upload'; // URL to Node.js service

                $postData = json_encode(array(
                    'filePath' => $filePath,
                    'fileName' => $fileName
                ));

                $ch = curl_init($nodeApiUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Ensures that the response from the Node.js API is returned as a string instead of being output directly.
                curl_setopt($ch, CURLOPT_POST, true); //Specifies that this request is a POST request.
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);//Sets the data to be sent in the POST request (i.e., the JSON payload).
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',// Sets the HTTP headers for the request. Content-Type: application/json indicates that the data is in JSON format. 
                    'Content-Length: ' . strlen($postData) //Content-Length is the length of the data being sent.
                ));

                $response = curl_exec($ch); //curl_exec executes the cURL session and stores the response from the Node.js API in $response.
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); //curl_getinfo retrieves the HTTP status code from the response. This helps to determine if the request was successful.
                 curl_close($ch); // closes the cURL session to free up system resources.

                if ($httpCode == 200) {
                    echo 'File uploaded successfully!';
                } else {
                    echo 'Error uploading file: ' . $response;
                }
            }else{
                //dont run the node js and say the authKEY/access token is false
            }

        }catch(Exception $e){
            echo ($e->getMessage());
        }
        //got the key now use it 
    }
?>