<?php
session_start();
include('db_connect.php'); // Include your database connection file

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $ipAddress = $_GET['ipAddress'];

    // Check if the checkbox for saving the device is checked
    if (isset($_POST['saveDevice']) && $_POST['saveDevice'] == '1') {
        $saveDeviceQuery = "INSERT INTO student_ip_addresses (student_id, ip_address) VALUES ('$username', '$ipAddress')";
        
        if (mysqli_query($conn, $saveDeviceQuery)) {
            // Set a cookie with the username and IP address
            setcookie('username', $username, time() + (86400 * 30), "/");
            setcookie('ipAddress', $ipAddress, time() + (86400 * 30), "/");
            header("Location: studenthome.php");
            exit;
        } else {
            echo "Error: " . $saveDeviceQuery . "<br>" . mysqli_error($conn);
        }
    } else {
        // Set a cookie with the username and IP address
        setcookie('username', $username, time() + (86400 * 30), "/");
        setcookie('ipAddress', $ipAddress, time() + (86400 * 30), "/");
        header("Location: studenthome.php");
        exit;
   
}
    
 }


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Save Device</title>
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

        img {
            max-width: 100%;
            height: auto;
        }

        form {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h2>Save This Device</h2>
    <img src="save_device_image.jpg" alt="Save Device Image">

    <form id="saveDeviceForm" action="" method="post">
        <label for="saveDeviceCheckbox">
            <input type="checkbox" id="saveDeviceCheckbox" name="saveDevice" value="1">
            Save this device
        </label>
        <br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
