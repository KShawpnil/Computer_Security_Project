<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

function generateSecretKey($length = 4)
{
    $characters = '0123456789';
    $otp = '';

    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[random_int(0, strlen($characters) - 1)];
    }

    return $otp;
}

$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'jsnode741@gmail.com';
$mail->Password = 'vanc njjm vpmg mnmf';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

include('db_connect.php');
session_start();

$attempt = isset($_SESSION['attempt']) ? $_SESSION['attempt'] : 5;

// IP ADDRESS TRUSTED
$trustedIp = ['::1', '127.0.0.1', '192.168.1.1'];

$ipAddress = $_SERVER['REMOTE_ADDR'];
$ip[] = $ipAddress;
echo "<h2 font-size:20px'>Your IP Address is: $ipAddress<br></h2>";

if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ipAddress = trim(end($forwardedIps));
}

// CHECKING IP ADDRESS
// if (!in_array($ipAddress, $trustedIp)) {
//     echo '<body style="justify-content: center; ">';
//     echo '<div style="color: red;"><h2>Access denied!</h2></div>';
//     echo '</body>';
//     exit;
// }

if (isset($_POST['submit'])) {
    $username = $_POST['username'];
    $pass = $_POST['pass'];

    if (strlen($username) == 9 && is_numeric($username)) {
        $sql = "SELECT * FROM student WHERE s_id=?";
        $stmnt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmnt, "s", $username);
        mysqli_stmt_execute($stmnt);
        $result = mysqli_stmt_get_result($stmnt);

        if ($result->num_rows > 0) {

            $row = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $row['s_id'];

            if ($row['password'] === $pass) {
              $_SESSION['user_email'] = $row['email'];
          
              if ($attempt > 0) {
                  $id = $row['s_id'];
                  $db_ip_address_query = "SELECT ip_address FROM student_ip_addresses WHERE student_id = $id";
                  $result_ip = mysqli_query($conn, $db_ip_address_query);
          
                   $user_ip_addresses = [];
                    while ($ip_row = mysqli_fetch_assoc($result_ip)) {
                      $user_ip_addresses[] = $ip_row['ip_address'];
                    }
          
                    if (in_array($ipAddress, $user_ip_addresses)) {
                      $_SESSION['username'] = $row['s_id'];
                    header("Location: studenthome.php");
                    exit;
                } else {
                    
                    $otp = generateSecretKey();
                    $recipientEmail = $row['email'];
                    $mail->addAddress($recipientEmail);
                    $mail->Subject = 'Your OTP for Login';
                    $mail->Body = 'Your OTP is: ' . $otp;
                
                    if ($mail->send()) {
                        $_SESSION['otp'] = $otp;
                        $_SESSION['otp_timestamp'] = time();
                        echo 'OTP sent via email. Please check your email and enter the OTP within the specified time.';
                        echo  '<div id="otp-div" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px;">
                        <label for="otp" class="form-label">Enter OTP:</label>
                        <input type="text" class="form-control" id="otp" name="otp" style="margin-bottom: 10px;">
                        <button type="button" class="btn btn-outline-primary" onclick="verifyOTP()">Verify
                        </button>
                    </div>';
                    } else {
                        echo 'Failed to send OTP via email.';
                    }
                }
              } else {
                $resetTime = 10; // 10 seconds
                  $lastAttemptTimestamp = isset($_SESSION['last_attempt_timestamp']) ? $_SESSION['last_attempt_timestamp'] : 0;
                  $currentTime = time();

                  if ($lastAttemptTimestamp > 0 && ($currentTime - $lastAttemptTimestamp) >= $resetTime) {
                      $attempt = 5;
                      $_SESSION['last_attempt_timestamp'] = $currentTime;
                  } else {
                      echo '<div class="warning-message">You have exceeded the maximum number of attempts.</div>';
                      echo '<div class="warning-message">Try again after ' . ($resetTime - ($currentTime - $lastAttemptTimestamp)) . ' seconds.</div>';
}

                  echo '<div class="warning-message">Try again after 10 seconds. You have reached max attempts.</div>';
              }
          }
           else {
                if ($attempt > 0) {
                    $attempt--;
                    echo '<h1> Invalid Password! Try Again. </h1>';
                    echo "<div class='warning-message'>You have $attempt attempts left</div>";
                }

                if ($attempt == 0) {
                  $resetTime = 10; // 10 seconds
                  $lastAttemptTimestamp = isset($_SESSION['last_attempt_timestamp']) ? $_SESSION['last_attempt_timestamp'] : 0;
                  $currentTime = time();
                  
                  if ($lastAttemptTimestamp > 0 && ($currentTime - $lastAttemptTimestamp) >= $resetTime) {
                      $attempt = 5;
                      $_SESSION['last_attempt_timestamp'] = $currentTime;
                  } else {
                      echo '<div class="warning-message">You have exceeded the maximum number of attempts.</div>';
                      echo '<div class="warning-message">Try again after ' . ($resetTime - ($currentTime - $lastAttemptTimestamp)) . ' seconds.</div>';
                  }
                  
                }

                $_SESSION['attempt'] = $attempt;
                $_SESSION['last_attempt_timestamp'] = time();
                
            }
        } else {
            echo "INVALID USERNAME";
        }
    } else {
        $sql = "SELECT * FROM verifier WHERE v_id=?";
        $stmnt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmnt, "s", $username);
        mysqli_stmt_execute($stmnt);
        $result = mysqli_stmt_get_result($stmnt);

        if ($result->num_rows > 0) {
            $row = mysqli_fetch_assoc($result);
            $_SESSION['username'] = $row['v_id'];

            if ($row['password'] === $pass) {
              $_SESSION['user_email'] = $row['email'];
          
              if ($attempt > 0) {
                $id = $row['v_id'];

                $db_ip_address_query = "SELECT ip_address FROM verifier_ip_addresses WHERE verifier_id = '$id'";
                $result_ip = mysqli_query($conn, $db_ip_address_query);
                
                 $user_ip_addresses = [];
                 while ($ip_row = mysqli_fetch_assoc($result_ip)) {
                     $user_ip_addresses[] = $ip_row['ip_address'];
                 }
                
                 if (in_array($ipAddress, $user_ip_addresses)) {
                  $_SESSION['username'] = $row['s_id'];
                  header("Location: teacherhome.php");
                  exit;
              } else {
                  
              
                  $otp = generateSecretKey();
                  $recipientEmail = $row['email'];
                  $mail->addAddress($recipientEmail);
                  $mail->Subject = 'Your OTP for Login';
                  $mail->Body = 'Your OTP is: ' . $otp;
              
                  if ($mail->send()) {
                      $_SESSION['otp'] = $otp;
                      $_SESSION['otp_timestamp'] = time();
                      echo 'OTP sent via email. Please check your email and enter the OTP within the specified time.';
                      echo 
                      '<div id="otp-div" style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 200px;">
                      <label for="otp" class="form-label">Enter OTP:</label>
                      <input type="text" class="form-control" id="otp" name="otp" style="margin-bottom: 10px;">
                      <button type="button" class="btn btn-outline-primary" onclick="verifyOTP()">Verify
                      </button>
                  </div>';
                  } else {
                      echo 'Failed to send OTP via email.';
                  }
              }
              } else {
                  echo '<div class="warning-message">Try again after 10 seconds. You have reached max attempts.</div>';
              }
          }
           else {
                if ($attempt > 0) {
                    $attempt--;
                    echo '<div class="warning-message"><h1> Invalid Password! Try again </h1></div>';
                }

                if ($attempt == 0) {
                  $resetTime = 10; // 10 seconds
                  $lastAttemptTimestamp = isset($_SESSION['last_attempt_timestamp']) ? $_SESSION['last_attempt_timestamp'] : 0;
                  $currentTime = time();
                  
                  if ($lastAttemptTimestamp > 0 && ($currentTime - $lastAttemptTimestamp) >= $resetTime) {
                      $attempt = 5;
                      $_SESSION['last_attempt_timestamp'] = $currentTime;
                  } else {
                      echo '<div class="warning-message">You have exceeded the maximum number of attempts.</div>';
                      echo '<div class="warning-message">Try again after ' . ($resetTime - ($currentTime - $lastAttemptTimestamp)) . ' seconds.</div>';
                  }
                  
                }

                $_SESSION['attempt'] = $attempt;
                $_SESSION['last_attempt_timestamp'] = time();
                echo "You have $attempt attempts left";
            }
        } else {
            echo "INVALID USERNAME";
        }
    }

    $_SESSION['attempt'] = $attempt;
}

