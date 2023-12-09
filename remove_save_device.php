<?php
session_start();
include('db_connect.php'); // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the 'removeDevice' parameter is set
    if (isset($_POST['removeDevice'])) {
        $deviceId = $_POST['removeDevice'];

        // Delete the saved device from the database
        $deleteDeviceQuery = "DELETE FROM student_ip_addresses WHERE id = '$deviceId'";
        if (mysqli_query($conn, $deleteDeviceQuery)) {
            echo "Device removed successfully.";
        } else {
            echo "Error removing device: " . mysqli_error($conn);
        }
    }
}

// Fetch the user's saved devices from the database
$username = $_SESSION['username'];
$getDevicesQuery = "SELECT id, ip_address FROM student_ip_addresses WHERE student_id = '$username'";
$result = mysqli_query($conn, $getDevicesQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remove Saved Devices</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
            padding: 20px;
        }

        h2 {
            color: #333;
        }

        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Remove Saved Devices</h2>

    <?php
    if (mysqli_num_rows($result) > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>IP Address</th><th>Action</th></tr>";

        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['ip_address'] . "</td>";
            echo "<td><form method='post' action='remove_save_device.php'>
            <button type='submit' name='removeDevice' value='" . $row['id'] . "' style='background-color: red; color: white; padding: 10px;'>Remove</button>
            </form>
            </td>";
            echo "</tr>";
            
        }

        echo "</table>";
        
    } else {
        echo "No saved devices found.";
    }
    ?>
<br>
    <a href="studenthome.php">Back to Home</a>

</body>
</html>
