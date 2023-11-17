<?php

include('db_connect.php');

session_start();


if(isset($_SESSION['username'])){
  $username=mysqli_real_escape_string($conn,$_SESSION['username']);

  if(strlen($username)==9 && is_numeric($username)){
    $sql = "SELECT * FROM student WHERE s_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user=mysqli_fetch_assoc($result);
  }
  else{
    $sql = "SELECT * FROM verifier WHERE v_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user=mysqli_fetch_assoc($result);
  }
  
  $_SESSION['username'] = $username;

}
else{
  header("HTTP/1.0 404 Not Found");
  echo "<h1>404 Not Found</h1>";
  echo "The page that you have requested could not be found.";
  exit();
}

if(isset($_GET['a_id'])){
    $a_id=$_GET['a_id'];
    $achievement_id=mysqli_real_escape_string($conn,$_GET['a_id']);
    $sql="SELECT * FROM achievements WHERE a_id = ?";
    $stmt=mysqli_prepare($conn,$sql);
    mysqli_stmt_bind_param($stmt,"i",$achievement_id);
    mysqli_stmt_execute($stmt);
    $result=mysqli_stmt_get_result($stmt);
    $achievement=mysqli_fetch_assoc($result);
    mysqli_free_result($result);
}

if (isset($_POST['submitsearch'])) {
  if (!empty($_POST['searchtext'])) {
    $searchtype = filter_input(INPUT_POST, 'searchtype', FILTER_SANITIZE_STRING);
    $searchtext = '%' . mysqli_real_escape_string($conn, $_POST['searchtext']) . '%';
    $sql = "";
    $stmt = "";
    if ($searchtype == "Students") {
      $sql = "SELECT * FROM student WHERE name LIKE ? OR s_id LIKE ?";
    } 
    else if ($searchtype == "Verifiers") {
        $sql = "SELECT * FROM verifier WHERE name LIKE ? OR v_id LIKE ?";
    } 
    else if ($searchtype == "Achievements") {
        $sql = "SELECT * FROM achievements WHERE name LIKE ? OR keywords LIKE ?";
    } 
    else if ($searchtype == "Events") {
        $sql = "SELECT * FROM events WHERE name LIKE ? OR summary LIKE ? OR keywords LIKE ?";
    } 
    else if ($searchtype == "Notices") {
        $sql = "SELECT * FROM notices WHERE name LIKE ? OR content LIKE ? OR keywords LIKE ?";
    }
    $stmt=mysqli_prepare($conn,$sql);
    if($stmt){
      mysqli_stmt_bind_param($stmt, "ss", $searchtext, $searchtext);
      mysqli_stmt_execute($stmt);
      $result = mysqli_stmt_get_result($stmt);

      if ($result->num_rows > 0) {
          $row = mysqli_fetch_assoc($result);
          $_SESSION['searchtext'] = $_POST['searchtext'];
          $_SESSION['searchtype'] = strtolower($searchtype);
          header("Location:view_all_profiles.php");
      } 
      else {
          echo "<script>alert('Sorry. We do not have that information in our database.')</script>";
      }
      mysqli_stmt_close($stmt);
    }
  }
  else {
    echo "<script>alert('Please choose an option to search.')</script>";
  }
}


if (isset($_POST['tagbutton'])) {
  $sql4 = "SELECT * FROM notifications WHERE notifications.a_id=? AND notifications.v_id=?";
  $stmt = mysqli_prepare($conn, $sql4);
  mysqli_stmt_bind_param($stmt, "ss", $achievement['a_id'], $_SESSION['tagv_id']);
  mysqli_stmt_execute($stmt);
  $result4 = mysqli_stmt_get_result($stmt);

  if (!$result4->num_rows > 0) {
      $sql3 = "INSERT INTO notifications (a_id, s_id, v_id, ntf_status) VALUES (?, ?, ?, 0)";
      $stmt1 = mysqli_prepare($conn, $sql3);
      mysqli_stmt_bind_param($stmt1, "sss", $achievement['a_id'], $achievement['s_id'], $_SESSION['tagv_id']);
      mysqli_stmt_execute($stmt1);
      mysqli_stmt_close($stmt1);
  } 
  else {
      echo "<script>alert('You have already tagged this verifier')</script>";
  }
  mysqli_stmt_close($stmt);
}

