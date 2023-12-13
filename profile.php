<?php
ob_start();

include('db_connect.php');

session_start();

if (isset($_POST['submitachievement'])){
  header("Location: submit.php");
  $query="SELECT * FROM achievements WHERE s_id=?";
  $stmnt = mysqli_prepare($conn,$query);
  mysqli_stmt_bind_param($stmnt, "s", $username);
  mysqli_stmt_execute($stmnt);
  $sql5 = mysqli_stmt_get_result($stmnt);
  while ($row = mysqli_fetch_array($sql5)){
    echo "<div id = 'file_div'";
    echo "file src = 'images/'" . $row['file_link'] . "'>";
    echo "<p>" . $row['category'] . "</p>";
    echo "<p>" . $row['name'] . "</p>";
    echo "<p>" . $row['external_file'] . "</p>";
    echo "<p>" . $row['description'] . "</p>";
    echo "<p>" . $row['keywords'] . "</p>";
    echo "<p>" . $row['v_id'] . "</p>";
    echo "</div>";
  }
}

if (isset($_POST['submit9'])) {
  header("Location:delete.php");
}

if (isset($_SESSION['username'])) {
  $username = mysqli_real_escape_string($conn, $_SESSION['username']);

  if (strlen($username) == 9 && is_numeric($username)) {
    $sql = "SELECT * FROM student WHERE s_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    $sqlprojects = "SELECT * FROM student INNER JOIN achievements ON student.s_id=achievements.s_id WHERE student.s_id=? AND achievements.category LIKE 'project'";
    $stmtProjects = mysqli_prepare($conn, $sqlprojects);
    mysqli_stmt_bind_param($stmtProjects, "s", $user['s_id']);
    mysqli_stmt_execute($stmtProjects);
    $userprojects = mysqli_stmt_get_result($stmtProjects);
    $userprojects = mysqli_fetch_all($userprojects, MYSQLI_ASSOC);

    $sqlinternships = "SELECT * FROM student INNER JOIN achievements ON student.s_id=achievements.s_id WHERE student.s_id=? AND achievements.category LIKE 'internship'";
    $stmtInternships = mysqli_prepare($conn, $sqlinternships);
    mysqli_stmt_bind_param($stmtInternships, "s", $user['s_id']);
    mysqli_stmt_execute($stmtInternships);
    $userinternships = mysqli_stmt_get_result($stmtInternships);
    $userinternships = mysqli_fetch_all($userinternships, MYSQLI_ASSOC);

    $sqlawards = "SELECT * FROM student INNER JOIN achievements ON student.s_id=achievements.s_id WHERE student.s_id=? AND achievements.category LIKE 'honors and awards'";
    $stmtAwards = mysqli_prepare($conn, $sqlawards);
    mysqli_stmt_bind_param($stmtAwards, "s", $user['s_id']);
    mysqli_stmt_execute($stmtAwards);
    $userawards = mysqli_stmt_get_result($stmtAwards);
    $userawards = mysqli_fetch_all($userawards, MYSQLI_ASSOC);

    $sqlextracurricularactivities = "SELECT * FROM student INNER JOIN achievements ON student.s_id=achievements.s_id WHERE student.s_id=? AND achievements.category LIKE 'extra-curricular activities'";
    $stmtExtracurricularActivities = mysqli_prepare($conn, $sqlextracurricularactivities);
    mysqli_stmt_bind_param($stmtExtracurricularActivities, "s", $user['s_id']);
    mysqli_stmt_execute($stmtExtracurricularActivities);
    $userextracurricularactivities = mysqli_stmt_get_result($stmtExtracurricularActivities);
    $userextracurricularactivities = mysqli_fetch_all($userextracurricularactivities, MYSQLI_ASSOC);

    $sqlstudentevents = "SELECT * FROM participates INNER JOIN events ON participates.e_id=events.e_id WHERE participates.s_id=?";
    $stmtStudentEvents = mysqli_prepare($conn, $sqlstudentevents);
    mysqli_stmt_bind_param($stmtStudentEvents, "s", $user['s_id']);
    mysqli_stmt_execute($stmtStudentEvents);
    $userstudentevents = mysqli_stmt_get_result($stmtStudentEvents);
    $userstudentevents = mysqli_fetch_all($userstudentevents, MYSQLI_ASSOC);


    if (isset($_POST['submitpicture'])){
      $image_link = $_FILES['file']['name'];
      $targetpicture = "profile_pictures/" . basename($_FILES['file']['name']);

      if (move_uploaded_file($_FILES['file']['tmp_name'], $targetpicture)) {
        $msg = "Uploaded successfully";
      } 
      else {
        $msg = "Error in uploading";
      }

      $sqlpicture = "UPDATE student SET image_id=? WHERE s_id=?";
      $stmnt = mysqli_prepare($conn, $sqlpicture);
      mysqli_stmt_bind_param($stmnt, "ss", $image_link, $username);
      mysqli_stmt_execute($stmnt);
    }
  }
 
  else{
    $sql = "SELECT * FROM verifier WHERE v_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if (isset($_POST['submitpicture'])) {
      $targetDirectory = "profile_pictures/";
      $image_link = $_FILES['file']['name'];
      $targetPath = $targetDirectory . basename($image_link);

      // File type validation (e.g., allowing only image file types)
      $allowedFileTypes = array("jpg", "jpeg", "png", "gif");
      $fileExtension = pathinfo($targetPath, PATHINFO_EXTENSION);

      if (in_array(strtolower($fileExtension), $allowedFileTypes)) {
          // Check and limit file size
          $maxFileSize = 2 * 1024 * 1024; // 2MB
          if ($_FILES['file']['size'] <= $maxFileSize) {
              if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                  $msg = "Uploaded successfully";

                  // Securely update the image in the database
                  $stmt = mysqli_prepare($conn, "UPDATE verifier SET image_id = ? WHERE s_id = ?");
                  mysqli_stmt_bind_param($stmt, "ss", $image_link, $username);
                  mysqli_stmt_execute($stmt);
              } else {
                  $msg = "Error in uploading";
              }
          } else {
              $msg = "File size exceeds the allowed limit.";
          }
      } else {
          $msg = "Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.";
      }
  }



  $_SESSION['username'] = $username;
  ob_end_flush();
} 
}
else {
  header("HTTP/1.0 404 Not Found");
  echo "<h1>404 Not Found</h1>";
  echo "The page that you have requested could not be found.";
  exit();
}