?>




<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UIUSAT - Login</title>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx"
      crossorigin="anonymous"
    />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

  </head>
  <body>
    <style>
      @import url('https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,200;0,300;0,400;0,600;0,700;0,900;1,200;1,300;1,400;1,600;1,700;1,900&display=swap');
      body {
        font-family: 'Source Sans Pro', sans-serif;
        background-color: #f8f2e8;
      }
      .log-btn {
        text-decoration: none;
      }
      .form-main {
        margin-top: 600px;
      }
      
    .warning-message {
        color: #FF0000; 
        font-weight: bold;
        font-size: 20px;
        margin-top: 5px;
        height: 40px;
        width: 100%;
        
        border-radius: 3px;
        text-align: center;
        justify-content: center;
        background-color: #FFBABA;

    }
    h2 {
      color: blue;
      
      height: 15px;
      background-color: #f8f2e8;
    }
    h1{
        color: black; 
        font-size: 20px;
        margin-top: 5px;
        height: 40px;
        width: 100%;
        font-weight: bold;
        border-radius: 3px;
        text-align: center;
        justify-content: center;
        background-color: #FFFFF0
        

    }


      
    </style>


    <div class="container form-main" style="margin-top: 170px;">
      <div class="text-center mb-5">
        <h2 style="color: rgb(138, 113, 66)">Welcome to UIUSAT</h2>
      </div>
      <div class="container pt-5 pb-3" style="background-color: #f9ebd1; width: 700px;">
        <form action="" class="form-input  d-flex justify-content-center" method="post">
          <div class="row mx-5" style="width: 400px;">
            <div class="col col-lg-12">
              <div class="mb-3">
                <label for="exampleFormControlInput1" class="form-label"
                  >Username</label
                >
                <input
                  type="text"
                  class="form-control"
                  id="exampleFormControlInput1"
                  placeholder="Username"
                  name="username"
                />
              </div>
            </div>

            <div class="col col-lg-12">
              <div class="mb-3">
                <label for="exampleFormControlInput1" class="form-label"
                  >Password</label>
                <input
                  type="password"
                  class="form-control"
                  id="exampleFormControlInput1"
                  placeholder="Password"
                  name="pass"
                />
              </div>
            </div>
            <div class="col col-lg-12 text-center">
              <button
                type="submit"
              
                class="btn btn-outline-primary"
                style="
                  width: 60px;
                  background-color: rgb(33, 57, 33);
                  color: white;
                  border-radius: 0%;
                  border: 0px transparent;
                "
                type="submit"
                name="submit">
                Login
              </button>
            </div>
            <div class="col col-lg-12 text-center">
                <input type="checkbox" id="saveDevice" name="saveDevice" />
                <label for="saveDevice">Save this device</label>
            </div>
            <div class="col col-lg-12 text-center mt-2">
              <p>
                Don't have an account? <a class="log-btn" href="registration.php">Signup</a>
              </p>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>

