<?php  

if (isset($_POST['login-submit'])) {

    require 'dbh.inc.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        //header("Location: ../../index.php?error=emptyfields");
        echo "empty fields !!!";
        exit();
    }

    else {
        $sql = "SELECT * FROM user WHERE username=?;";
        $stmt = mysqli_stmt_init($conn);
        if (!mysqli_stmt_prepare($stmt, $sql)) {
            header("Location: ../../index.php?error=sqlerror");
            exit();
        }

        else {
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if ($row = mysqli_fetch_assoc($result)) {
                
                if ($password != $row['password']) {
                    header("Location: ../../index.php?error=wrongpassword");
                    exit();
                }

                else {
                    session_start();
                    $_SESSION['userId'] = $row['id'];
                    $_SESSION['name'] = $row['username'];
                    header("Location: ../../importDataFromExcelFile.php");
                    exit();
                }   

            }
            
            else {
                header("Location: ../../index.php?error=nouser");
                exit();
            }
        }
    }

}

else {
    header("Location: ../index.php");
    exit();
}

?>