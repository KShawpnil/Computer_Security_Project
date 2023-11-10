<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);

function generateSecretKey($length = 6){
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
$trustedIp = ['::1','127.0.0.1', '192.168.1.1'];

$ipAddress = $_SERVER['REMOTE_ADDR'];
echo " <div style= 'color: blue; font-size:20px'>
Your IP Address is : $ipAddress 
<br> 
</div> ";
 

// GETTING ACTUAL USER IP ADDRESS
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $forwardedIps = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ipAddress = trim(end($forwardedIps));
}

// CHECKING IP ADDRSS
if (!in_array($ipAddress, $trustedIp)) {
  echo 
  '<body style="  justify-content: center;  ">

  <div style="color: red;">
      <h2>Access denied!</h2>
  </div>
  
  </body>';

    exit;
}



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
                    $otp = generateSecretKey();
                    $recipientEmail = $row['email'];
                    $mail->addAddress($recipientEmail);
                    $mail->Subject = 'Your OTP for Login';
                    $mail->Body = 'Your OTP is: ' . $otp;

                    if ($mail->send()) {
                        $_SESSION['otp'] = $otp;
                        $_SESSION['otp_timestamp'] = time();
                        echo 'OTP sent via email. Please check your email and enter the OTP within the specified time.';
                        echo '<div  id="otp-div">
                            <label for="otp" class="form-label">Enter OTP:</label>
                            <input type="text" class="form-control" id="otp" name="otp" />
                            <button type="button" class="btn btn-outline-primary" onclick="verifyOTP()">Verify</button>
                        </div>';
                    } else {
                        echo 'Failed to send OTP via email.';
                    }
                }
                else {
                  echo '<div class="warning-message">hi</div>';
                  echo '<div class="warning-message">Try again after 10 seconds You have reached max attempts .</div>';
                }
                
            } else {
              if ($attempt > 0) {
                $attempt--; 
                echo '<h1> WRONG PASSWORD, TRY AGAIN </h1>';
                echo '<div class="warning-message">You have $attempt left</div>';
              }
            
              if ($attempt == 0) {
                $resetTime = 10; 
                $lastAttemptTimestamp = isset($_SESSION['last_attempt_timestamp']) ? $_SESSION['last_attempt_timestamp'] : 0;
                $currentTime = time();
            
                if ($lastAttemptTimestamp > 0 && ($currentTime - $lastAttemptTimestamp) >= $resetTime) {
                    
                    $attempt = 5;
                    $_SESSION['last_attempt_timestamp'] = $currentTime;
                } else {
                    echo '<div class="warning-message">You have exceeded the maximum number of attempts.</div>';
                    echo '<div class="warning-message">Try again after 5 minutes.</div>';
                }
              }
            
              $_SESSION['attempt'] = $attempt;
              $_SESSION['last_attempt_timestamp'] = time();
              echo "You have $attempt attempts left";
            }
        } else {
             echo "INVALID USERNAME";
         }

        // $_SESSION['attempt'] = $attempt;
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
                    $otp = generateSecretKey();
                    $recipientEmail = $row['email'];
                    $mail->addAddress($recipientEmail);
                    $mail->Subject = 'Your OTP for Login';
                    $mail->Body = 'Your OTP is: ' . $otp;

                    if ($mail->send()) {
                        $_SESSION['otp'] = $otp;
                        $_SESSION['otp_timestamp'] = time();
                        echo 'OTP sent via email. Please check your email and enter the OTP within the specified time.';
                        echo '<div  id="otp-div">
                            <label for="otp" class="form-label">Enter OTP:</label>
                            <input type="text" class="form-control" id="otp" name="otp" />
                            <button type="button" class="btn btn-outline-primary" onclick="verifyOTP()">Verify</button>
                        </div>';
                    } else {
                        echo 'Failedd to send OTP via email.';
                    }
                 }
                //     echo '<div class="warning-message">hi</div>';
                //     echo '<div class="warning-message">Try again .</div>';
                // }
            } else {
                if ($attempt > 0) {
                    $attempt--; 
                    echo '<div class="warning-message"> <h1> WRONG PASSWORD,TRY AGAIN! </h1> </div>';
                }

                
                if ($attempt == 0) {
                    $resetTime = 10; 
                    $lastAttemptTimestamp = isset($_SESSION['last_attempt_timestamp']) ? $_SESSION['last_attempt_timestamp'] : 0;
                    $currentTime = time();
                
                    if ($lastAttemptTimestamp > 0 && ($currentTime - $lastAttemptTimestamp) >= $resetTime) {
                        
                        $attempt = 5;
                        $_SESSION['last_attempt_timestamp'] = $currentTime;
                    } else {
                        echo '<div class="warning-message">You have exceeded the maximum number of attempts.</div>';
                        echo '<div class="warning-message">Try again after 5 minutes.</div>';
                    }
                }
                
                $_SESSION['attempt'] = $attempt;
                $_SESSION['last_attempt_timestamp'] = time();
                echo "You have $attempt attempts left";
            }
        } 
        else {
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
        margin-top: 10px;

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
                name="submit">
                Login
              </button>
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
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa"
      crossorigin="anonymous"
    ></script>
    <script>
      

      function verifyOTP() {
        
        var enteredOTP = document.getElementById('otp').value;
        var storedOTP = "<?php echo $_SESSION['otp']; ?>"; 
        
        if (enteredOTP === storedOTP) {

          setTimeout(() => {
            console.log('hi');
            sendLoginAlertEmail();
          }, 1000);
          
          var userResponse = prompt("Do you want to save this device to trusted?", "Yes");

            // Check the user's response
            if (userResponse !== null) {
                <?php $ipAddress = $_SERVER['REMOTE_ADDR']; 
                $trustedIp[]= $ipAddress;
                ?>

            } else {
                
            }

          setTimeout(() => {
            
            window.location.href = 'studenthome.php';
          }, 3000);

        } else {
          alert('Incorrect OTP. Please try again.');
        }
      }

      function sendLoginAlertEmail() {
    var userEmail = "<?php echo $_SESSION['user_email']; ?>";
    console.log("User Email: " + userEmail);

    $.ajax({
        type: "POST",
        url: "send_login_alert.php",
        data: { userEmail: userEmail }, // Use the same key as in PHP
        success: function(response) {
            console.log(response);
        }
    });
}


    </script>
  </body>
</html>
