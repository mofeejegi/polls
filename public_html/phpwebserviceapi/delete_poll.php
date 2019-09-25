<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 21/07/2018
 * Time: 10:25 AM
 */
require_once ('../../includes/initialize.php');

$user = "";
$owner = "";

//json response array
$response = array("error" => false);

$headers = apache_request_headers();
if(isset($headers['Authorization'])){
    $user = User::find_by_api_key(trim($headers['Authorization']));
}

if (!$user){
    $response['error'] = true;
    $response['message'] = "Could not authenticate profile, try logging in again";

    echo json_encode($response, JSON_PRETTY_PRINT);
    return;
}


if (empty($_GET['poll_id'])){
    $response['error'] = true;
    $response['message'] = "Could not find poll to delete";

    echo json_encode($response, JSON_PRETTY_PRINT);
    return;
}


$poll = Poll::find_by_id($_GET['poll_id']);

if ($poll){
    //First confirm if it's the owner performing the delete
    if ($user->id != $poll->user_id){
        $response['error'] = true;
        $response['message'] = "Authority not granted to delete this poll";

        echo json_encode($response, JSON_PRETTY_PRINT);
        return;
    }

    $database->begin_transaction();
    $is_success = true;

    foreach ($poll->get_poll_options() as $option){
        $is_success = $option->delete_associated_votes() && $is_success;
        $is_success = $option->delete() && $is_success;
    }

    $is_success = $poll->delete() && $is_success;

    if ($is_success) {
        $database->commit();
        $response['error'] = false;
        $response['message'] = "Successfully deleted Poll: {$poll->title}";

    } else {
        $database->rollback();
        $response['error'] = false;
        $response['message'] = "Error: Failed to delete Poll: {$poll->title}";
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} else {
    $response['error'] = true;
    $response['message'] = "Could not find poll to delete";

    echo json_encode($response, JSON_PRETTY_PRINT);
}



//remember to remove close the connection, usually done in footer layout
if (isset($database)) {$database->close_connection(); }