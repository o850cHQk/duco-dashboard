<?php
    /*
        Login API:
    */
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
        session_start();
        require('../db.php');
        // check if account exists
        $userName = $mysql->real_escape_string($_GET['user']);
        $sql_query = 'SELECT * FROM users WHERE username = "' . $userName . '" LIMIT 1';
        $result = $mysql->query($sql_query);
        if (!$result) {
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
            $duinoServer = json_decode($output);
            if ($duinoServer['success'] == false) {
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
            $_SESSION['id'] = $json['id'];
            $_SESSION['name'] = $json['name'];
            http_response_code(200);
            die(json_encode($json));
        }
        $db_user = $result->fetch_assoc();
        $json = array(
            'id'=>$db_user['userID'],
            'name'=>$db_user['userName'],
            'message'=>'Logged in successfully!'
        );
        $_SESSION['id'] = $json['id'];
        $_SESSION['name'] = $json['name'];
        http_response_code(200);
        die(json_encode($json));
    }
    http_response_code(404);
    echo(json_encode(array("message"=>"Does not exist")));
?>
