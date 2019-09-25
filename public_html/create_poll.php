<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 20/07/2018
 * Time: 1:15 PM
 */

require_once ("../includes/initialize.php");

if (!$session->is_logged_in()) { redirect_to("login.php"); }

$user = User::find_by_id($session->user_id);

$title="";
$description="";
$images_present=false;
$option_count=0;
$end_date = strftime("%Y-%m-%d", strtotime('next month'));

$options_name = array();


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

    //Populate option names
    foreach (range(1, $option_count) as $item){
        if (isset($_POST['option_'.$item])){
            $options_name[$item - 1] = trim($_POST['option_'.$item]);
        } else {
            $options_name[$item - 1] = "";
        }
    }

}

if (isset($_POST['submit_all'])){

    //Create Poll Object
    $poll = Poll::make($user->id, $title, $end_date, $option_count, $description, $images_present, false);

    //Validate time remaining on poll to avoid creating ended polls
    if (empty($errors['end date'])){
        if (!$poll->get_time_remaining()) {
            $errors['end date'] = "Poll should end at a future time";
        }
    }

    if ($option_count === 0){
        $errors['options'] = "Options should be at least 2";
    }


    foreach (range(1, $option_count) as $item){
        if (isset($_POST['option_'.$item])){
            $opt_it = trim($_POST['option_'.$item]);
            validate_presences(['option_'.$item=>$opt_it]);
        }
    }

    if (empty($errors)){
        $database->begin_transaction();
        $is_success = true;

        $is_success = $poll->save() && $is_success;

        $poll_id = $database->inserted_id();

        foreach (range(1, $option_count) as $item){
            $is_success = PollOption::make($poll_id, trim($_POST['option_'.$item]))->save() && $is_success;
        }

        if ($is_success){
            $database->commit();
            $session->message("New poll created ".$poll->title);
            redirect_to("my_polls.php");
        } else {
            $database->rollback();
            $message = "Error: Failed to make changes. Please try again";
        }


    }


}



?>

<?php include SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.'header.php'; ?>

<div class="container">

    <h2 class="my-3 text-left">Create Poll</h2>

    <?php show_message($message); ?>

    <form action="create_poll.php" method="post" name="createPoll">

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
                    <select class="form-control custom-checkbox my-2 <?php if (isset($errors['options'])) echo "is-invalid"; ?>"
                            name="options_submit" onchange="createPoll.submit()">
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

                <button type="submit" class="btn btn-primary mb-3" name="submit_all">Create Poll</button>

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
