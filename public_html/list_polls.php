<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 24/07/2018
 * Time: 9:40 AM
 */

require_once ("../includes/initialize.php");

$user = "";
if ($session->is_logged_in()){$user = User::find_by_id($session->user_id);}

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

$polls = Poll::search_polls($search_term, $sort, $is_desc);

?>
<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">
    <?php show_message($message); ?>

    <br/>
    <div class="row">
        <h2 class="col-sm-12 text-center">Poll Search</h2>
    </div>


    <p class="text-center">Sort by:</p>
    <form action="list_polls.php" method="get" class="form-inline justify-content-center">

        <?php if ($search_term) echo "<input type=\"hidden\" name=\"q\" value=\"$search_term\" />"; ?>

        <select name="sort" class="custom-select m-1">
            <option value='date_created' <?php if (empty($sort) || $sort==="date_created") echo 'selected'?>>Date Created</option>
            <option value='total_votes' <?php if ($sort==="total_votes") echo 'selected'?>>Total Votes</option>
            <option value='title' <?php if ($sort==="title") echo 'selected'?>>Title</option>
        </select>

        <select name="order" class="custom-select m-1">
            <option value='desc' <?php if ($is_desc) echo 'selected'?>>Descending</option>
            <option value='asc' <?php if (!$is_desc) echo 'selected'?>>Ascending</option>
        </select>

        <button type="submit" class="btn btn-primary m-1">Sort</button>

    </form>

    <?php

    if (!empty($search_term))
        echo "<p>Found ". count($polls) . " polls matching \"{$search_term}\"</p>";

    ?>

    <div class="row">

            <?php

            foreach ($polls as $poll){

                echo "<div class=\"col-lg-4 col-md-6 col-sm-12\">";

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

                echo "<br/><br/></div>";

            }

            ?>

    </div>

</div>

<?php include_layout_template("footer.php")  ?>
