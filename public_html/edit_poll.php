<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 5:55 PM
 */

require_once ("../includes/initialize.php");

if (!$session->is_logged_in()) { redirect_to("login.php"); }

if (empty($_GET['poll_id'])){
    $session->message("Error: Could not find Poll");
    redirect_to("./");
}

$user = User::find_by_id($session->user_id);
$poll = Poll::find_by_id($_GET['poll_id']);

if (!$poll){
    $session->message("Error: Could not find Poll");
    redirect_to("./");
}

//First confirm if it's the user performing the edit
if ($user->id != $poll->user_id){
    $session->message("Error: Authority not granted to edit this poll");
    redirect_to("./");
}

//Then confirm if the poll already has a vote
if ($poll->total_votes > 0){
    $session->message("Error: Cannot edit poll with votes");
    redirect_to("view_poll.php?poll_id={$poll->id}");
}

$original_option_count = (int) $poll->total_options;
$title=$poll->title;
$description=$poll->description;
$images_present=$poll->images_present;
$option_count= $original_option_count;
$end_date = strftime("%Y-%m-%d", strtotime($poll->getEndDate()));

$poll_options = $poll->get_poll_options();
$options_name = array();//name of the options
foreach ($poll_options as $poll_option){
    $options_name[] = $poll_option->name;
}


if (isset($_POST['options_submit'])){

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $images_present = isset($_POST['images_present']);
    $end_date = trim($_POST['end_date']);
    $option_count = (int) $_POST['options_submit'];

    //Validate presences
    validate_presences(["title"=> $title, "end date"=>$end_date]);

    //Validate date input
    if (empty($errors["end date"])) {
        $temp_date = explode('-', $end_date);

        if (strtotime($end_date) && strlen($end_date) <= 10
            && count($temp_date) == 3 && checkdate($temp_date[1], $temp_date[2], $temp_date[0])){
            $end_date = strftime("%Y-%m-%d", strtotime(trim($_POST['end_date'])));
        } else {
            $errors["end date"] = "Date input not valid";
            $end_date = strftime("%Y-%m-%d", strtotime('next month'));
        }
    }



    //Populate options
    foreach (range(1, $option_count) as $option_index){
        if (isset($_POST['option_'.$option_index])){
            $options_name[$option_index - 1] = trim($_POST['option_'.$option_index]);
        } else {
            $options_name[$option_index - 1] = "";
        }
    }

}

if (isset($_POST['submit_all'])){

    //Edit Poll Object
    $poll->user_id = $user->id;
    $poll->title = $title;
    $poll->setEndDate($end_date);
    $poll->total_options = $option_count;
    $poll->description = $description;
    $poll->images_present = $images_present;

    //Validate time remaining on poll to avoid creating ended polls
    if (empty($errors['end date'])){
        if (!$poll->get_time_remaining()) {
            $errors['end date'] = "Poll should end at a future time";
        }
    }

    if ($option_count === 0){
        $errors['options'] = "Options should be at least 2";
    }

    foreach (range(1, $option_count) as $option_index){
        if (isset($_POST['option_'.$option_index])){
            $opt_it = trim($_POST['option_'.$option_index]);
            validate_presences(['option_'.$option_index=>$opt_it]);
        }
    }

    if (empty($errors)){
        $database->begin_transaction();
        $is_success = true;

        if ($option_count > $original_option_count) {
            foreach (range(1, $original_option_count) as $option_index) {
                $poll_option = $poll_options[$option_index - 1];
                $poll_option->name = trim($_POST['option_' . $option_index]);
                $poll_option->save();
            }

            //create new options if option count is now higher than original
            foreach (range($original_option_count+1, $option_count) as $op){
                $is_success = PollOption::make($poll->id, trim($_POST['option_' . $op]))->save() && $is_success;
            }

        } else if ($option_count < $original_option_count){
            foreach (range(1, $option_count) as $option_index) {
                $poll_option = $poll_options[$option_index - 1];
                $poll_option->name = trim($_POST['option_' . $option_index]);
                $poll_option->save();
            }

            //delete extra options if option count is now less than original
            foreach (range($option_count+1, $original_option_count) as $op){
                $is_success = $poll_options[$op - 1]->delete() && $is_success;
            }

        } else {//equal
            //just edit the values
            foreach (range(1, $option_count) as $option_index) {
                $poll_option = $poll_options[$option_index - 1];
                $poll_option->name = trim($_POST['option_' . $option_index]);
                $poll_option->save();
            }
        }


        $poll->save();

        if ($is_success){
            $database->commit();
            $session->message("Poll edited ".$poll->title);
            redirect_to("view_poll.php?poll_id={$poll->id}");
        } else {
            $database->rollback();
            $message = "Error: Failed to make changes. Please try again";
        }
    }


}

