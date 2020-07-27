<?php
/*
 * tip for serving static files directly

if (preg_match('/\.(?:png|jpg|jpeg|gif|svg|pdf)$/', $_SERVER["REQUEST_URI"])) {
    return false;    // serve the requested resource as-is.
}

*/


require_once('../vendor/autoload.php');
require_once('resources/php/helper.fn.php');

$router = new AltoRouter();

require_once('controllers.php');

// HTML Response Routes
$router->addRoutes([
    ['GET','/', 'resources/views/todo.php'], // ['GET','/', 'index'],
    ['POST','/', 'resources/views/todo.php'],
    ['GET','/features', 'resources/views/features.php'],
    ['GET','/tasks', 'list_tasks'],
    ['GET','/login', 'resources/views/login.php'],
    ['POST','/login', 'resources/views/login.php'],
    ['GET','/logout', 'logout'],
//    ['POST','/login', 'authenticate_login'],
    ]);

// JSON Response Routes
$router->addRoutes([
    ['POST','/task/create/', 'create_task'],
    ['POST','/task/import/', 'import_tasks'],
    ['PATCH','/task/complete/[i:id]/[a:complete]?/?', 'complete_task'],
    ['POST','/task/delete/[i:id]', 'delete_task'],
    ['PATCH','/task/edit/[i:id]', 'edit_task'],
    ['PATCH','/task/complete/[i:id]', 'complete_task'],
    ['PATCH','/task/pending/[i:id]', 'pending_task'],
    ]);

// Serve static files
$router->addRoutes([
    ['GET','/resources/[*:type]/[*:file]', 'serve_file'],
    ['GET','/favicon.ico', 'resources/images/favicon.ico'],
    ['GET','/signup', 'resources/views/signup.php'],
    ['POST','/signup', 'resources/views/signup.php'],
]);

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

/*
    Deprecated:
*/

function serve_file($type, $file){
    
    $last_modified_time = filemtime("resources/$type/" . $file);
    $etag = md5_file("resources/$type/".$file);

    header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified_time)." GMT");
    header("Etag: $etag");

    /* removed cache for debugging purposes.
    if (@strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified_time ||
        (array_key_exists("HTTP_IF_NONE_MATCH", $_SERVER) &&
        (trim($_SERVER['HTTP_IF_NONE_MATCH']) == $etag))){
            header("Cache-Control: public, max-age=31536000");
            header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 30)));
            header("HTTP/1.1 304 Not Modified");
            exit;
    }
    */
    

    $ext = pathinfo($file, PATHINFO_EXTENSION);//var_dump($mime_types);
    //define('MIME_TYPES_URL', 'http://svn.apache.org/repos/asf/httpd/httpd/trunk/docs/conf/mime.types');
    require('resources/php/mimetypes.php');
    header("Cache-Control: public, max-age=31536000");
    header('Expires: '.gmdate('D, d M Y H:i:s \G\M\T', time() + (60 * 60 * 24 * 30)));
    header("Content-type: {$mime_types[$ext]}"); //image/svg+xml
    readfile("resources/$type/$file");
}

