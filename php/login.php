<?php
    session_start();
    if(isset($_SESSION['userId'])) {
        header("location: ../importDataFromExcelFile.php");
        exit;
    }

    require '../php/includes/dbh.inc.php';

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">

        <title>Startmin - Bootstrap Admin Theme</title>

        <!-- Bootstrap Core CSS -->
        <link href="../css/bootstrap.min.css" rel="stylesheet">

        <!-- MetisMenu CSS -->
        <link href="../css/metisMenu.min.css" rel="stylesheet">

        <!-- Custom CSS -->
        <link href="../css/startmin.css" rel="stylesheet">

        <!-- Custom Fonts -->
        <link href="../css/font-awesome.min.css" rel="stylesheet" type="text/css">

        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>

        <div class="container">
            <div class="row">
                <div class="col-md-4 col-md-offset-4">
                    <div class="login-panel panel panel-default">
                        <div class="panel-heading">
                            
                            
                                <?php
                                  $notification = "Please Sign In";

                                  if (isset($_POST['login-submit'])) {
                                    
                                    $username = $_POST['username'];
                                    $password = $_POST['password'];

                                    if (empty($username) || empty($password)) {
                                        $notification = "Please Enter Username and Password";
                                    } else {
                                        $sql = "SELECT * FROM user WHERE username=?;";
                                        $stmt = mysqli_stmt_init($conn);
                                        if (!mysqli_stmt_prepare($stmt, $sql)) {
                                            $notification = "Error ! Please Contact Admin !";
                                        }
                                        else {
                                            mysqli_stmt_bind_param($stmt, "s", $username);
                                            mysqli_stmt_execute($stmt);
                                            $result = mysqli_stmt_get_result($stmt);

                                            if ($row = mysqli_fetch_assoc($result)) {
                                                
                                                if ($password != $row['password']) {
                                                    $notification = "Username or Password Incorrect";
                                                }

                                                else {
                                                    //session_start();
                                                    $_SESSION['userId'] = $row['id'];
                                                    $_SESSION['name'] = $row['username'];
                                                    header("Location: ../importDataFromExcelFile.php");
                                                    exit();
                                                }   

                                            }
                                            
                                            else {
                                                $notification = "Username or Password Incorrect";
                                            }
                                        }
                                    }

                                  }

                                  else {
                                      
                                  }

                                if ($notification === "Please Sign In") {
                                    echo '<h3 class="panel-title">Please Sign In</h3>';
                                } else {
                                    echo '<h3 class="panel-title" color="#FF0000" >' . $notification . '</h3>';
                                }

                            ?>
                            
                        </div>
                        <div class="panel-body">
                            <form role="form" action="" method="post" name="frmLogin" id="frmLogin">
                                <fieldset>
                                    <div class="form-group" >
                                        <input class="form-control" placeholder="Username" name="username" type="text" autofocus>
                                    </div>
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Password" name="password" type="password" value="">
                                    </div>
                                    <!-- Change this to a button or input when using this as a form -->
                                    <button type="submit" id="submit" name="login-submit" class="btn btn-lg btn-success btn-block">Login</button>
                                </fieldset>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        

        <!-- jQuery -->
        <script src="../js/jquery.min.js"></script>

        <!-- Bootstrap Core JavaScript -->
        <script src="../js/bootstrap.min.js"></script>

        <!-- Metis Menu Plugin JavaScript -->
        <script src="../js/metisMenu.min.js"></script>

        <!-- Custom Theme JavaScript -->
        <script src="../js/startmin.js"></script>





    </body>
</html>
