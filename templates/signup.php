<?php
$auth = new \SimpleAuth\SimpleAuth();
$username = null;

if($auth->is_logged_in()){
    $username = $auth->user_name();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if($auth->create_new_user()){
        //echo 'much wow';
        flash("Registration successful!");
        $_SESSION['message'] = ['Registration mega Successful!'];
        //header('Location: /');
    } else {
        flash($auth->feedback);
    }
}


function show_flash(){
    if(isset($_SESSION['message']) && is_array($_SESSION['message'])){
        foreach($_SESSION['message'] as $msg){
            ?>
            <div class="alert alert-primary" role="alert">
                <?php echo $msg; ?>
            </div>
            <?php
        }
        unset($_SESSION['message']);
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Jekyll v4.0.1">
    <title>Signin Template · Bootstrap</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.5/examples/sign-in/">

    <!-- Bootstrap core CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
    </style>
    <!-- Custom styles for this template -->
    <link href="static/signin.css" rel="stylesheet">
</head>
<template>

    <?php
    echo "hola";
    echo var_dump($_SESSION) . "kipe";?>
</template>
<body class="text-center">
<form class="form-signin" method="post">
    <img class="mb-4" src="static/todolist.svg" alt="" width="72" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Sign up!</h1>
    <label for="inputUsername" class="sr-only">Username</label>
    <input name="username" type="text" id="inputUsername" class="form-control" placeholder="Username" required autofocus>
    <label for="inputEmail" class="sr-only">Email</label>
    <input name="email" type="email" id="inputUsername" class="form-control" placeholder="Email address" required>
    <label for="inputPassword" class="sr-only">Password</label>
    <input name="password_new" type="password" id="inputPassword" class="form-control" placeholder="Password" required>
    <label for="inputPasswordRepeat" class="sr-only">Repeat Password</label>
    <input name="pass_new_repeat" type="password" id="inputPasswordRepeat" class="form-control" placeholder="Repeat Password" required>
    <?php show_flash(); ?>
    <input type="hidden" name="_token" value="<?php echo $_SESSION['_token'];?>">
    <button name="register" class="btn btn-lg btn-primary btn-block" type="submit">Sign up</button>
</form>
</body>
</html>
