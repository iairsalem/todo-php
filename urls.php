<?php
/*
 * tip for serving static files directly
if (preg_match('/\.(?:png|jpg|jpeg|gif|svg|pdf)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}
*/

require 'vendor/autoload.php';
require 'helper.fn.php';

$router = new AltoRouter();

require('views.php');

// HTML Response Routes
$router->addRoutes([
    ['GET','/', 'templates/test2.php'], // ['GET','/', 'index'],
    ['POST','/', 'templates/test2.php'], // ['GET','/', 'index'],
    ['GET','/features', 'templates/features.php'],
    ['GET','/arena', 'arena'],
    ['GET','/test2', 'test2'],
    ['GET','/redi', 'redi'],
    ['GET','/tasks', 'list_tasks'],
    ['GET','/login', 'templates/login.php'],
    ['POST','/login', 'templates/login.php'],
    ['GET','/logout', 'logout'],
    ['GET','/register', 'get_signup'],
    ['GET','/signup', 'templates/signup.php'],
    ['POST','/signup', 'templates/signup.php'],
    ['POST','/register', 'post_signup'],
    ['POST','/task/create/', 'create_task'],
    ['POST','/task/import/', 'import_tasks'],
    ['POST','/login', 'authenticate_login'],
    ]);

// JSON Response Routes
$router->addRoutes([
    ['PATCH','/task/complete/[i:id]/[a:complete]?/?', 'complete_task'],
    ['POST','/task/delete/[i:id]', 'delete_task'],
    ['PATCH','/task/edit/[i:id]', 'edit_task'],
    ['PATCH','/task/complete/[i:id]', 'complete_task'],
    ['PATCH','/task/pending/[i:id]', 'pending_task'],
    ]);

// Serve static files
$router->addRoutes([
    ['GET','/static/[*:file]', 'serve_file'],
]);


function serve_file($file){
    error_reporting(E_ALL ^ E_NOTICE);
    //$file = 'myfile.php';
    $last_modified_time = filemtime("static/" . $file);
    $etag = md5_file("static/".$file);

    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
    header("Etag: $etag");

    if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
        (array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER) &&
        (trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag))){
            header("Cache-Control: public, max-age=31536000");
            header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 30)));
            header("HTTP/1.1 304 Not Modified");
            exit;
    }


    $ext = pathinfo($file, PATHINFO_EXTENSION);//var_dump($mime_types);
    //define('MIME_TYPES_URL', 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
    require('mimetypes.php');
    header("Cache-Control: public, max-age=31536000");
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 30)));
    header("Content-type: {$mime_types[$ext]}"); //image/svg+xml
    readfile('static/'.$file);
    //echo file_get_contents('static/'.$file);
}

$match = $router->match();

if( is_array($match) && is_callable( $match['target'] ) ) {
    call_user_func_array( $match['target'], $match['params'] );
} else {
    if(isset($match['target']) && file_exists($match['target'])){
        include($match['target']);
    } else{
        // no route was matched
        header( $_SERVER["SERVER_PROTOCOL"] . ' 404 Not Found');
        echo isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']: '/';
        echo "\n404";
    }
}

