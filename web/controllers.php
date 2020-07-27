<?php

require_once('../vendor/autoload.php');
use SimpleAuth\SimpleAuth;

$auth = new SimpleAuth();
$conn = $auth->get_conn();


function redi(){
    global $auth;
    if($auth->bad_login() || true){
        flash("Incorrect Credentials. Please log in again");
        header('Location: /login');
    }
}

function test(){
    $title = "sembusak";
    include('resources/views/header.php');
    include('resources/views/test.php');
}

function test2(){
    //$title = "sembusak";
    //include('resources/views/header.php');
    include('resources/views/test2.php');
}

function arena(){
    include('resources/views/arena.php');
}

function index(){
    global $auth;
    $title = "Home";
    include('resources/views/header.php');
    include('resources/views/navbar.php');
    if ($auth->is_logged_in()){
        include('resources/views/task_list.php');
    } else {
        include('resources/views/guest.php');
    }
    include ('resources/views/footer.php');
}
function check_login(){
    global $auth;
    if(!$auth->is_logged_in()){
        header('HTTP/1.0 403 Forbidden');
        exit;
    }
    return $auth->user_id();
}

function get_login(){
    include('resources/views/header.php');
    include('resources/views/navbar.php');
    include('resources/views/login.php');
    include('resources/views/footer.php');
}

function post_login(){
    global $auth;
    if($auth->is_logged_in()){
        header("Location: /");
    }else{
        flash("Bad Login Credentials");
        include('resources/views/header.php');
        include('resources/views/navbar.php');
        include('resources/views/login.php');
        include('resources/views/footer.php');
    }
}

function logout(){
    global $auth;
    $auth->logout();
    $auth->feedback = 'Logged out';
    flash('Logged out');
    header("Location: /");
}
function get_signup(){
    global $auth;
    $title = 'Sign Up';
    include('resources/views/header.php');
    include('resources/views/navbar.php');
    $auth->show_signup_form();
    include('resources/views/footer.php');
}

function post_signup(){
    global $auth;
    if($auth->create_new_user()) {
        include('resources/views/signup_confirmation.php');
    } else {
        $title = 'Sign Up';
        include('resources/views/header.php');
        include('resources/views/navbar.php');
        $auth->show_signup_form();
        include('resources/views/footer.php');
    }
}

function delete_task($id, $echo = true){
    global $conn;
    $user_id = check_login();
    // create new task
    $sql = 'DELETE FROM tasks WHERE user_id = ? AND task_id = ?';//   INSERT INTO tasks (user_id, name) VALUES (?,?)';
    $ret = [];
    if(execute_sql($sql, array($user_id, $id))){
        $ret['success'] = true;
    } else{
        $ret['success'] = false;
        $ret['user'] = $user_id;
        $ret['task'] = $id;
    }
    if($echo){
        echo json_encode($ret);
    } else {
        flash('Task deleted');
        header("Location: /");
        return $ret;
    }
}

function import_tasks($echo = true){
    global $conn, $auth;

    if(!$auth->is_logged_in()){
        exit("[];");
    }

    $user_id = $auth->user_id();

    $sql = 'INSERT INTO tasks (user_id, name, status) VALUES (?,?,?)';
    $ret = [];
    $success = [];
    $errors = false;
    if(isset($_POST["import"])){
        $q = $conn->prepare($sql);
        foreach($_POST["import"] as $index => $task){
            //error_log(json_encode($_POST['import']));
            $status = (isset($task['completed']) && $task['completed']!= 'false') ? 'completed': 'pending';
            $task_name = $task["task_name"];
            if($task_name == ''){
                continue;
            }
            //assert(strlen(trim($task_name)) > 0);
            if(execute_sql($sql, [$user_id, $task_name, $status])){
                $success[$index] = $conn->lastInsertId();;
            } else {
                $success[$index] = false;
                $errors = true;
            }
        }
        $ret['success'] = !$errors;
        if($errors){
            $ret['error'] = 'check details, true = imported, false= not imported';
        }
        $ret['details'] = $success;
    } else{
        $ret['success'] = false;
        $ret['error'] = 'missing data';
    }
    if ($echo){
        exit(json_encode($ret));
    }
}

