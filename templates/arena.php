<html>
<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
</head>
<body>
<script>
    var rajambie = 0;
    var jqxhr = $.ajax( "example.php" )
        .done(function() {
            alert( "success" );
        })
        .fail(function() {
            alert( "error" );
            rajambie = 1;
        })
        .always(function() {
            alert( "complete" );
        });
    alert(rajambie);
</script>
</body>
</html>
<?php

