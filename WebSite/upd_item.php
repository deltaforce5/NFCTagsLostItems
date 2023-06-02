<?php
require("./reserved.php");

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== TRUE) {
    // If the user is not logged in, return an error message and stop the script execution
    header("HTTP/1.1 401 Unauthorized");
    header("Content-Type: application/json");
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 405 Method Not Allowed");
    header("Content-Type: application/json");
    echo json_encode(["error" => "Use of methods other than POST are not allowed"]);
    exit;
}


// Establish a connection to the MySQL server
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// Check for errors in connection
if ($mysqli->connect_errno) {
    header("HTTP/1.1 500 Internal Server Error");
    header("Content-Type: application/json");
    echo json_encode(["Failed to connect to MySQL: " . $mysqli->connect_error]);
    exit;
}

// Process the payload
$rawData = file_get_contents("php://input");
$boundary = substr($rawData, 0, strpos($rawData, "\r\n"));

$parts = array_slice(explode($boundary, $rawData), 1, -1);
$formData = array();

foreach ($parts as $part) {
    $part = ltrim($part, "\r\n");
    list($header, $body) = explode("\r\n\r\n", $part, 2);

    if (preg_match('/Content-Disposition:.*name="([^"]+)"; filename="([^"]+)"/i', $header, $matches)) {
        $name = $matches[1];
        $filename = $matches[2];
        
        $fileData = substr($body, 0, strlen($body) - 2);
    
        // validate the file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $fileType = $finfo->buffer($fileData);

        if ($fileType != "image/png" && $fileType != "image/jpeg") {
            throw new Exception("Invalid file type: " . $fileType);
        }

        $newimg = 'data:' . mysqli_real_escape_string($mysqli, filter_var($fileType, FILTER_SANITIZE_STRING)) . ';base64,' . base64_encode($fileData);

    } else if (preg_match('/Content-Disposition:.*name="([^"]+)"/i', $header, $matches)) {
        $name = $matches[1];
        $value = substr($body, 0, strlen($body) - 2);

        $formData[$name] = $value;
    }
}


$itemTagid = mysqli_real_escape_string($mysqli, filter_var($formData["tagid"], FILTER_SANITIZE_STRING));
$itemKeyid = mysqli_real_escape_string($mysqli, filter_var($formData["keyid"], FILTER_SANITIZE_STRING));
$itemDescription = mysqli_real_escape_string($mysqli, filter_var($formData["description"], FILTER_SANITIZE_STRING));
$oldimg = mysqli_real_escape_string($mysqli, filter_var($formData["oldimg"], FILTER_SANITIZE_STRING));

if (!isset($newimg) && isset($oldimg)) {
    $newimg = $oldimg;
}

// Execute a UPDATE query to update the item in the database
$query = "UPDATE tItems SET keyid='$itemKeyid', description='$itemDescription', image='$newimg' WHERE tagid='$itemTagid'";
if (!$mysqli->query($query)) {
    header("HTTP/1.1 403 Forbidden");
    header("Content-Type: application/json");
    echo json_encode(["error" => $mysqli->error]);
    die();
}


// Close the database connection
$mysqli->close();

// Return a success message to the AJAX request
header("HTTP/1.1 200 OK");
header("Content-Type: application/json");
echo json_encode(["success" => "Item updated successfully"]);

?>