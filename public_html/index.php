<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 29/06/2018
 * Time: 4:05 PM
 */

require_once ("../includes/initialize.php");

$user = "";
if ($session->is_logged_in()){$user = User::find_by_id($session->user_id);}

$sql = "SELECT * FROM polls ";
$sql.= "ORDER BY total_votes DESC ";
$sql.= "LIMIT 3";
$top_polls = Poll::find_by_sql($sql);


$sql = "SELECT * FROM polls ";
$sql.= "ORDER BY total_votes DESC ";
$sql.= "LIMIT 3 OFFSET 3";
$next_top_polls = Poll::find_by_sql($sql);

?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>


<div id="header" class="container-fluid">
    <?php show_message($message); ?>
    <div class="jumbotron jumbotron-fluid justify-content-center">
        <div class="container">
            <h1 class="display-2 text-center">Welcome to Polls</h1>
            <p class="text-center">Ask for opinions, and see what other have to say about it.</p>
            <div class="text-center">
                <a href="<?php echo (($user)? 'create_poll.php' : 'login.php')?>" class="btn btn-outline-primary text-center">Create your Poll now</a>
            </div>
        </div>
    </div>

</div>


<div class="container">

    <div class="row">
        <h2 class="col-sm-12 text-center mb-4">Top Polls</h2>
    </div>

    <!--Show the top 3 polls-->
    <div class="card-deck">
    <?php


    foreach ($top_polls as $poll){

        echo "<div class='card shadow'>";
            echo "<div class='card-header bg-primary text-white text-center text-uppercase'><strong>{$poll->title}</strong></div>";
            echo "<div class='card-body bg-light'>";
                echo "<p>{$poll->description}</p>";
                $options = $poll->get_poll_options();
                foreach ($options as $option) {
                    echo "<div class='progress' style='height: 25px'>";
                        echo "<div class='progress-bar bg-warning text-dark ". ($poll->is_active()? "progress-bar-striped progress-bar-animated" : "") . "' 
                        style=\"width:". ($poll->can_user_view_votes($user)? $option->get_percentage_in_poll() : 0) ."%; 
                        height:25px;\">&nbsp;{$option->name} ". ($poll->can_user_view_votes($user)? "(".$option->get_percentage_in_poll()."%)" : "" ) . "</div>";
                    echo "</div><br/>";
                }
                echo "<br/>";
                echo "<text>Total Votes: {$poll->total_votes} ". ($poll->is_active()? "" : "(Closed)") ."</text> <br/>";
                echo "<b><small>by " . User::find_by_id($poll->user_id)->username . " on " . datetime_to_text($poll->getCreationDate()) . "</small></b><br/>";
            echo "</div>";
            echo "<a href='view_poll.php?poll_id={$poll->id}' style='text-decoration: none'>
                    <div class='card-footer bg-success text-white text-center'>". ($poll->can_user_view_votes($user)? "View Results":"Vote Now") ."</div>
                  </a>";
        echo "</div>";
    }
    ?>
    </div>

    <br/><br/>

    <!--Show the 4th to 6th top polls-->
    <div class="card-deck">
    <?php

    foreach ($next_top_polls as $poll){

        echo "<div class='card shadow'>";
            echo "<div class='card-header bg-primary text-white text-center text-uppercase'><strong>{$poll->title}</strong></div>";
            echo "<div class='card-body bg-light'>";
                echo "<p>{$poll->description}</p>";
                $options = $poll->get_poll_options();
                foreach ($options as $option) {
                    echo "<div class='progress' style='height: 25px'>";
                        echo "<div class='progress-bar bg-warning text-dark ". ($poll->is_active()? "progress-bar-striped progress-bar-animated" : "") . "' 
                        style=\"width:". ($poll->can_user_view_votes($user)? $option->get_percentage_in_poll() : 0) ."%; 
                        height:25px;\">&nbsp;{$option->name} ". ($poll->can_user_view_votes($user)? "(".$option->get_percentage_in_poll()."%)" : "" ) . "</div>";
                    echo "</div><br/>";
                }
                echo "<br/>";
                echo "<text>Total Votes: {$poll->total_votes} ". ($poll->is_active()? "" : "(Closed)") ."</text> <br/>";
                echo "<b><small>by " . User::find_by_id($poll->user_id)->username . " on " . datetime_to_text($poll->getCreationDate()) . "</small></b><br/>";
            echo "</div>";
            echo "<a href='view_poll.php?poll_id={$poll->id}' style='text-decoration: none'>
                    <div class='card-footer bg-success text-white text-center'>". ($poll->can_user_view_votes($user)? "View Results":"Vote Now") ."</div>
                  </a>";
        echo "</div>";
    }

    ?>
    </div>
    <br/><br/>

    <div class="text-center">
        <a href="list_polls.php?sort=total_votes&order=desc" class="btn btn-primary text-center">View more</a>
    </div>

</div>


<?php include_layout_template("footer.php")  ?>
