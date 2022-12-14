<?php
    /*
        Runner API:
    */
    require('../config.php');
    header('Content-Type: application/json');
    if (!isset($_GET['api']) || $node_Password == 'ChaNgEmE!') {
        http_response_code(401);
        die(json_encode(array("message"=>"API Key not set")));
    }
    if ($_GET['api'] != $node_Password) {
        http_response_code(401);
        die(json_encode(array("message"=>"Invalid API")));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // okay load basic stuff and grab the oldest checked user
        require('../db.php');
        $sql_query = 'SELECT lastChecked, userID, userName FROM users ORDER BY lastChecked LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // sddatabase problem
            http_response_code(500);
            die(json_encode(array("message"=>"Database Error")));
        }
        $db_user = $result->fetch_assoc();
        $json = array(
            'id'=>$db_user['userID'],
            'name'=>$db_user['userName'],
            'lastChecked'=>$db_user['lastChecked'],
            'checkTime'=>$interval
        );
        // Send back the next user and done
        http_response_code(200);
        die(json_encode($json));
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // okay load basic stuff and grab the oldest checked user
        if (!isset($_POST['userID']) || !isset($_POST['balance']) || !isset($_POST['stakeAmount']) || !isset($_POST['verfied']) || !isset($_POST['mintersTotal']) || !isset($_POST['hashrateTotal']) || !isset($_POST['checkedTime'])) {
            http_response_code(400);
            die(json_encode(array("message"=>"Did not submit all data")));
        }
        require('../db.php');
        $userID = $mysql->real_escape_string($_POST['userID']);
        $balance = $mysql->real_escape_string($_POST['balance']);
        $stakeAmount = $mysql->real_escape_string($_POST['stakeAmount']);
        $verfied = $_POST['verfied'];
        $minersTotal = $_POST['mintersTotal'];
        $hashrateTotal = $_POST['hashrateTotal'];
        $checkedTime = $_POST['checkedTime'];
        require('../db.php');
        $sql_query = 'UPDATE users SET lastChecked = ' . $checkedTime . ' WHERE userID = ' . $userID;
        $result = $mysql->query($sql_query);
        if (!$result) {
            http_response_code(500);
            die(json_encode(array("message"=>"Unable to update the database.")));
        }
        $sql_query = 'INSERT INTO `overview` (`userID`, `balance`, `stakeAmount`, `verfied`, `minersTotal`, `hashrateTotal`, `checkedTime`) VALUES (' . $userID . ', ' . (float)$balance .', ' . (float)$stakeAmount .', ' . $verfied .', ' . $minersTotal .', ' . (float)$hashrateTotal .', ' . $checkedTime .')';
        if (!$mysql->query($sql_query)) {
            http_response_code(500);
            die(json_encode(array("message"=>$mysql->error)));
        }
        $sql_query = 'SELECT lastChecked, userID, userName FROM users ORDER BY lastChecked LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // database problem
            http_response_code(500);
            die(json_encode(array("message"=>"Database Error")));
        }
        $db_user = $result->fetch_assoc();
        $json = array(
            'id'=>$db_user['userID'],
            'name'=>$db_user['userName'],
            'lastChecked'=>$db_user['lastChecked'],
            'checkTime'=>$interval
        );
        // Send back the next user and done
        http_response_code(200);
        die(json_encode($json));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>