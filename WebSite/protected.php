<?php
require('./reserved.php');

// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== TRUE) {
    header('Location: login.php');
    exit;
}

echo "<link href=\"site.css\" rel=\"stylesheet\">";

// Display welcome message
echo "<div class=\"main\">Welcome, " . $_SESSION['username'] . "!</div>";
?>
<div class="logout_btn">
  <form id="logout-form" method="POST" action="logout.php">
    <button type="submit">Logout</button>
  </form>
</div>


<br /><br /><h1>Items List</h1>
<br />

<!-- Overlay div for adding a new item -->
<div id="add-item-overlay">
  <div id="add-item-form-container">
    <h2>Add New Item</h2>
    <form id="add-item-form" method="POST" action="add_item.php">
      <label for="item-tag">tagid:</label>
      <input type="text" id="item-tag" name="item-tag" required>
      <div class="inline-box">
        <label for="item-key">keyid:</label>
        <img src="add.png" id="random-item-key" onclick="randomKey(document.getElementById('item-key'))" />
      </div>
      <input type="text" id="item-key" name="item-key" required>
      <label for="item-description">description:</label>
      <textarea id="item-description" name="item-description" required></textarea>
      <label for="item-image">image:</label>
      <input type="file" id="item-image" name="item-image" /></td>
      <button type="submit">Add Item</button>
    </form>
    <button type='button' id="close-add-item-overlay">Close</button>
  </div>
</div>


<!-- Overlay div to delete an item -->
<div class='overlay' id='delete-item-overlay'>"
  <div class='confirm-box'>
    <form id="delete-item-form" method='POST' action="del_item.php">
      <p>Are you sure you want to delete this row?</p>
      <input type='hidden' id='tagid_delete' name='tagid_delete' value=''>
      <button type='submit' class="delete-item-btn">Confirm deletion</button>
      <button type='button' id="close-delete-item-overlay">No</button>
    </form>
  </div>
</div>

<?php
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query the database for the table contents
$query = "SELECT * FROM tItems";
$result = mysqli_query($conn, $query);
$numRows = mysqli_num_rows($result);

echo "Total number of entries: " . $numRows;

// Display the table contents
echo "<table><tr>";
echo "<th>tagid</th>";
echo "<th>keyid</th>";
echo "<th>description</th>";
echo "<th>image</th>";
echo "<th><button id='add-item-btn'>Add Item</button></th>";
echo "</tr>";

while ($row = mysqli_fetch_assoc($result)) {

    // Check if the row is being edited
    $is_editing = isset($_POST['edit']) && $_POST['edit'] == $row['tagid'];
    echo "<tr>";

    if ($is_editing) {
        echo "<form method='POST' id='update-item-form'>";
        echo "<input type='hidden' name='tagid' value='" . $row['tagid'] . "' />";
        echo "<input type='hidden' name='oldimg' value='" . $row['image'] . "' />";
        echo "<td>" . $row['tagid'] . "</td>";
        echo "<td><input type='text' id='edit-item-keyid' name='keyid' value='" . $row['keyid'] . "'></td>";
        echo "<td><input type='text' id='edit-item-description' name='description' value='" . $row['description'] . "'></td>";
        echo "<td><image src=\"" . $row['image'] . "\" /><br />";
        echo "<label for='new-file'>Change image:</label>";
        echo "<input type='file' id='new-file' name='new-file' /></td>";
        echo "<td>";
        echo "<button type='submit' class='ok-btn' name='updact' value='Save'>Save</button>";
        echo "<button type='submit' class='cancel-btn' name='updact' value='Cancel'>Cancel</button>";
        echo "</td>";
        echo "</form>";
    } else {
        echo "<td>" . $row['tagid'] . "</td>";
        echo "<td>" . $row['keyid'] . "</td>";
        echo "<td>" . $row['description'] . "</td>";
        echo "<td><image src=\"" . $row['image'] . "\"</td>";
	      echo "<td>";
        echo "<form method='POST'>";
        echo "<input type='hidden' name='edit' value='" . $row['tagid'] . "'>";
        echo "<input type='submit' class='edit-item-btn' value='Edit'>";
        echo "</form>";
        echo "<button type='button' class='delete-item-btn' onclick='showOverlay(\"" . $row['tagid'] . "\")'>Delete</button>";
        echo "</td>";
    }
    echo "</tr>";
}
echo "</table>";



// Close database connection
mysqli_close($conn);
?>
<script>
var updForm = document.getElementById("update-item-form");

