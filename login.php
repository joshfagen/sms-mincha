<?php
include('db.php');
if (isset($_SESSION['user'])) {
    header("Location: ".BASE_URL."listing.php");
}
$logiErr = '';
$emptyrecord = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $password = $_POST['password'];


    if(!empty($email) and !empty($password)){


        $statement = $connection->prepare("SELECT email, password FROM admin_table");
        $statement->execute();
        $result = $statement->fetch();

        if(($email == $result['email']) and (md5($password) == $result['password'])){

            $_SESSION['user'] = $email;

            header("Location: ".BASE_URL."listing.php");

        }else{

            $logiErr =  "Username or Password Incorrect";
        }
    }else{

        $emptyrecord = 'Please Fill Out This Fields';
    }

}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" />
    <script src="https://cdn.datatables.net/1.10.12/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.12/js/dataTables.bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.12/css/dataTables.bootstrap.min.css" />
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

    <meta charset="UTF-8">
    <title>Title</title>
    <style>
        /*@import "bourbon";*/

        body {
            background: #eee !important;
        }

        .wrapper {
            margin-top: 80px;
            margin-bottom: 80px;
        }

        .form-signin {
            max-width: 380px;
            padding: 15px 35px 45px;
            margin: 0 auto;
            background-color: #fff;
            border: 1px solid rgba(0,0,0,0.1);

        .form-signin-heading,
        .checkbox {
            margin-bottom: 30px;
        }

        .checkbox {
            font-weight: normal;
        }

        .form-control {
            position: relative;
            font-size: 16px;
            height: auto;
            padding: 10px;
        @include box-sizing(border-box);

        &:focus {
             z-index: 2;
         }
        }

        input[type="text"] {
            margin-bottom: -1px;
            border-bottom-left-radius: 0;
            border-bottom-right-radius: 0;
        }

        input[type="password"] {
            margin-bottom: 20px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
        }
        .button {
            width: 115px;
            height: 25px;
            background: #4E9CAF;
            padding: 10px;
            text-align: center;
            border-radius: 5px;
            color: white;
            font-weight: bold;
        }

    </style>
</head>
<body>
<div class="wrapper">
    <form class="form-signin" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" autocomplete="off">
<h4 style="color: red"><?php echo $logiErr ?></h4>

<h4 style="color: red"><?php echo $emptyrecord ?></h4>
        <div class="col-md-12" style="text-align: center">
            <h3 class="form-signin-heading">Twilio Sms Application</h3>
        </div>
        <div class="row">
            <div class="col-md-12">
                <input type="text" class="form-control" name="email" placeholder="Email Address" autofocus=""/>
            </div>
        </div>  
        <div class="row">
                <div class="col-md-12">
                 <input type="password" class="form-control" name="password" placeholder="Password"/>
                </div>
        </div>
        <button class="btn btn-lg btn-primary btn-block" value="login" type="submit">Login</button>
    </form>
</div>

</body>
</html>