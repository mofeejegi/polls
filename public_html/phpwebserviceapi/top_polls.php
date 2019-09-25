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
if (isset($_POST['user_id'])){
    $user = User::find_by_id(trim($_POST['user_id']));
}

$sql = "SELECT * FROM polls ";
$sql.= "ORDER BY total_votes DESC ";
$sql.= "LIMIT 6";
$top_polls = Poll::find_by_sql($sql);


if (!empty($top_polls)){

    $response['polls'] = array();

    foreach ($top_polls as $poll){

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

                    $options = array();
                    $options['id'] = $poll_option->id;
                    $options['name'] = $poll_option->name;
                    $options['votes'] = (int) $poll_option->votes;
                    $options['votes_percent'] = $poll_option->get_percentage_in_poll();
                    $options['imagefilename'] = $poll_option->imagefilename;
                    $options['user_vote'] = (($user)? $user->did_vote_for($poll_option) : false );

                    // push single poll into final response array
                    array_push($poll_object['poll_options'], $options);
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