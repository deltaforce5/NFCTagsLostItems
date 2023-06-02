<?php
require('./reserved.php');

$tagid = $_GET['t'];
$keyid = $_GET['k'];
    

if (isset($_GET['t']) && isset($_GET['k'])) {
        $tagid = filter_var($_GET['t'], FILTER_SANITIZE_STRING);
        $keyid = filter_var($_GET['k'], FILTER_SANITIZE_STRING);

	// Create connection
	$conn = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	$sql = "SELECT image, description FROM tItems WHERE tagid = '" . $tagid . "' AND keyid = '" . $keyid . "';";
	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
		//echo "column1: " . $row["image"] . "<br>";
		echo "<img src=\"" . $row["image"] . "\" /><br />" . $row["description"] . "<br />";
	    }
	} else {
	    echo "(IT) Nessuna informazione disponibile. (EN) No available data. <br />";
	}
	$conn->close();

} else {
    echo "Invalid Request";
}

?>
