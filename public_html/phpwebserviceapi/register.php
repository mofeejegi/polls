<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 5:46 PM
 */
header('Content-type: application/json');
require_once ('../../includes/initialize.php');


//json response array
$response = array("error" => false);

$username = "";
$email = "";
$password = "";

if (true || isset($_POST['username']) && isset($_POST['email']) && isset($_POST['password'])){

    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    //Make User, validation is done also
    $user = User::make($username, $email, $password, "user");

    if (empty($errors)) {

        //see if any unique fields for this new user have been taken
        $user->check_field_exists(["username" => $username, "email" => $email]);

        if (empty($errors)) {
            //save user to the db
            if ($user->save()){
                $response['error'] = false;
                $response["user"]["id"] = $database->inserted_id();
                $response["user"]["username"] = $user->username;
                $response["user"]["email"] = $user->email;
                $response["user"]["api_key"] = $user->api_key;

                echo json_encode($response, JSON_PRETTY_PRINT);

            } else {
                $response['error'] = true;
                $response['message'] = "Error creating profile";

                echo json_encode($response, JSON_PRETTY_PRINT);
            }

        } else {
            $response['error'] = true;
            //echo the taken field back
            $taken = "";
            if (array_key_exists('username', $errors)) $taken = "username";
            if (array_key_exists('email', $errors)) $taken = "email";
            $response['message'] =  $taken ." already taken";

            echo json_encode($response, JSON_PRETTY_PRINT);
        }

    } else {
        $response['error'] = true;
        $response['message'] = "Please check your fields and try again";
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

} else {
    $response['error'] = true;
    $response['message'] = "Please fill in all fields";

    echo json_encode($response, JSON_PRETTY_PRINT);

}

if (isset($database)) {$database->close_connection();}