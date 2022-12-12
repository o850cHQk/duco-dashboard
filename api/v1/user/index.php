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
        // get a start date and check for end
        if (isset($_GET['startDate'])) {
            $startDate =  $mysql->real_escape_string($_GET['startDate']);
            $endDate = '';
            if (isset($_GET['endDate'])) {
                $endDate =  $mysql->real_escape_string($_GET['startDate']);
            } else {
                $endDate =  $_SERVER['REQUEST_TIME'];
            }
            $sql_query = 'SELECT * FROM overview WHERE userID = ' . $userID . ' AND checkedTime BETWEEN ' . $startDate . ' AND ' . $endDate . ' ORDER BY checkedTime ASC';
            $result = $mysql->query($sql_query);
            $rows = $result->num_rows;
            if ($rows == 0) {
                $json['message'] = "The dashboard has not retreived any information in this date range. If you have just signed in it will take a minute for data to appear on your dashboard.";
            }
            if ($rows > 1) {
                while($row = $result->fetch_assoc()) {
                    array_push($json['overview'], $row);
                }
            }
        }
        http_response_code(200);
        die(json_encode($json));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
