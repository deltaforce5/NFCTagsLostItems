<?php
require('./reserved.php');

// Start session
session_start();

// Connect to database
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query the database to check if the users table is empty
$query = "SELECT COUNT(*) as count FROM tUsers";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);

// Retrieve user credentials from login form
$username = mysqli_real_escape_string($conn, filter_var($_POST['username'], FILTER_SANITIZE_STRING));
$password = mysqli_real_escape_string($conn, filter_var($_POST['password'], FILTER_SANITIZE_STRING));

if ($row['count'] == 0) {
    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the new user into the database
    $query = "INSERT INTO tUsers (username, password) VALUES ('$username', '$hashed_password')";
    if (mysqli_query($conn, $query)) {
        echo "New admin user created successfully, with entered password. ";
        echo "<br><br><a href=\"./login.php\">Go back to login</a>";
        exit;
    } else {
        echo "Error creating new user: " . mysqli_error($conn);
        exit;
    }
}

// Query the database for the user
$query = "SELECT * FROM tUsers WHERE username='$username'";
$result = mysqli_query($conn, $query);

// Check if the user exists
if (mysqli_num_rows($result) == 1) {
    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (password_verify($password, $user['password'])) {
        // Password is correct, start a new session
        session_regenerate_id();
        $_SESSION['loggedin'] = TRUE;
        $_SESSION['username'] = $username;
        header('Location: protected.php');
    } else {
        // Password is incorrect, redirect back to login page
        header('Location: login.php');
    }
} else {
    // User not found, redirect back to login page
    header('Location: login.php');
}

// Close database connection
mysqli_close($conn);
?>