if (updForm != null) {
  document.getElementById("edit-item-keyid").focus();

  // When the user submits the form in the overlay div, delete the item from the database table
  updForm.addEventListener("submit", function(event) {
    event.preventDefault(); // Prevent the form from submitting normally
    
    const actionVal = event.submitter.value;
    const formData = new FormData(updForm);
    
    if(actionVal === "Cancel") {
      event.target.submit();
    } else if(actionVal === "Save") {
      var fileItem = formData.get('new-file');
      
      if (typeof(fileItem.files) != 'undefined')
        formData.append("file", fileItem.files[0]);

      // Send an AJAX request to delete the item from the database
      var xhr = new XMLHttpRequest();
      xhr.open("POST", "upd_item.php", true);
      xhr.setRequestHeader("Content-type", "multipart/form-data");
      xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
          // If the request was successful, hide the overlay div
          delItemOverlay.style.display = "none";

          // Display a success message to the user
          var response = JSON.parse(xhr.responseText);
          alert(response.success);
          window.location = "./protected.php";
        }
        if (xhr.readyState == 4 && xhr.status > 399) {
          // Display an error message to the user
          var response = JSON.parse(xhr.responseText);
          alert(response.error);
        }
      };
      xhr.send(formData);
    } else {
      // do nothing!
    }
  });
}

/* JavaScript code to handle the add overlay */
var addItemOverlay = document.getElementById("add-item-overlay");

// When the "Add item" button is clicked, show the overlay div
document.getElementById("add-item-btn").addEventListener("click", function() {
  addItemOverlay.style.display = "block";
});

// When the "Close" button in the overlay div is clicked, hide the overlay div
document.getElementById("close-add-item-overlay").addEventListener("click", function() {
  addItemOverlay.style.display = "none";
  // clear the form fields
  document.getElementById("item-tag").value = "";
  document.getElementById("item-description").value = "";
  document.getElementById("item-key").value = "";
  document.getElementById('item-image').value="";
});

// When the user submits the form in the overlay div, add the new item to the database table
document.getElementById("add-item-form").addEventListener("submit", function(event) {
  event.preventDefault(); // Prevent the form from submitting normally

  const formData = new FormData(document.getElementById("add-item-form"));
  
  // Send an AJAX request to add the new item to the database
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "add_item.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

  // handle response
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
      // If the request was successful, hide the overlay div
      addItemOverlay.style.display = "none";

      // clear the form fields
      document.getElementById("item-tag").value = "";
      document.getElementById("item-description").value = "";
      document.getElementById("item-key").value = "";
      document.getElementById('item-image').value="";

      // Display a success message to the user
      var response = JSON.parse(xhr.responseText);
      alert(response.success);
      location.reload();
    }
    if (xhr.readyState == 4 && xhr.status > 399) {
      // Display an error message to the user
      var response = JSON.parse(xhr.responseText);
      alert(response.error);
    }
  };

  xhr.send(formData);
});

/* JavaScript code to handle the delete overlay */
var delItemOverlay = document.getElementById("delete-item-overlay");

function showOverlay(id) {
  var overlay = document.getElementById('delete-item-overlay');
  overlay.style.display = 'block';
  document.getElementById('tagid_delete').value = id;
}

// When the "No" button in the overlay div is clicked, hide the overlay div
document.getElementById("close-delete-item-overlay").addEventListener("click", function() {
  delItemOverlay.style.display = "none";
  document.getElementById('tagid_delete').value = "";
});

// When the user submits the form in the overlay div, delete the item from the database table
document.getElementById("delete-item-form").addEventListener("submit", function(event) {
  event.preventDefault(); // Prevent the form from submitting normally

  // Get the form data
  var itemTagid = document.getElementById("tagid_delete").value.toString();

  // Send an AJAX request to delete the item from the database
  var xhr = new XMLHttpRequest();
  xhr.open("POST", "del_item.php", true);
  xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
      // If the request was successful, hide the overlay div
      delItemOverlay.style.display = "none";

      // clear the form fields
      document.getElementById("tagid_delete").value = "";

      // Display a success message to the user
      var response = JSON.parse(xhr.responseText);
      alert(response.success);
      location.reload();
    }
    if (xhr.readyState == 4 && xhr.status > 399) {
      // Display an error message to the user
      var response = JSON.parse(xhr.responseText);
      alert(response.error);
    }
  };
  xhr.send("t=" + itemTagid);
});


function randomKey(targetObj) {
  var length = 64;
  var characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  var random_string = '';
  for (var i = 0; i < length; i++) {
    index = Math.floor(Math.random() * (characters.length - 1));
    random_string += characters[index];
  }

  if (targetObj != null)
    targetObj.value = random_string;
}

</script>