if (isset($_GET['verifya_id'])) {
  $a_id = $_GET['verifya_id'];
  $achievement_id = mysqli_real_escape_string($conn, $_GET['verifya_id']);

  $sql = "SELECT * FROM achievements WHERE a_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $achievement_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);

  $achievement = mysqli_fetch_assoc($result);
  mysqli_free_result($result);

  if (isset($_POST['verifybutton'])) {
      $sql5 = "UPDATE notifications SET ntf_status=1 WHERE a_id=?";
      $stmt5 = mysqli_prepare($conn, $sql5);
      mysqli_stmt_bind_param($stmt5, "s", $_GET['verifya_id']);
      mysqli_stmt_execute($stmt5);
      $sql6 = "UPDATE achievements SET is_verified=1, v_id=? WHERE a_id=?";
      $stmt6 = mysqli_prepare($conn, $sql6);
      mysqli_stmt_bind_param($stmt6, "ss", $username, $_GET['verifya_id']);
      mysqli_stmt_execute($stmt6);
      echo "<script type='text/javascript'>alert('Verified successfully.');</script>";
  }
  if (isset($_POST['declinebutton'])) {
      $sql5 = "UPDATE notifications SET ntf_status=1 WHERE a_id=?";
      $stmt5 = mysqli_prepare($conn, $sql5);
      mysqli_stmt_bind_param($stmt5, "s", $_GET['verifya_id']);
      mysqli_stmt_execute($stmt5);
      echo "<script type='text/javascript'>alert('Declined successfully.');</script>";
  }
  mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UIUSAT - Specific Achievement</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="style.css">
    <link href="css/main.css" rel="stylesheet">
    <link href="nav.css" rel="stylesheet">
    <link rel="stylesheet" href="css/fontawesome/css/fontawesome.min.css" >

    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx"
      crossorigin="anonymous"
    />
    <!-- <link rel="stylesheet" href="style.css"> -->
    <!-- Search -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- search end -->
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"
      integrity="sha512-1sCRPdkRXhBV2PBLUdRb4tMg1w2YPf37qatUFeS7zlBy7jJI8Lf4VHwWfZZfpXtYSLy85pkm9GaYVYMfw5BC1A=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
      
    />
  </head>
  <body>
    <!-- Header -->
    
  <!-- Navbar -->
   
  <div class="site-nav"> 
         <!-- Nav bar Start -->
            <nav class="navbar navbar-expand-xl"style="width:1350px; position:relative; left:-85px ;Background-color:#cf8c10;">
	<a href="#" class="navbar-brand"style="position:relative; left:15px ;"><i class="fa fa-cube"></i>UIU<b>SAT</b></a>  		
	<button type="button" class="navbar-toggler" data-toggle="collapse" data-target="#navbarCollapse">
		<span class="navbar-toggler-icon"></span>
	</button>
	<!-- Collection of nav links, forms, and other content for toggling -->
	<div id="navbarCollapse" class="collapse navbar-collapse justify-content-start">		
		<!-- <form class="navbar-form form-inline">
			<div class="input-group search-box" style="position:relative; left:-10px">								
				<input type="text" id="search" class="form-control" placeholder="Search here...">
				<span class="input-group-addon"><i class="material-icons"></i></span>
			</div>
		</form> -->
    <form method="POST">
      <div class="search">
      <input type="text" name="searchtext" id="search" placeholder="Search" style="position:relative; top: 40px; width: 200px; left:-50px; padding:6px; border-radius:5px;">
      <!-- <div class="col col-lg-6"> -->
      <!-- <label for="exampleFormControlInput1" class="form-label">Choose From Below</label> -->
        <select class="form-select" name="searchtype" aria-label="Default select example" style="position:relative; left: 170px; width: 150px">
          <option selected>Filter</option>
          <option value="Students">Students</option>
          <option value="Verifiers">Verifiers</option>
          <option value="Achievements">Achievements</option>
          <option value="Events">Events</option>
          <option value="Notices">Notices</option>
        </select>
      <!-- </div> -->
        <!-- <button name="submitfiltersearch" class="btn">Search</button> -->
        <button name="submitsearch" class="btn" style="position:relative; top: -40px; left: 330px; background-color:#FFF; ">Search</button>
      </div>
    </form>
		<div class="navbar-nav ml-auto" style="position:relative; left:280px">
    <?php
      if(strlen($username)==9 && is_numeric($username)){
      ?>
        <a href="studenthome.php" class="nav-item nav-link active"><i class="fa fa-home"></i><span>Home</span></a>
      <?php
      }
      else{
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
<!-- form -->

<div class="wrapper">
    <div class="form">
    <div class="container">
      
  <div id="carouselExampleDark" class="carousel carousel-dark slide" data-bs-ride="carousel">
    <div class="carousel-inner ">
      <div class="carousel-item active azaira mt-3" data-bs-interval="10000">
        <!-- <img src="'event_images/'.'$event[image]'" class="d-block w-100" alt="..."> -->
      </div>
      <br></br>
    </div>
</div>
<div class="h6"><span class="text-muted h6"></span></div>
    <div class="myimg" style="display: flex;
  justify-content: center;"><img src="./images/<?php echo $achievement['file_link']; ?>" style="width:400px;height: 250px;border-radius: 10px; border: 6px solid #f08c09; padding: 3px;"></div>
    <div class="inputfield">
        <label><?php echo $achievement['category']; ?></label>
    </div>  
    <div class="inputfield">
        <label>Title: </label>
        <input type="text" class="input" value="<?php echo $achievement['name']; ?>" name="achievementtitle" readonly>
    </div> 
    <div class="inputfield">
        <label>Description: </label>
        <textarea type="text" rows="20" cols="50" class="input" name="achievementdescription" readonly><?php echo $achievement['description']; ?>
        </textarea>
        
    </div>
    <div class="inputfield">
        <label>Keywords: </label>
        <input type="text" class="input" value="<?php echo $achievement['keywords']; ?>" name="achievementkeywords" readonly>
    </div>
    <div class="inputfield">
        <label>External File Link: </label>
        <input type="text" class="input" value="<?php echo $achievement['external_file']; ?>" name="achievementtexternalfile" readonly>
    </div>
    <div class="inputfield">
        <label>Verification: </label>
        <?php
            if ($achievement['is_verified'] == 1) {
              echo ("Verified by " . $achievement['v_id']);
            }
            else {
              echo ("Not Verified");
            }
        ?>
    </div>
    

    
        <?php
        if(strlen($username)==9 && is_numeric($username) && $achievement['is_verified'] != 1){
        ?>
            <br></br>
            <form action="" method="POST">
            <div class="search">
            <label>Search Verifier To Tag </label>
            <input type="text" name="searchtext2" id="search" placeholder="Search" style="position:relative; top: 40px; width: 200px; left:-180px; padding:6px; border-radius:5px;">
            <button name="submitsearch2" class="btn" style="position:relative; top: 38px; left: -180px; background-color:#F50; ">Search</button>
        </div>
      </form>
            <div class="shadow-1-strong bg-white my-5 p-5" id="education">
            <div class="education-section"style="position: relative;top: 30px; background-color:#fff;height:1200px;">
            <!-- Here is the div that u need  -->
            
            <?php
            if(isset($_POST['submitsearch2'])){
                foreach($rows2 as $row2){
                $_SESSION['tagv_id']=$row2['v_id']; ?>
            <div class="notific">
            <div class="note1" style="height: 150px;
        width: 900px;
        background-color:#d18c25;
        border-radius: 0px;
        border-bottom: 5px solid #8143a0;
        border-right: 5px solid #8143a0;
        color: #fff;
        padding-bottom:15px ;
        position: relative;left:7px; "> 
        
        <div class="textbox"style="position:relative;left:50px;top:40px"><h5><?php echo htmlspecialchars($row2['name']) ?></h5></div>

        <div class="textdetail"style="position:relative;left:50px;top:40px">
                    <form action="" method="POST">
                    <button name="tagbutton" class="btn" style="position:relative; top: -40px; left: 330px; background-color:#F50; ">Tag</button>
                    </form>
                    </div>

                    <?php
                    
                    ?>
                    <?php
            }
        }
    }
    else if (strlen($username)==9 && is_numeric($username) && $achievement['is_verified'] == 0) {
    
      ?>
      <br></br>
            <form action="" method="POST">
            <div class="search">
            <button name="submitsearch2" class="btn" style="position:relative; top: 40px; left: 0px; background-color:#F50; ">Search</button>
        </div>
      </form>
    <?php
    }
    else if(!(strlen($username)==9 && is_numeric($username))){
      ?>
      <br></br>
            <form action="" method="POST">
            <div class="search">
            <button name="verifybutton" class="btn" style="position:relative; top: 40px; left: 0px; background-color:#F50; ">Verify</button>
            <button name="declinebutton" class="btn" style="position:relative; top: 40px; left: 0px; background-color:#F50; ">Decline</button>
        </div>
      </form>
    <?php
    }
    mysqli_close($conn);
    ?>
    
    
    </div>
</div>
</div>	

	
</body>
</html>