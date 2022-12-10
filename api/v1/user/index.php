<?php
    /*
    Simple API
    
    */
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        session_start();
        // Check if logged in
        if (!isset($_SESSION['id'])) {
            http_response_code(400);
            die(json_encode(array("message"=>"You are not logged in")));
        }
        // Okay get the last 20 results
        require('../config.php');
        require('../db.php');
        // get the last time it was checked
        $sql_query = 'SELECT lastChecked FROM users WHERE userID = ' . $_SESSION['id'] . ' LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // something went wrong maybe log out and log back in?
            http_response_code(400);
            die(json_encode(array("message"=>"Unable to get your details, try logging out and logging in again.")));
        }
        $db_user = $result->fetch_assoc();

        $json = array(
            'id'=>$_SESSION['id'],
            'name'=>$_SESSION['name'],
            'lastChecked'=>$db_user['lastChecked'],
            'statistics'=>array(),
            'message'=>''
        );
        $sql_query = 'SELECT balance, stakeAmount, verified, minersTotal, hashrateTotal FROM ducostats WHERE userID = ' . $_SESSION['id'] . ' LIMIT 20';
        $result = $mysql->query($sql_query);
        if ($rows == 0) {
            http_response_code(400);
            die(json_encode(array("message"=>"You are not assigned to a conference. Please contact an Administrator to resolve this.")));
        }
        if ($rows > 1) {
            
            while($row = $result->fetch_assoc()) {
                array_push($json['conferences'], $row);
            }
            http_response_code(202);
            die(json_encode($json));
        }
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
