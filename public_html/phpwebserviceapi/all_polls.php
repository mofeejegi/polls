<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 30/07/2018
 * Time: 9:12 AM
 */

header('Content-type: application/json');
require_once ("../../includes/initialize.php");

$response = array("error"=>false);

$user = "";
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

$search_term = "";
$sort="date_created";
$is_desc=true;

if (!empty($_GET['q'])){
    $search_term = trim($_GET['q']);
}

if (!empty($_GET['sort'])){

    $sort = trim($_GET['sort']);

    if ($sort != "date_created" && $sort != "total_votes" && $sort != "title"){
        $sort = "date_created";
    }
}

if (!empty($_GET['order'])){
    if (htmlentities(trim($_GET['order'])) == "desc"){
        $is_desc = true;
    } else {
        $is_desc = false;
    }
}

$all_polls = Poll::search_polls($search_term, $sort, $is_desc);


if (!empty($all_polls)){

    $response['polls'] = array();

    foreach ($all_polls as $poll){

        try {
            $poll_object = array();
            $poll_object['id'] = $poll->id;
            $poll_object['owner_id'] = $poll->user_id;
            $poll_object['owner'] = User::find_by_id($poll->user_id)->username;
            $poll_object['title'] = $poll->title;
            $poll_object['description'] = $poll->description;
            $poll_object['is_open'] = (bool) $poll->is_open;
            $poll_object['date_created'] = $poll->getCreationDate();
            $poll_object['expiry_date'] = $poll->getEndDate();
            $poll_object['total_votes'] = $poll->total_votes;
            $poll_object['total_options'] = $poll->total_options;
            $poll_object['images_present'] = (bool) $poll->images_present;
            $poll_object['poll_options'] = array();

            if (!empty($poll->get_poll_options())){

                foreach ($poll->get_poll_options() as $poll_option){

                    $option_object = array();
                    $option_object['id'] = $poll_option->id;
                    $option_object['name'] = $poll_option->name;
                    $option_object['votes'] = (int) $poll_option->votes;
                    $option_object['votes_percent'] = $poll_option->get_percentage_in_poll();
                    $option_object['imagefilename'] = $poll_option->imagefilename;
                    $option_object['user_vote'] = (($user)? $user->did_vote_for($poll_option) : false );

                    // push single poll into final response array
                    array_push($poll_object['poll_options'], $option_object);
                }

            }

        } catch (Exception $e){

            echo $e;

        } finally {
            // push single poll into final response array
            array_push($response["polls"], $poll_object);
        }


    }

    if (empty($response['polls'])){
        $response['error'] = true;
        $response['message'] = "No valid poll found";

    } else {
        $response['error'] = false;
        $response['message'] = "";
    }

    echo json_encode($response, JSON_PRETTY_PRINT);

} else {

    $response['error'] = true;
    $response['message'] = "No polls found";

    echo json_encode($response, JSON_PRETTY_PRINT);
}

if (isset($database)) {$database->close_connection();}