<?php






$postString = http_build_query($_POST);
echo $postString;

//$headers = getallheaders();
$contentType = isset($_SERVER["HTTP_CONTENT_TYPE"]) ? $_SERVER["HTTP_CONTENT_TYPE"] : null;




if (isset($headers["Content-Type"]) && strpos($headers["Content-Type"], "multipart/form-data") !== false) {
    $boundary = substr($headers["Content-Type"], strpos($headers["Content-Type"], "boundary=") + 9);

    $rawData = file_get_contents("php://input");
    $boundaryData = "--" . $boundary . "\r\n";
    $formData = array();

    $parts = explode($boundaryData, $rawData);

    foreach ($parts as $part) {
        if (empty($part)) {
            continue;
        }

        $partArray = array();

        list($rawHeaders, $partBody) = explode("\r\n\r\n", $part, 2);
        $headers = explode("\r\n", $rawHeaders);

        foreach ($headers as $header) {
            $headerParts = explode(":", $header, 2);
            if (count($headerParts) == 2) {
            $partArray[trim($headerParts[0])] = trim($headerParts[1] ?? '');
            }
        }

        if (isset($partArray["Content-Disposition"])) {
            $partData = array();

            preg_match('/^form-data;.*name="([^"]+)"/', $partArray["Content-Disposition"], $matches);
            $partData["name"] = $matches[1];

            if (preg_match('/filename="([^"]+)"/', $partArray["Content-Disposition"], $matches)) {
            $partData["filename"] = $matches[1];
            $partData["type"] = $partArray["Content-Type"];
            $partData["data"] = $partBody;
            } else {
            $partData["data"] = $partBody;
            }

            $formData[] = $partData;
        }
    }

    // process the form data here
    foreach ($formData as $partData) {
        switch ($partData["name"]) {
            case "tagid":
                $itemTagid = mysqli_real_escape_string($mysqli, filter_var($partData["data"], FILTER_SANITIZE_STRING));
                break;
            case "keyid":
                $itemKeyid = mysqli_real_escape_string($mysqli, filter_var($partData["data"], FILTER_SANITIZE_STRING));
                break;
            case "description":
                $itemDescription = mysqli_real_escape_string($mysqli, filter_var($partData["data"], FILTER_SANITIZE_STRING));
                break;
            case "oldimg":
                $oldimg = mysqli_real_escape_string($mysqli, filter_var($partData["data"], FILTER_SANITIZE_STRING));
                break;
            case "new-file":
                $newimg = 'data:' . mysqli_real_escape_string($mysqli, filter_var($partData["type"], FILTER_SANITIZE_STRING)) . ';base64,' . base64_encode($partData["data"]);
                break;
            default:
                break;
        }
    }

    if (isset($oldimg) && !isset($newimg)){
        $newimg = $oldimg;
    }

/*
if (isset($partData['tagid'])) {
    $itemTagid = mysqli_real_escape_string($mysqli, filter_var($partData["tagid"], FILTER_SANITIZE_STRING));
}

if (isset($partData['keyid'])) {
    $itemKeyid = mysqli_real_escape_string($mysqli, filter_var($partData["keyid"], FILTER_SANITIZE_STRING));
}

if (isset($partData['description'])) {
    $itemDescription = mysqli_real_escape_string($mysqli, filter_var($partData["description"], FILTER_SANITIZE_STRING));
}

if (isset($partData['oldimg'])) {
    $oldimg = mysqli_real_escape_string($mysqli, filter_var($partData["oldimg"], FILTER_SANITIZE_STRING));
}

if (isset($partData["new-file"]) ) {
    if (isset($_FILES["new-file"]) && $_FILES["new-file"]["error"] == 0) { 
        $newimage = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($_FILES["new-file"]["tmp_name"]));
    } else {
        $newimage = $oldimg;
    }
} else {
    // handle regular form field
    // ...
}
*/
    // Execute a UPDATE query to update the item in the database
    $query = "UPDATE tItems SET keyid='$itemKeyid', description='$itemDescription', image='$newimg' WHERE tagid='$itemTagid'";
    if (!$mysqli->query($query)) {
        header("HTTP/1.1 403 Forbidden");
        header("Content-Type: application/json");
        echo json_encode(["error" => $mysqli->error]);
        die();
    }
    // Return a success message to the AJAX request
    //header("Content-Type: application/json");
    //echo json_encode(["success" => "Item updated successfully"]);
    header("Location: protected.php");
} else {
    header("HTTP/1.1 400 Bad Request");
    header("Content-Type: application/json");
    echo json_encode(["error" => "Invalid Content-Type header"]);
}



// Get the form data
/*
$itemTagid = mysqli_real_escape_string($mysqli, filter_var($_POST["tagid"], FILTER_SANITIZE_STRING));
$itemKeyid = mysqli_real_escape_string($mysqli, filter_var($_POST["keyid"], FILTER_SANITIZE_STRING));
$itemDescription = mysqli_real_escape_string($mysqli, filter_var($_POST["description"], FILTER_SANITIZE_STRING));
$oldimg = mysqli_real_escape_string($mysqli, filter_var($_POST["oldimg"], FILTER_SANITIZE_STRING));
if (isset($_FILES["new-file"]) && $_FILES["new-file"]["error"] == 0) { 
    $newimage = 'data:image/jpeg;base64,' . base64_encode(file_get_contents($_FILES["new-file"]["tmp_name"]));
} else {
    $newimage = $oldimg;
}*/
