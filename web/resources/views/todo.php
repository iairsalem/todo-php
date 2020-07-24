<?php
$auth = new \SimpleAuth\SimpleAuth();
$username = null;
if($auth->is_logged_in()){
    $username = $auth->user_name();
}
if(isset($_SESSION['bad_login']) && $_SESSION['bad_login']){
    $auth->clear_login();
    flash("Incorrect Credentials. Please log in again");
    //$_SESSION['lapapa'] = 'lapapa++';
    unset($_SESSION['bad_login']);
    header('Location: /login');
} else{
    //$_SESSION['lapapa'] = 'lapapa++';
    //$_SESSION['bad_login'] = false;
}
//echo print_r($_SESSION);
function show_flash(){
    if(isset($_SESSION['message']) && is_array($_SESSION['message'])){
        foreach($_SESSION['message'] as $msg){
            ?>
            <div class="alert alert-primary" role="alert">
                <strong><?php echo $msg; ?></strong>
            </div>
            <?php
        }
        unset($_SESSION['message']);
    }
}

?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name="csrf-token" content="<?php echo $_SESSION['_token'] ?? 'no_csrf'; ?>" />
    <meta name="current-user" content="<?php echo $auth->is_logged_in()?'connected':'false'; ?>" />
    <title>RTBE: Rich Client-server To-do List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css' rel='stylesheet'>
    <link href='resources/stylesheets/todo.css' rel='stylesheet'>
    <script>
        var server_tasks = <?php list_tasks(true);?>;
    </script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
    <script type='text/javascript' src='https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js'></script>
    <script type='text/javascript' src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'></script>
    <script type='text/javascript' src="resources/js/todo.js"></script>
    <script type='text/javascript' src="resources/js/helperfn.js"></script>
    <script src="resources/js/jstorage.js"></script>
</head>
<body>
<?php include('todo_navbar.php');?>
<?php
show_flash();
if(!$username){

    ?>
 <main role="main" class="cover d-flex h-100 p-3 mx-auto flex-column">
    <h1 class="text-center">Welcome, guest</h1>
     <p class="lead">This To-Do List is yours.</p>
     <p class="lead">
         <a href="/features" class="btn btn-lg btn-secondary">Features</a>
     </p>
</main>

<?php
} else {
?>
    <main role="main" class="cover d-flex h-100 p-3 mx-auto flex-column">
        <h1 class="text-center">Tasks</h1>
    </main>
<?php    
}
?>
<div class="page-content page-container" id="page-content">
    <div class="padding" style="padding-top: 50px;">
        <div class="row container d-flex justify-content-center mx-auto">
            <div class="col-lg-12">
                <div class="card px-3">
                    <div class="card-body">
                        <h4 class="card-title">Your To-do List:</h4>
                        <div class="add-items d-flex"><input id="txt_add" name="txt_add" type="text" class="form-control todo-list-input" placeholder="What do you need to do today?"> <button id="btn_add_task" class="add btn btn-primary font-weight-bold todo-list-add-btn">Add</button></div>
                        <div class="list-wrapper">
                            <ul class="d-flex flex-column-reverse todo-list">
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<template id="edit_template">
    <input id="txt_edit_00" type="text" class="edit-input form-control input-lg todo-list-input" value="">
    <button class="mr-auto btn btn-primary btn-sm save_button" type="button">Save</button>
    <button class="mr-auto btn btn-secondary btn-sm cancel_button" type="button">Cancel</button>
</template>
<template id="task_template">
    <li id='task_00'>
        <div class="form-check"><label class="form-check-label"> <input class="checkbox" type="checkbox"><span id="span_task_name_00"></span><i class="input-helper"></i></label></div><i class="edit_button remove mdi mdi-close-circle-outline"></i><i class="del_button remove mdi mdi-close-circle-outline"></i>
    </li>
</template>
</body>
</html>