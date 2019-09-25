<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 07/08/2018
 * Time: 8:32 PM
 */

require_once ("../../includes/initialize.php");

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
    $response["error"] = true;
    $response['message'] = "Error: Could not find Poll details";
    echo json_encode($response, JSON_PRETTY_PRINT);
    return;
}

//Find poll and validate it
$poll = Poll::find_by_id($_GET['poll_id']);
if (!$poll){
    $response["error"] = true;
    $response['message'] = "Error: Could not find Poll details";
    echo json_encode($response, JSON_PRETTY_PRINT);
    return;
}

$owner = User::find_by_id($poll->user_id);
$options = $poll->get_poll_options();

if (!$options){
    $response["error"] = true;
    $response['message'] = "Error: Invalid Poll, no options";
    echo json_encode($response, JSON_PRETTY_PRINT);
    return;
}

//Vote Poll

if ($user && isset($_POST['option_id'])){

    foreach ($options as $option) {

        $option_id = trim($_POST['option_id']);

        if ($option_id == $option->id) {
            if (!$poll->is_active()) {
                $response["error"] = true;
                $response['message'] = "Error: Poll no longer active";
                echo json_encode($response, JSON_PRETTY_PRINT);

            } else if (!$user->has_voted_in_poll($poll)) {//confirm user hasn't voted in poll
                $poll->vote_option_with_user($user, $option);
                $response["error"] = false;
                $response['message'] = "Voted!";

                $poll->generate_poll_response($response, $user);

                echo json_encode($response, JSON_PRETTY_PRINT);

            } else {
                $response["error"] = true;
                $response['message'] = "Error: You have already voted in this poll";
                echo json_encode($response, JSON_PRETTY_PRINT);
            }

            return;
        }
    }

    return;
}


//Rescind Vote
if (isset($_POST['remove_vote'])){
    if ($poll->is_active()){

        if ($poll->remove_user_vote($user)){
            $response["error"] = false;
            $response['message'] = "Vote rescinded successfully";

            $poll->generate_poll_response($response, $user);

        } else {
            $response["error"] = true;
            $response['message'] = "Error: Something went wrong, please try removing vote again";
        }

    } else {
        $response["error"] = true;
        $response['message'] = "Poll no longer active";
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

    return;
}

//Close Poll
if (isset($_POST['close_poll'])){
    if ($poll->is_active() && $user->id === $owner->id){
        $poll->is_open = false;

        if ($poll->save()){
            $response["error"] = false;
            $response['message'] = "Poll has been closed";

            $poll->generate_poll_response($response, $user);

        } else {
            $response["error"] = true;
            $response['message'] = "Error: Something went wrong trying to close the poll";
        }

    } else {
        $response["error"] = true;
        $response['message'] = "Error: You cannot close this poll";
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

    return;
}

if (isset($database)) {$database->close_connection();}
