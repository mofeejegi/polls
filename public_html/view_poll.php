<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 22/07/2018
 * Time: 11:32 PM
 */

require_once ("../includes/initialize.php");

$user = "";
$owner = "";
$option = array();

if (empty($_GET['poll_id'])){
    $session->message("Error: Could not find Poll details");
    redirect_to("./");
}

if ($session->is_logged_in()){
    $user = User::find_by_id($session->user_id);
}

//Find poll and validate it
$poll = Poll::find_by_id($_GET['poll_id']);
if (!$poll){
    $session->message("Error: Could not find Poll details");
    redirect_to("./");
}

$owner = User::find_by_id($poll->user_id);
$options = $poll->get_poll_options();

if (!$options){
    $session->message("Error: Invalid Poll, no options");
    redirect_to("./");
}

//Vote Poll (This style has been improved upon in the
foreach ($options as $option) {
    if ($user && isset($_POST['vote_'.$option->id])){

        if (!$poll->is_active())
            $message = "Error: Poll no longer active";

        //confirm that user hasn't voted
        else if (!$user->has_voted_in_poll($poll)){
            $poll->vote_option_with_user($user, $option);
            redirect_to("view_poll.php?poll_id={$poll->id}");

        } else {
            $message = "Error: You have already voted in this poll";
        }
    }
}

//Rescind Vote
if (isset($_POST['remove_vote'])){
    if ($user && $poll->is_active()){

        if ($poll->remove_user_vote($user)){
            $session->message("Vote rescinded successfully");
        } else {
            $session->message("Error: Something went wrong, please try removing vote again");
        }
    }
    redirect_to("view_poll.php?poll_id={$poll->id}");
}

//Close Poll
if (isset($_POST['close_poll'])){
    if ($poll->is_active() && $user->id === $owner->id){
        $poll->is_open = false;
        $poll->save();
    } else {
        $session->message("Error: You cannot close this poll");
    }
    redirect_to("view_poll.php?poll_id={$poll->id}");
}



?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">

    <?php show_message($message); ?>

    <span class="form-inline my-3"><h2><?php echo $poll->title; ?>!</h2> <b>&nbsp;(by: <?php echo $owner->username; ?>)</b></span>

    <p class="card bg-light p-1"><?php echo $poll->description; ?></p>


    <?php
    if ($poll->is_active())
        echo  "<p><i>This poll ends in " . $poll->get_time_remaining() . "</i></p>";

    else {
        if (!$poll->get_time_remaining())
            echo "<p class='text-danger' ><i>This poll has ended since "
                . datetime_to_text($poll->getEndDate()) . "</i></p>";

        else if (!$poll->is_open) {
            echo "<p class='text-danger'>This poll has been closed by the owner</p>";
        }
    }

    ?>


    <?php //Some poll actions: Remove vote, Close Poll, Edit Poll, Delete Poll

    if ($user) {

        //If the user has voted already, and time still remains and the poll is still open
        if ($user->has_voted_in_poll($poll) && $poll->is_active()) {
            echo " <form action='view_poll.php?poll_id={$poll->id}' method='post'> ";
            echo "<button type='submit' class='btn btn-primary' name='remove_vote'>Remove Vote</button>";
            echo "</form>";
        }

        if ($user->id === $poll->user_id) {

            if ($poll->is_active()) {
                echo " <form action='view_poll.php?poll_id={$poll->id}' method='post'> ";
                echo "<button type='submit' class='btn btn-primary' name='close_poll'>Close Poll</button>";
                echo "</form>";

                if ((int)$poll->total_votes === 0)
                    echo " <a class='btn btn-warning' href='edit_poll.php?poll_id={$poll->id}'>Edit Poll</a> ";
            }

            echo " <a class='btn btn-danger' href='delete_poll.php?poll_id={$poll->id}'>Delete Poll</a> ";
        }
    }

    ?>

    <hr/>
    <h3>Options</h3>



    <form action="view_poll.php?poll_id=<?php echo $poll->id?>" method="post">

        <div class="">

        <?php
            //Loop through all options and echo their names and a vote button
            if (!empty($options)){
                foreach ($options as $option){

                    echo "<div class='row'>";

                        //Option progress bar
                        echo "<div class='col-md-9 my-1'>";
                            echo "<div class='progress' style='height: 25px;'>";
                                echo "<div class='progress-bar bg-warning text-dark ". ($poll->is_active()? "progress-bar-striped progress-bar-animated" : "") . "' 
                                                    style='width:". ($poll->can_user_view_votes($user)? $option->get_percentage_in_poll() : 0) ."%; 
                                                    height:25px;'>&nbsp;&nbsp;{$option->name} ". ($poll->can_user_view_votes($user)? "(".$option->get_percentage_in_poll()."%)" : "" ) . "</div>";
                            echo "</div>";
                        echo "</div>";

                        //Vote/Login button
                        echo "<div class='col-md-3 my-1'>";
                            if ($user) {
                                //if usr has not voted in this poll and poll is still ongoing
                                if (!$user->has_voted_in_poll($poll) && $poll->is_active())
                                    echo "<button type='submit' class='btn btn-primary p-0' style='height: 25px; width:100px;'  name='vote_" . $option->id . "'>Vote</button>";
                                else {
                                    if ($user->did_vote_for($option))
                                        echo "<div class='p-0' style='height: 25px; float: right'>" . $option->votes . " vote(s) <strong style='color: green'><i class=\"fas fa-check-circle\"></i></strong></div>";
                                    else
                                        echo "<div class='p-0' style='height: 25px; float: right'>". $option->votes . " vote(s) </div>" ;
                                }

                            } else {
                                //show votes if poll is already over, otherwise instruct login
                                if ($poll->is_active()){
                                    echo "<i>Please <a href='login.php'>login</a> to vote</i>";
                                } else
                                    echo $option->votes . " vote(s)";
                            }
                        echo "</div>";

                    echo "</div><br/>";


                }
            }
        ?>


        </div>


    </form>

</div>

<?php include_layout_template("footer.php")  ?>