<script>

 function  verifyOTP() {
    var username = "<?php echo $_SESSION['username']; ?>";
    var ipAddress = "<?php echo $ipAddress; ?>";
    var enteredOTP = document.getElementById('otp').value;
    var storedOTP = "<?php echo $_SESSION['otp']; ?>";

    if (enteredOTP === storedOTP) {
        sendLoginAlertEmail();

       $.ajax({
            type: 'POST',
            url: 'saveDevice.php',
            data: { username: username, ipAddress: ipAddress },
            success: function (response) {
                console.log(response);

                // Redirect only after the AJAX request is successful
                window.location.replace('saveDevice.php?username=' + encodeURIComponent(username) + '&ipAddress=' + encodeURIComponent(ipAddress));
            },
            error: function (xhr, status, error) {
                console.error("Ajax request failed: " + error);
            }
        });
    } else {
        alert('Incorrect OTP. Please try again.');
    }
}




    function saveIpAddress() {
        var ipAddress = "<?php echo $ipAddress; ?>";
        console.log("IP Address: " + ipAddress);

        $.ajax({
            type: "POST",
            url: "save_ip_address.php",
            data: { ipAddress: ipAddress },
            success: function (response) {
                console.log(response);
            }
        });
    }

    function sendLoginAlertEmail() {
        var userEmail = "<?php echo $_SESSION['user_email']; ?>";
        console.log("User Email: " + userEmail);

        $.ajax({
            type: "POST",
            url: "send_login_alert.php",
            data: { userEmail: userEmail },
            success: function (response) {
                console.log(response);
            }
        });
    }
</script>
  </body>
</html>
