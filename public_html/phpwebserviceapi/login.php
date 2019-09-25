<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 1:10 PM
 */
header('Content-type: application/json');
require_once ("../../includes/initialize.php");

//json response array
$response = array("error" => false);

$username = "";
$password = "";

if (true || isset($_POST['username']) && isset($_POST['password'])){

    $username = trim($_POST['username']);//Please note here that username refers to both username and email
    $password = $_POST['password'];

    //check db to see if username/password exist
    $found_user = User::authenticate($username, $password);

    //if no errors in validation
    if (empty($errors)) {

        if ($found_user) {
            $response["error"] = false;
            $response['message'] = "Logged in successfully as ".$found_user->username;
            $response["user"]["id"] = $found_user->id;
            $response["user"]["username"] = $found_user->username;
            $response["user"]["email"] = $found_user->email;
            $response["user"]["api_key"] = $found_user->api_key;
            echo json_encode($response, JSON_PRETTY_PRINT);

        } else {
            $response['error'] = true;
            $response['message'] = "Username/Password incorrect";
            echo json_encode($response, JSON_PRETTY_PRINT);
        }

    } else {
        $response["error"] = true;
        $response['message'] = "Please check your fields and try again";
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

} else {
    $response["error"] = true;
    $response['message'] = "Some fields are missing";
    echo json_encode($response, JSON_PRETTY_PRINT);
}

if (isset($database)) {$database->close_connection();}