function create_task($echo = true){
    global $conn;
    $user_id = check_login();
    // create new task
    $sql = 'INSERT INTO tasks (user_id, name) VALUES (?,?)';
    $ret = [];

    if(execute_sql($sql, array($user_id, $_POST['task_name']))){
        $ret['response'] = true;
        $ret['id'] = $conn->lastInsertId();
    } else{
        $ret['response'] = false;
    }
    if($echo){
        header('Content-Type: application/json');
        exit(json_encode($ret));
        //echo json_encode($ret);
    } else {
        flash('Task created.');
        header("Location: /");
        return $ret;
    }
}

function complete_task($id){
    return complete_pending_task($id, 'complete');
}
function pending_task($id){
    return complete_pending_task($id, 'pending');
}

function complete_pending_task($id, $complete_pending = 'complete'){
    global $conn;
    $user_id = check_login();
    // create new task
    if($complete_pending === 'complete'){
        $sql = "UPDATE tasks SET status = 'completed', completed = datetime('now','localtime') WHERE user_id = ? AND task_id = ?";
    } else {
        //pending
        $sql = "UPDATE tasks SET status = 'pending', created_updated = datetime('now','localtime'), completed = NULL WHERE user_id = ? AND task_id = ?";
    }

    $ret = [];
    if(execute_sql($sql, array($user_id, $id))){
        $ret['success'] = true;
    } else{
        $ret['success'] = false;
    }
    //header('Content-Type: application/json');
    exit(json_encode($ret));
}



function edit_task($id){
    global $conn;
    $user_id = check_login();
    if (!array_key_exists('task_name', $_POST) || strlen($_POST['task_name']) < 1) {
        header('Content-Type: application/json');
        exit(json_encode(['response' => 'error']));
    } else {
        $task_name = $_POST['task_name'];
    }
    // create new task
    $sql = "UPDATE tasks SET name = ?, created_updated = datetime('now','localtime') WHERE user_id = ? AND task_id = ?";
    $ret = [];
    if(execute_sql($sql, array($task_name,$user_id, $id))){
        $ret['success'] = true;
    } else{
        $ret['success'] = false;
    }
    header('Content-Type: application/json');
    //echo 'sem';
    exit(json_encode($ret));
}

function list_tasks($echo = true){
    global $conn, $auth;

    if(!$auth->is_logged_in()){
        echo("[]");
        return;
    }

    $user_id = $auth->user_id();


    $sql = 'SELECT task_id, task_id as server_id, user_id, name as task_name, status FROM tasks WHERE user_id = :user_id ORDER BY created_updated, completed DESC';
    $query = $conn->prepare($sql);
    $query->bindValue(':user_id', $user_id);
    $query->execute();
    $tasks = [];
    while($row = $query->fetch(PDO::FETCH_ASSOC)){
        if($row['status'] == 'completed'){
            $row['completed'] = true;
        } else{
            $row['completed'] = false;
        }
        unset($row['status']);
        $tasks[] = $row;
    }
    if($echo){
        //echo json_encode(array('pending' => $pending, 'completed' => $completed));
        echo json_encode($tasks);
    } else {
        return $tasks;
    }
}

function record_exists($sql, $values){
    global $conn;
    $query = $conn->prepare($sql);
    $query->execute($values);
    if($query->fetch()){
        return true;
    }
    return false;
}

function execute_sql ($sql, $values, $q=null){
    global $conn;
    if($q){
        $query = $q;
    }else{
        $query = $conn->prepare($sql);
    }
    $query->execute($values);
    if($query->rowCount() > 0){
        return true;
    }
    return false;
}

function complete_task_old($id, $complete = 'complete', $echo = true){
    check_login();
    $sql = 'SELECT task_id FROM tasks WHERE user_id = ? and task_id = ? ';
    $params = [];
    $ret = false;
    if(record_exists($sql, array($_SESSION['user_id'], $id))) {
        if($complete){
            $sql = 'UPDATE tasks SET status = `completed`, completed = ?';
            $params[0] = "=datetime('now','localtime')";
        } else {
            $sql = 'UPDATE tasks SET status = `pending`, completed = NULL';
        }
        if(execute_sql($sql, $params)){
            $ret = array('response' => true);
        }
    }
    if(!$ret){
        $ret = array('response' => false);
    }
    if($echo){
        echo json_encode($ret);
    }else{
        return $ret;
    }
}

function flash($message){
    if (!isset($_SESSION['message']) || !is_array($_SESSION['message'])){
        $_SESSION['message'] = [];
    }
    $_SESSION['message'][]= $message;
}