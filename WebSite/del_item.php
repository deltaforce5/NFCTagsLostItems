<?php
require('./reserved.php');

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
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

// Get the form data
$itemTagid = mysqli_real_escape_string($mysqli, filter_var($_POST["t"], FILTER_SANITIZE_STRING));

// Execute a DELETE query to remove the item from the database
$query = "DELETE FROM tItems WHERE tagid='$itemTagid'";
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
echo json_encode(["success" => "Item deleted successfully"]);
?>