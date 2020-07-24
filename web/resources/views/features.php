<?php
$auth = new \SimpleAuth\SimpleAuth();
$username = null;
if($auth->is_logged_in()){
    $username = $auth->user_name();
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <meta name="csrf-token" content="<?php echo $_SESSION['_token'] ?? 'no_csrf'; ?>" />
    <meta name="current-user" content="<?php echo $auth->is_logged_in()?'false':'false'; ?>" />
    <title>RTBE: Rich Client-server To-do List</title>
    <link href='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.0.3/css/font-awesome.css' rel='stylesheet'>
    <link href='resources/stylesheets/todo.css' rel='stylesheet'>
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <script>
        var sample_tasks = [
            "RTBE: 'Remember the Back-end'",
            "Meaning: Most of the heavy-lifting is done at the front end",
            "But: The backend handles authentication, DB access (CRUD) , security...",
            "Plus: Ad-hoc PHP framework was used. Back-end could be switched for any other backend, any language",
            "If not logged in: Changes will be saved locally",
            "Login or Register to save tasks to the server",
            "No php framework. Built from scratch",
            "Handles both logged in and guest use (via localStorage)",
            "Uses routing plugin, modified authentication class",
            "No JS Framework. javascript/jQuery",
            "Front end javascript: advanced, Single Page Application-like",
            "AJAX, handling intermediate states and errors, handle different threads/actions at the same time",
            "SQL DB, PDO (Sqlite for convenience). Avoids common DB security flaws",
            "Own implementation of CSRF protection",
            "RESTful, php returns json. Does not allow to change other user's tasks",
            "Dynamic UI. Inline editing",
            "Features (this)  To-Do list is clirnt-side only"
        ];

        var server_tasks = <?php list_tasks(true);?>;
        sample_tasks.reverse();

        for(var i in sample_tasks){
            server_tasks.push({name: sample_tasks[i], task_id:i})
        }
        //server_tasks = sample_tasks.concat(server_tasks);

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

<div class="page-content page-container" id="page-content">

    <main role="main" class="cover d-flex h-100 p-3 mx-auto flex-column">
        <h1 class="text-center">Features</h1>
        <p class="lead">Some highlights of this to-do app</p>
    </main>
    <div class="padding" style="padding-top: 0;">
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
        <div class="form-check show-text"><label class="form-check-label"> <input class="checkbox" type="checkbox"><span id="span_task_name_00"></span><i class="input-helper"></i></label></div><i class="edit_button remove mdi mdi-close-circle-outline"></i><i class="del_button remove mdi mdi-close-circle-outline"></i>
    </li>
</template>
</body>
</html>