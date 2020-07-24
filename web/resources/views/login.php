<?php
    $auth = new \SimpleAuth\SimpleAuth();

$username = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if($auth->validate_login()){
        flash("Login successful!");
        header('Location: /');
        exit;
    } else {
        $username = $_POST['username'];
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
    <title>RTBE: Rich Client-server To-do List</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/4.5/examples/sign-in/">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
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
    <link href="resources/stylesheets/signin.css" rel="stylesheet">
  </head>
  <body class="text-center">
  <form class="form-signin" method="post">
    <img class="mb-4" src="resources/images/todolist.svg" alt="" width="72" height="72">
    <h1 class="h3 mb-3 font-weight-normal">Please sign in</h1>
    <label for="inputUsername" class="sr-only">Username</label>
    <input name="username" type="text" id="inputUsername" class="form-control" placeholder="Username/Email address" required autofocus value="<?php echo $username; ?>">
    <label for="inputPassword" class="sr-only">Password</label>
    <input name="password" type="password" id="inputPassword" class="form-control" placeholder="Password" required>
    <?php show_flash(); ?>
    <input type="hidden" name="_token" value="<?php echo $_SESSION['_token'];?>">
    <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    <a class="btn btn-lg btn-primary btn-block" href="/signup" role="button">Go to Sign up</a>
  </form>
  <script>
  var e = document.getElementById("inputUsername");
  if(e && e.value!=""){
    e.select();
  }
</script>
</body>
</html>
