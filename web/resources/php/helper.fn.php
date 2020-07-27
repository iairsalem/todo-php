<?php

function change_http_method(){
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST['_method'])){
            $_SERVER["REQUEST_METHOD"] = strtoupper($_POST['_method']);
        }
    }
}
change_http_method();


function get_branch_head($branch){
    $url = 'https://api.github.com/repos/iairsalem/todo-php/branches/'. $branch;
    $json = file_get_contents($url);
    if($json){
        $json = json_decode($json);
        if($json && $json['commit']  && $json['commit']['sha'] )
        $sha = $json['commit']['sha'];
        return substr($sha,0,7);
    }
}