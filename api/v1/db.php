<?php
    # Connect to the database
    $mysql = new mysqli($database_host, $database_user, $database_pass, $database_name);

    # Check if the database connects
 
    if (!is_null($mysql->connect_error)) {
        # The database didnt connect, might have to put some kind of error and kill in here you know.
        $json = array("error"=>"nodb", "msg"=>"Unable to connect to database: " . $mysql->connect_error);
        die(json_encode($json));
    }
?>
