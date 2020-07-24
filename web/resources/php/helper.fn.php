<?php

function change_http_method(){
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST['_method'])){
            $_SERVER["REQUEST_METHOD"] = strtoupper($_POST['_method']);
        }
    }
}
change_http_method();