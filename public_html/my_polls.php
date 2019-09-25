<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 1:14 PM
 */

require_once ('../includes/initialize.php');

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

$polls = $user->get_all_polls();

?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">
    <?php show_message($message) ?>

    <h2 class="my-3">Your Polls</h2>

    <?php

    if (empty($polls)){

        echo "<div class='card justify-content-center'>";
            echo "<div class='card-body text-primary text-center'>";

                echo "<i class='fa fa-10x fa-chart-bar'></i>";
                echo "<p class='display-4'>No polls created yet</p>";

            echo "</div>";
            echo "<p class='text-center'><a href='create_poll.php'>Click here to create one 😉</a></p>";
        echo "</div>";

    } else {

        echo "<div class='card-columns'>";//row

            foreach ($polls as $poll) {

                //echo "<div class=\"col-lg-4 col-md-6 col-sm-12\">";

                echo "<div class='card'>";
                echo "<div class='card-header bg-primary text-white text-center text-uppercase'><strong>{$poll->title}</strong></div>";
                echo "<div class='card-body bg-light'>";
                echo "<p>{$poll->description}</p>";
                $options = $poll->get_poll_options();
                foreach ($options as $option) {
                    echo "<div class='progress' style='height: 25px'>";
                    echo "<div class='progress-bar bg-warning text-dark ". ($poll->is_active()? "progress-bar-striped progress-bar-animated" : "") . "' 
                                                style=\"width:" . ($poll->can_user_view_votes($user) ? $option->get_percentage_in_poll() : 0) . "%; 
                                                height:25px;\">&nbsp;{$option->name} " . ($poll->can_user_view_votes($user) ? "(" . $option->get_percentage_in_poll() . "%)" : "") . "</div>";
                    echo "</div><br/>";
                }
                echo "<br/>";
                echo "<text>Total Votes: {$poll->total_votes} " . ($poll->is_active() ? "" : "(Closed)") . "</text> <br/>";
                echo "<b><small>Created on " . datetime_to_text($poll->getCreationDate()) . "</small></b><br/>";
                echo "</div>";
                echo "<a href='view_poll.php?poll_id={$poll->id}' style='text-decoration: none'>
                                    <div class='card-footer bg-success text-white text-center'>" . ($poll->can_user_view_votes($user) ? "View Results" : "Vote Now") . "</div>
                                  </a>";
                echo "</div><br/>";

                //echo "<br/><br/></div>";

            }

        echo "</div>";
    }

    ?>



</div>




<?php include_layout_template('footer.php'); ?>