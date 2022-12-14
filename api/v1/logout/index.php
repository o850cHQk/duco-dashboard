<?php
    /*
        Logout API:
    */
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        setcookie( "id", "", 1, "/" );
        setcookie( "timeframe", "1800", 1, "/" );
        http_response_code(200);
        die(json_encode(array("message"=>"You have logged out successfully")));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
