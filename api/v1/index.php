<?php
    /*
    Simple API
    
    */
    header('Content-Type: application/json');
    require_once('config.php');
    // Check if single mode
    $json = array(
            'mode'=>$user_mode,
            'userName'=>false);
    if ($json['mode'] == 'single') {
        // single mode set the logged in user as the one in the config
        $json['userName'] = $single_user;
        http_response_code(200);
        die(json_encode($json));
    }
    session_start();
    if (isset($_SESSION['userName'])) {
        $json['userName'] = $single_user;
    }
    http_response_code(200);
    die(json_encode($json));
?>
