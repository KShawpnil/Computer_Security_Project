<?php



/*include('db_connect.php'); */
session_start();
ob_start();
if(isset($_GET['d_id']) && isset($_SESSION['username'])) {

    include('db_connect.php');
    $id = $_GET["d_id"];

    $sql9 = "DELETE FROM achievements WHERE a_id=?";
    $query = mysqli_prepare($conn, $sql9);
    if($query){
        mysqli_stmt_bind_param($query, "i", $id);
        $result=mysqli_stmt_execute($query);  
        if($result) {
            header("Location: profile.php");
            exit();
        } 
        else {
            echo "Failed brah ";
        }
        mysqli_stmt_close($query);
    }
}
else if(!isset($_SESSION['username'])){
    header("HTTP/1.0 404 Not Found");
    echo "<h1>404 Not Found</h1>";
    echo "The page that you have requested could not be found.";
    exit();
}
else {
    header("Location: profile.php");
}

