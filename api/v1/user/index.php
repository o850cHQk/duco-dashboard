<?php
    /*
    Simple API
    
    */
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Check if logged in
        if (!isset($_COOKIE['id'])) {
            http_response_code(401);
            die(json_encode(array("message"=>"You are not logged in")));
        }
        // Okay get the last 20 results
        require('../config.php');
        require('../db.php');
        // get the last time it was checked
        $userID = $mysql->real_escape_string($_COOKIE['id']);
        $sql_query = 'SELECT userID, userName, lastChecked FROM users WHERE userID = ' . $userID . ' LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // something went wrong maybe log out and log back in?
            http_response_code(400);
            die(json_encode(array("message"=>"Unable to get your details, try logging out and logging in again.")));
        }
        $db_user = $result->fetch_assoc();
        $json = array(
            'id'=>$db_user['userID'],
            'name'=>$db_user['userName'],
            'lastChecked'=>$db_user['lastChecked'],
            'overview'=>array(),
            'message'=>''
        );
        $sql_query = 'SELECT * FROM (SELECT * FROM overview WHERE userID = ' . $userID . ' ORDER BY overviewID DESC LIMIT 20) AS overviews ORDER BY overviewID ASC';
        $result = $mysql->query($sql_query);
        $rows = $result->num_rows;
        if ($rows == 0) {
            http_response_code(400);
            die(json_encode(array("message"=>"The dashboard has not retreived any information about your wallet yet, please give it a minute.")));
        }
        if ($rows > 1) {
            while($row = $result->fetch_assoc()) {
                array_push($json['overview'], $row);
            }
        }
        http_response_code(200);
        die(json_encode($json));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
