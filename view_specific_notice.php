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

if(isset($_GET['n_id'])){
  $n_id = $_GET['n_id'];
  $notice_id = mysqli_real_escape_string($conn, $n_id);

  $sql = "SELECT * FROM notices WHERE n_id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "s", $notice_id);
  mysqli_stmt_execute($stmt);

  $result = mysqli_stmt_get_result($stmt);
  $notice = mysqli_fetch_assoc($result);
  mysqli_free_result($result);
  mysqli_stmt_close($stmt);
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

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>UIUSAT - Specific Notice</title>
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
            <nav class="navbar navbar-expand-xl"style="position:relative; left:-55px ; width: 1400px; Background-color:#cf8c10;">
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
      <input type="text" name="searchtext" id="search" placeholder="Search" style="position:relative; top: 40px; width: 200px; left: -33px; padding:6px; border-radius:5px;">
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
    <div class="inputfield">
        <label>Headline: </label>
        <input type="text" class="input" value="<?php echo htmlspecialchars($notice['name'], ENT_QUOTES, 'UTF-8'); ?>" name="noticename" readonly>
    </div>

    <div class="inputfield">
        <label>Posted On: </label>
        <input type="text" class="input" value="<?php echo htmlspecialchars($notice['post_date'], ENT_QUOTES, 'UTF-8'); ?>" name="noticepostdate" readonly>
    </div>

    <div class="inputfield">
        <label>Content: </label>
        <textarea type="text" rows="20" cols="50" class="input" name="noticecontent" readonly><?php echo htmlspecialchars($notice['content'], ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="inputfield">
        <label>Keywords: </label>
        <input type="text" class="input" value="<?php echo htmlspecialchars($notice['keywords'], ENT_QUOTES, 'UTF-8'); ?>" name="noticekeywords" readonly>
    </div>

    <div class="inputfield">
        <label>Posted By: </label>
        <input type="text" class="input" value="<?php echo htmlspecialchars($notice['v_id'], ENT_QUOTES, 'UTF-8'); ?>" name="noticeverifier" readonly>
    </div>

</div>
</div>	

	
</body>
</html>