if (isset($_POST['submitsearch'])) {
  if (!empty($_POST['searchtext'])) {
    $searchtype = filter_input(INPUT_POST, 'searchtype', FILTER_SANITIZE_STRING);
    $searchtext = $_POST['searchtext'];
    $searchtext = '%'. mysqli_real_escape_string($conn, $searchtext). '%';
    if ($searchtype == "Students") {
      $sql = "SELECT * FROM student WHERE name LIKE ? OR s_id LIKE ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "ss", $searchtext, $searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['searchtext'] = $_POST['searchtext'];
        $_SESSION['searchtype'] = "students";
        header("Location:view_all_profiles.php");
      } else {
        echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
    } 
    else if ($searchtype == "Verifiers") {
      $sql = "SELECT * FROM verifier WHERE name LIKE ? OR v_id LIKE ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "ss", $searchtext, $searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      echo ("Happy1");
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['searchtext'] = $_POST['searchtext'];
        $_SESSION['searchtype'] = "verifiers";
        echo "Happy2";
        header("Location:view_all_profiles.php");
      } else {
        echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
    } else if ($searchtype == "Achievements") {
      $sql = "SELECT * FROM achievements WHERE name LIKE ? OR keywords LIKE ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "ss", $searchtext, $searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['searchtext'] = $_POST['searchtext'];
        $_SESSION['searchtype'] = "achievements";
        header("Location: view_all_profiles.php");
      } else {
        echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
    } else if ($searchtype == "Events") {
      $sql = "SELECT * FROM events WHERE name LIKE ? OR summary LIKE ? OR keywords LIKE ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "sss", $searchtext, $searchtext,$searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['searchtext'] = $_POST['searchtext'];
        $_SESSION['searchtype'] = "events";
        header("Location:view_all_events.php");
      } else {
        echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
    } else if ($searchtype == "Notices") {
      $sql = "SELECT * FROM events WHERE name LIKE ? OR content LIKE ? OR keywords LIKE ?";
      $stmt = mysqli_prepare($conn, $sql);
      mysqli_stmt_bind_param($stmt, "sss", $searchtext, $searchtext,$searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);
      if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION['searchtext'] = $_POST['searchtext'];
        $_SESSION['searchtype'] = "notices";
        header("Location: view_all_notices.php");
      } else {
        echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
    } 
  }
    else {
      echo "<script>alert('Please choose an option to search.')</script>";
    }
  }