?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">

    <?php show_message($message); ?>

    <h2 class="my-3">Edit Poll: <?php echo $poll->title; ?></h2>


    <form action="edit_poll.php?poll_id=<?php echo $poll->id; ?>" method="post" name="editPoll">

        <div class="row">


            <div class="col-md-6">

                <div class="my-3">
                    <label for="title">Title</label><br/>
                    <input type="text" class="form-control <?php if (isset($errors['title'])) echo "is-invalid"; ?>"
                           name="title" id="title" value="<?php echo htmlentities($title); ?>" placeholder="title" required/>
                    <div class="invalid-feedback">
                        <?php echo $errors['title']; ?>
                    </div>
                </div>

                <div class="my-3">
                    <label>Description</label><br/>
                    <textarea type="text" class="form-control <?php if (isset($errors['description'])) echo "is-invalid"; ?>"
                              name="description"  rows="5" placeholder="description"><?php echo htmlentities($description); ?></textarea>
                    <div class="invalid-feedback">
                        <?php echo $errors['description']; ?>
                    </div>
                </div>

                <div class="my-3">
                    <label>End Date</label><br/>
                    <input type="date" class="form-control <?php if (isset($errors['end date'])) echo "is-invalid"; ?>"
                           name="end_date" value="<?php echo htmlentities($end_date); ?>" placeholder="YYYY-mm-dd" required/>
                    <div class="invalid-feedback">
                        <?php echo $errors['end date']; ?>
                    </div>
                </div>


                <label>Include Poll Images? </label>
                <input type="checkbox" class="m-2" name="images_present" value="Yes" <?php echo (($images_present)? 'checked' : '');  ?>/>


                <div class="my-3">
                    <label>Number of Poll Options</label>
                    <select class="form-control my-2 <?php if (isset($errors['options'])) echo "is-invalid"; ?>"
                            name="options_submit" onchange="editPoll.submit()">
                        <option value='0' <?php if ($option_count===0) echo 'selected';?> >0</option>

                        <?php foreach (range(2, 20) as $i){
                            if ($option_count === $i)
                                echo "<option value='{$i}' selected>{$i}</option>";
                            else
                                echo "<option value='{$i}' >{$i}</option>";
                        } ?>
                    </select>
                    <div class="invalid-feedback">
                        Please enter number of options.
                    </div>
                </div>

                <br/>

                <button type="submit" class="btn btn-primary mb-3" name="submit_all">Edit Poll</button>

            </div>

            <div class="col-md-6">


                <?php //Options for Poll would be loaded here ?>

                <?php if ($option_count >= 2){ ?>
                <?php
                foreach (range(1, $option_count) as $index){
                    echo "<div class='my-3'>";
                    echo "<label>Option {$index}</label><br/>";

                    echo "<input type='text' class='form-control ". ((isset($errors['option_'.$index]))? 'is-invalid' : '' ) .
                        "' name='option_{$index}' value=\"" . htmlentities($options_name[$index-1]) . "\" 
                                placeholder='enter option' required/>";

                    echo "<div class=\"invalid-feedback\">";
                    echo $errors['option_'.$index];
                    echo "</div>";
                    echo "</div>";


                }
                ?>

            </div>

        </div>







    </form>
    <?php } ?>

</div>


<?php include_layout_template("footer.php")  ?>

