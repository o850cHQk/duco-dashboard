<?php
    /*
        Login API:
    */
    header('Content-Type: application/json');
    require('../config.php');
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // check if user variable is set
        if (!isset($_GET['user'])) {
            http_response_code(400);
            die(json_encode(array("message"=>"Please specify a wallet account")));
        }
        // check if server is in single mode
        if ($user_mode == 'single' && $_GET['user'] != $single_user) {
            http_response_code(400);
            die(json_encode(array("message"=>"This server is set to single user mode, you can not log in with other duino coin wallets")));
        }
        require('../db.php');
        // check if account exists
        $userName = $mysql->real_escape_string($_GET['user']);
        $sql_query = 'SELECT * FROM users WHERE userName = "' . $userName . '" LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
            // sql error
            http_response_code(400);
            die(json_encode(array("message"=>"Unable to get your details, try logging in again.")));
        }
        $rows = $result->num_rows;
        if ($rows == 0) { 
            // user does not exist - lets see if it exists on duino coin network
            $url = 'https://server.duinocoin.com/users/' . $userName;
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
            if (!isset($duinoServer['result'])) {
                http_response_code(400);
                die(json_encode(array("message"=>"This account does not exist")));
            }
            // okay add the user into the database
            $sql_query = 'INSERT INTO `users` (`userName`, `lastChecked`) VALUES ("' . $userName .'", 0)';
            if (!$mysql->query($sql_query)) {
                http_response_code(400);
                die(json_encode(array("message"=>$mysql->error)));
            }
            // okay build reply reply
            $json = array(
                'id'=>$mysql->insert_id,
                'name'=>$userName,
                'message'=>'Logged in successfully!'
            );
            setcookie( "id", $json['id'], strtotime( '+30 days' ), "/" );
            http_response_code(200);
            die(json_encode($json));
        }
        $db_user = $result->fetch_assoc();
        $json = array(
            'id'=>$db_user['userID'],
            'name'=>$db_user['userName'],
            'message'=>'Logged in successfully!'
        );
        setcookie( "id", $json['id'], strtotime( '+30 days' ), "/" );
        http_response_code(200);
        die(json_encode($json));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
