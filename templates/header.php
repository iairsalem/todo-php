<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?php echo $title; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
    <meta name="csrf-token" content="<?php echo $_SESSION['_token']; ?>" />
    <style>
        #completed_tasks li{
            text-decoration: line-through;
            color: DarkGrey;
        }
        #completed_tasks .btn_edit{
            display: none;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"
            integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
            crossorigin="anonymous"></script>
</head>
<body>