?>

<!DOCTYPE html>
<html lang="en-US">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>UIUSAT - My Profile</title>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="crossorigin" />
  <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&amp;family=Roboto:wght@300;400;500;700&amp;display=swap" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&amp;family=Roboto:wght@300;400;500;700&amp;display=swap" media="print" onload="this.media='all'" />
  <noscript>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@600&amp;family=Roboto:wght@300;400;500;700&amp;display=swap" />
  </noscript>
  <link href="css/font-awesome/css/all.min.css?ver=1.2.1" rel="stylesheet">
  <link href="css/mdb.min.css?ver=1.2.1" rel="stylesheet">
  <link href="css/aos.css?ver=1.2.1" rel="stylesheet">
  <link href="css/main.css?ver=1.2.1" rel="stylesheet">
  <link href="css/main.css" rel="stylesheet">
  <link href="nav.css" rel="stylesheet">
  <link rel="stylesheet" href="css/fontawesome/css/fontawesome.min.css">
  <noscript>
    <style type="text/css">
      [data-aos] {
        opacity: 1 !important;
        transform: translate(0) scale(1) !important;
      }
    </style>
  </noscript>
</head>

<body class="bg-light" id="top">
  <header class="d-print-none">
    <div class="container text-center text-lg-left">
      <div class="site-nav">
        <!-- Nav bar Start -->
        <nav class="navbar navbar-expand-xl navbar-dark bg-dark" style="left:90px ;">
          <a href="#" class="navbar-brand"><i class="fa fa-cube"></i>UIU<b>SAT</b></a>
          <button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
          </button>
          <!-- Collection of nav links, forms, and other content for toggling -->
          <div id="navbarCollapse" class="collapse navbar-collapse justify-content-start">

            <form method="POST">
              <div class="search">
                <input type="text" name="searchtext" id="search" placeholder="Search" style="position:relative; top: 40px; width: 200px; left:-50px; padding:6px; border-radius:5px;">
                <select class="form-select" name="searchtype" aria-label="Default select example" style="position:relative; left: 170px; width: 150px">
                  <option selected>Filter</option>
                  <option value="Students">Students</option>
                  <option value="Verifiers">Verifiers</option>
                  <option value="Achievements">Achievements</option>
                  <option value="Events">Events</option>
                  <option value="Notices">Notices</option>
                </select>
                <button name="submitsearch" class="btn" style="position:relative; top: -40px; left: 280px; background-color:#FFF; ">Search</button>
              </div>
            </form>
            <?php
            ?>

            <div class="navbar-nav ml-auto" style="position:relative; left:280px">
              <?php
              if (strlen($username) == 9 && is_numeric($username)) {
              ?>
                <a href="studenthome.php" class="nav-item nav-link active"><i class="fa fa-home"></i><span>Home</span></a>
              <?php
              } else {
              ?>
                <a href="teacherhome.php" class="nav-item nav-link active"><i class="fa fa-home"></i><span>Home</span></a>
              <?php
              }
              ?>
              <a href="profile.php" class="nav-item nav-link"><i class="fa fa-users"></i><span>Profile</span></a>
              <a href="view_all_events.php" class="nav-item nav-link"><i class="fa fa-briefcase"></i><span>Events</span></a>
              <a href="view_all_notices.php" class="nav-item nav-link"><i class="fa fa-envelope"></i><span>Notices</span></a>
              <a href="notification.php" class="nav-item nav-link"><i class="fa fa-bell"></i><span>Notifications</span></a>
              <a href="logout.php" class="nav-item nav-link"><i class="fa-solid fa-right-from-bracket"></i><span>Log Out</span></a>
              <?php
        if (strlen($username) == 9 && is_numeric($username)) {
        ?>
          <div class="nav-item dropdown">
            <a href="profile.php" data-toggle="dropdown" class="nav-item nav-link dropdown-toggle user-action"><img src="images/student.jpg" class="avatar" alt="Avatar"> Student </a>
          </div>
        <?php
        }
        else {
        ?>
          <div class="nav-item dropdown">
            <a href="profile.php" data-toggle="dropdown" class="nav-item nav-link dropdown-toggle user-action"><img src="images/verifier.jpg" class="avatar" alt="Avatar"> Verifier </a>
          </div>
        <?php
        }
        ?>
            </div>
        </nav>
        <!-- Nav bar end -->
      </div>
    </div>
    </div>
  </header>
  <div class="page-content" style="position: relative;top: 30px; background-color:#ccc7d1">
    <div class="container">
      <div class="resume-container">
        <div class="shadow-1-strong bg-white my-5" id="intro">
          <div class="b-info text-white">
            <div class="cover b-image" style="position: relative;top: -1px;"><img src="images/head3.png" style="height:480px;width:1115px;">
              <div class="mask" style="background-color: rgba(187, 147, 147, 0.164);backdrop-filter: blur(2px);">
                <div class="text-center p-5">
                  <?php
                  if (strlen($username) == 9 && is_numeric($username)) {
                  ?>
                    <div class="avatar p-1"><img class="img-thumbnail shadow-2-strong" src="images/student.jpg" width="160" height="160" /></div>
                  <?php
                  } else {
                  ?>
                    <div class="avatar p-1"><img class="img-thumbnail shadow-2-strong" src="images/verifier.jpg" width="160" height="160" /></div>
                  <?php
                  }
                  ?>
                  <div class="header-bio mt-3">
                    <div data-aos="zoom-in" data-aos-delay="0">
                      <h2 class="h1"><?php echo $user['name']; ?></h2>
                      <p>Welcome to My Profile!</p>
                    </div>
                    <div class="header-social mb-3 d-print-none" data-aos="zoom-in" data-aos-delay="200">
                    </div>
                    <?php
                  if (strlen($username) == 9 && is_numeric($username)) {
                  ?>
                    <form action="pdf.php" method="GET" target="_blank">
                      <div class="d-print-none" style="float: right inherit;">

                        <a class="btn btn-outline-light btn-lg shadow-sm mt-1 me-3" style="text-decoration: none; color: #FFF" href="pdf.php?s_id=<?php echo $user["s_id"] ?>" data-aos="fade-right" data-aos-delay="700">Generate CV</a>
                        
                    </form>
                  <?php
                  }
                  ?>
                    
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="shadow-1-strong bg-white my-5 p-5" id="about">
          <div class="about-section">
            <div class="row">
              <div class="col-md-6">
              </div>
              
              <div class="bio">
                <div class="col-md-5 offset-lg-1">
                  <div class="row mt-2">
                    <h2 class="h2 fw-light mb-4">Bio</h2>
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="far fa-calendar-alt pe-2 text-muted" style="width:24px;opacity:0.85;"></i>Name</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['name']; ?></div>
                    </div>
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="far fa-envelope pe-2 text-muted" style="width:24px;opacity:0.85;"></i> ID</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $username; ?></div>
                    </div>
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="fab fa-skype pe-2 text-muted" style="width:24px;opacity:0.85;"></i> Email</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['email']; ?></div>
                    </div>
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="fas fa-phone pe-2 text-muted" style="width:24px;opacity:0.85;"></i> Phone</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['phone']; ?></div>
                    </div>
                    
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="fas fa-map-marker-alt pe-2 text-muted" style="width:24px;opacity:0.85;"></i> Department</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['department']; ?></div>
                    </div>
                    
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="fas fa-map-marker-alt pe-2 text-muted" style="width:24px;opacity:0.85;"></i> DOB</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['dob']; ?></div>
                    </div>
                    <div class="col-sm-5">
                      <div class="pb-2 fw-bolder"><i class="fas fa-map-marker-alt pe-2 text-muted" style="width:24px;opacity:0.85;"></i> Gender</div>
                    </div>
                    <div class="col-sm-7">
                      <div class="pb-2"><?php echo $user['gender']; ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
      if (strlen($username) == 9 && is_numeric($username)) {
      ?>

      <?php
      }
      ?>

      <?php
      if (strlen($username) == 9 && is_numeric($username)) {
      ?>
        <div class="shadow-1-strong bg-white my-5 p-5" id="education">
          <div class="education-section">
            <h2 class="h2 fw-light mb-4">Skills:</h2>
            <div class="List">
              <ol>
                <?php
                $sqlallkeywords = "SELECT achievements.keywords FROM student INNER JOIN achievements ON student.s_id=achievements.s_id WHERE student.s_id='$username'";
                $result = mysqli_query($conn, $sqlallkeywords);
                $userallkeywords = mysqli_fetch_all($result, MYSQLI_ASSOC);
                foreach ($userallkeywords as $rowkeywords) {
                  foreach ($rowkeywords as $singlekeyword) {
                    echo $singlekeyword . " ";
                  }
                }
                ?>
              </ol>
            </div>
          </div>
        </div>


    </div>
    <h1 class="h2 fw-light mb-4">Achievements</h1>
    <form action="" method="POST">
      <div class="input-group" style="float: right inherit;">
        <button name="submitachievement" href="submit.php" class="btn1">Add Achievement</button>
      </div>
      <div class="shadow-1-strong bg-white my-5 p-5" id="experience">
        <div class="work-experience-section">
          <h2 class="h2 fw-light mb-4">Projects:</h2>
          <?php
          if (!empty($userprojects)) {
            $count = 0;
            foreach ($userprojects as $userproject) {
              $count++;
          ?>
              <div class="timeline">
                <div class="timeline-card timeline-card-info" data-aos="fade-in" data-aos-delay="0">
                  <div class="timeline-head px-4 pt-3">

                    <div class="h6">Project: <span class="text-muted h6"><?php echo $count; ?></span></div>
                    <div class="h6">Title: <span class="text-muted h6"><?php echo $userproject['name']; ?></span></div>
                    <div class="h6">Description: <span class="text-muted h6"><?php echo $userproject['description']; ?></span></div>
                    <div class="h6">Keywords: <span class="text-muted h6"><?php echo $userproject['keywords']; ?></span></div>
                    <div class="h6">External File Link: <span class="text-muted h6"><?php echo $userproject['external_file']; ?></span></div>
                    <div class="h6">Verification: <span class="text-muted h6">
                        <?php
                        if ($userproject['is_verified'] == 1) {
                          echo ("Verified by " . $userproject['v_id']);
                        } else {
                          echo ("Not Verified");
                        }
                        ?>
                      </span></div>
                    <a href="view_specific_achievement.php?a_id=<?php echo $userproject['a_id'] ?>">
                      <div class="h6"><span class="text-muted h6"><?php echo ("Click To View Details"); ?></span></div>
                    </a>
                    <div class="h6">Image: <span class="text-muted h6"></span></div>
                    <div class="myimg"><img src="./images/<?php echo $userproject['file_link']; ?>" style="width:400px;height: 250px;border-radius: 10px; border: 6px solid #f08c09; padding: 3px;"></div>

                    <div class="input-group" style="float: right inherit;">
                      <a class="btn5" style="text-decoration: none; color: white;" href="delete.php?d_id=<?php echo $userproject["a_id"] ?>">Delete</a>
                    </div>
                    <br></br>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>

      <div class="shadow-1-strong bg-white my-5 p-5" id="experience">
        <div class="work-experience-section">
          <h2 class="h2 fw-light mb-4">Internship:</h2>
          <?php
          if (!empty($userinternships)) {
            $count = 0;
            foreach ($userinternships as $userinternship) {
              $count++;
          ?>
              <div class="timeline">
                <div class="timeline-card timeline-card-info" data-aos="fade-in" data-aos-delay="0">
                  <div class="timeline-head px-4 pt-3">

                    <div class="h6">Internship: <span class="text-muted h6"><?php echo $count; ?></span></div>
                    <div class="h6">Title: <span class="text-muted h6"><?php echo $userinternship['name']; ?></span></div>
                    <div class="h6">Description: <span class="text-muted h6"><?php echo $userinternship['description']; ?></span></div>
                    <div class="h6">Keywords: <span class="text-muted h6"><?php echo $userinternship['keywords']; ?></span></div>
                    <div class="h6">External File Link: <span class="text-muted h6"><?php echo $userinternship['external_file']; ?></span></div>
                    <div class="h6">Verification: <span class="text-muted h6">
                        <?php
                        if ($userinternship['is_verified'] == 1) {
                          echo ("Verified by " . $userinternship['v_id']);
                        } else {
                          echo ("Not Verified");
                        }
                        ?>
                      </span></div>
                    <a href="view_specific_achievement.php?a_id=<?php echo $userinternship['a_id'] ?>">
                      <div class="h6"><span class="text-muted h6"><?php echo ("Click To View Details"); ?></span></div>
                    </a>
                    <div class="h6">Image: <span class="text-muted h6"></span></div>
                    <div class="myimg"><img src="./images/<?php echo $userinternship['file_link']; ?>" style="width:400px;height: 250px;border-radius: 10px; border: 6px solid #f08c09; padding: 3px;"></div>

                    <div class="input-group" style="float: right inherit;">
                      <a class="btn5" style="text-decoration: none; color: white;" href="delete.php?d_id=<?php echo $userinternship["a_id"] ?>">Delete</a>
                    </div>
                    <br></br>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>

      <div class="shadow-1-strong bg-white my-5 p-5" id="experience">
        <div class="work-experience-section">
          <h2 class="h2 fw-light mb-4">Honors & Awards:</h2>
          <?php
          if (!empty($userawards)) {
            $count = 0;
            foreach ($userawards as $useraward) {
              $count++;
          ?>
              <div class="timeline">
                <div class="timeline-card timeline-card-info" data-aos="fade-in" data-aos-delay="0">
                  <div class="timeline-head px-4 pt-3">
                    <div class="h6">Honors: <span class="text-muted h6"><?php echo $count; ?></span></div>
                    <div class="h6">Title: <span class="text-muted h6"><?php echo $useraward['name']; ?></span></div>
                    <div class="h6">Description: <span class="text-muted h6"><?php echo $useraward['description']; ?></span></div>
                    <div class="h6">Keywords: <span class="text-muted h6"><?php echo $useraward['keywords']; ?></span></div>
                    <div class="h6">External File Link: <span class="text-muted h6"><?php echo $useraward['external_file']; ?></span></div>
                    <div class="h6">Verification: <span class="text-muted h6">
                        <?php
                        if ($useraward['is_verified'] == 1) {
                          echo ("Verified by " . $useraward['v_id']);
                        } else {
                          echo ("Not Verified");
                        }
                        ?>
                      </span></div>
                    <a href="view_specific_achievement.php?a_id=<?php echo $useraward['a_id'] ?>">
                      <div class="h6"><span class="text-muted h6"><?php echo ("Click To View Details"); ?></span></div>
                    </a>
                    <div class="h6">Image: <span class="text-muted h6"></span></div>
                    <div class="myimg"><img src="./images/<?php echo $useraward['file_link']; ?>" style="width:400px;height: 250px;border-radius: 10px; border: 6px solid #f08c09; padding: 3px;"></div>

                    <div class="input-group" style="float: right inherit;">
                      <a class="btn5" style="text-decoration: none; color: white;" href="delete.php?d_id=<?php echo $useraward["a_id"] ?>">Delete</a>
                    </div>
                    <br></br>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>

      <div class="shadow-1-strong bg-white my-5 p-5" id="experience">
        <div class="work-experience-section">
          <h2 class="h2 fw-light mb-4">Extra-Curricular Activities:</h2>
          <?php
          if (!empty($userextracurricularactivities)) {
            $count = 0;
            foreach ($userextracurricularactivities as $userextracurricularactivity) {
              $count++;
          ?>
              <div class="timeline">
                <div class="timeline-card timeline-card-info" data-aos="fade-in" data-aos-delay="0">
                  <div class="timeline-head px-4 pt-3">
                    <div class="h6">Activity: <span class="text-muted h6"><?php echo $count; ?></span></div>
                    <div class="h6">Title: <span class="text-muted h6"><?php echo $userextracurricularactivity['name']; ?></span></div>
                    <div class="h6">Description: <span class="text-muted h6"><?php echo $userextracurricularactivity['description']; ?></span></div>
                    <div class="h6">Keywords: <span class="text-muted h6"><?php echo $userextracurricularactivity['keywords']; ?></span></div>
                    <div class="h6">External File Link: <span class="text-muted h6"><?php echo $userextracurricularactivity['external_file']; ?></span></div>
                    <div class="h6">Verification: <span class="text-muted h6">
                        <?php
                        if ($userextracurricularactivity['is_verified'] == 1) {
                          echo ("Verified by " . $userextracurricularactivity['v_id']);
                        } else {
                          echo ("Not Verified");
                        }
                        ?>
                      </span></div>
                    <a href="view_specific_achievement.php?a_id=<?php echo $userextracurricularactivity['a_id'] ?>">
                      <div class="h6"><span class="text-muted h6"><?php echo ("Click To View Details"); ?></span></div>
                    </a>
                    <div class="h6">Image: <span class="text-muted h6"></span></div>
                    <div class="myimg"><img src="./images/<?php echo $userextracurricularactivity['file_link']; ?>" style="width:400px;height: 250px;border-radius: 10px; border: 6px solid #f08c09; padding: 3px;"></div>

                    <div class="input-group" style="float: right inherit;">
                      <a class="btn5" style="text-decoration: none; color: white;" href="delete.php?d_id=<?php echo $userextracurricularactivity["a_id"] ?>">Delete</a>
                    </div>
                    <br></br>
                  </div>
                </div>
              </div>
          <?php
            }
          }
          ?>
        </div>
      </div>

      <div class="shadow-1-strong bg-white my-5 p-5" id="education">
        <div class="education-section">
          <h2 class="h2 fw-light mb-4">Events Participation Badges:</h2>
          <div class="List">
            <ol>
              <?php
              if (!empty($userstudentevents)) {
                $count = 0;
                foreach ($userstudentevents as $userstudentevent) {
                  $count++;
              ?>
                  <div class="h6">Number: <span class="text-muted h6"><?php echo $count; ?></span></div>
                  <a href="view_specific_event.php?e_id=<?php echo $userstudentevent['e_id'] ?>"><?php echo htmlspecialchars($userstudentevent['name']) ?></a><br></br>
              <?php
                }
              }
              ?>
            </ol>
          </div>
        </div>
      </div>
  </div>
  </div>
<?php
      }
?>

<script src="scripts/mdb.min.js?ver=1.2.1"></script>
<script src="scripts/aos.js?ver=1.2.1"></script>
<script src="scripts/main.js?ver=1.2.1"></script>
</body>

</html>

<?php
mysqli_close($conn);
?>