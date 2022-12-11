<?php
    /*
        Runner API:
    */
    header('Content-Type: application/json');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // okay load basic stuff and grab the oldest checked user
        require('../config.php');
        require('../db.php');
        $sql_query = 'SELECT lastChecked, userID, userName FROM users ORDER BY lastChecked LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // something went wrong maybe log out and log back in?
            http_response_code(400);
            die(json_encode(array("message"=>"Unable to get user, you might need to login for the first time.")));
        }
        $db_user = $result->fetch_assoc();
        // Now we check if we need to actually grab anything
        if ($db_user['lastChecked'] + $interval > $_SERVER['REQUEST_TIME']) {
            http_response_code(200);
            die(json_encode(array("message"=>"OK")));
        }
        // okay get the time and update the lastChecked use $_SERVER['REQUEST_TIME']
        $sql_query = 'UPDATE users SET lastChecked = ' . $_SERVER['REQUEST_TIME'] . ' WHERE userID = ' . $db_user['userID'];
        $result = $mysql->query($sql_query);
        if (!$result) {
            http_response_code(400);
            die(json_encode(array("message"=>"Unable to update the database.")));
        }
        // now we curl the user and build the query
        $url = 'https://server.duinocoin.com/users/' . $db_user['userName'];
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        $output=curl_exec($ch);
        if($output === false) {
            curl_close($ch);
            http_response_code(400);
            die(json_encode(array("message"=>"We can not connect to the Duino Coin Servers at the moment, please try connecting later")));
        }
        curl_close($ch);
        $duinoServer = json_decode($output, true);
        // okay now to build the data for our database
        $balance = $mysql->real_escape_string($duinoServer['result']['balance']['balance']);
        $stakeAmount = $mysql->real_escape_string($duinoServer['result']['balance']['stake_amount']);
        $verfied = ($duinoServer['result']['balance']['verified'] == 'yes');
        $minersTotal = 0;
        $hashrateTotal = 0;
        $checkedTime = $_SERVER['REQUEST_TIME'];
        // now we need to loop through all the workers and add them into the totals for hashrate and miners
        for ($i = 0; $i < count($duinoServer['result']['miners']); $i++)  {
            $minersTotal = $i + 1;
            $hashrateTotal += (float)$duinoServer['result']['miners'][$i]['hashrate'];
        }
        // okay lets add this to the database

        $sql_query = 'INSERT INTO `overview` (`userID`, `balance`, `stakeAmount`, `verfied`, `minersTotal`, `hashrateTotal`, `checkedTime`) VALUES (' . $db_user['userID'] . ', ' . (float)$balance .', ' . (float)$stakeAmount .', ' . $verfied .', ' . $minersTotal .', ' . (float)$hashrateTotal .', ' . $checkedTime .')';
        if (!$mysql->query($sql_query)) {
            http_response_code(400);
            die(json_encode(array("message"=>$mysql->error)));
        }
        http_response_code(200);
        die(json_encode(array("message"=>"OK")));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>