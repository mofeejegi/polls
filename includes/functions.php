<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 29/06/2018
 * Time: 4:07 PM
 */

/**
 * @param null $location
 */
function redirect_to($location = null){
    if ($location != null) {
        header("Location: {$location}");
        exit;
    }
}

function validate_presences($required_fields=array(), $should_trim=true){
    global $errors;
    foreach ($required_fields as $key=>$field){

        if ($should_trim) {
            if (trim($field) === "" || $field === null) {
                $errors[$key] = $key . " can't be blank";
            }
        } else {
            if ($field === "" || $field === null) {
                $errors[$key] = $key . " can't be blank";
            }
        }
    }
}

function validate_min_lengths($required_fields=array(), $min_length){
    global $errors;
    foreach ($required_fields as $key=>$field){
        if (strlen(trim($field)) < $min_length){
            $errors[$key] = $key . " must have at least {$min_length} characters";
        }
    }
}

function validate_email($required_email, $key="email"){
    global $errors;
    if (strpos(trim($required_email), "@") === false || strpos(trim($required_email), ".") === false){
        $errors[$key] = "Please use a valid email address";
    }
}

function show_message($message){
    if ($message) {

        $is_success = !(substr($message, 0, 6) === "Error:");
        if (!$is_success)
            $message = substr($message, 7, strlen($message));
        echo "<div class='alert alert-dismissible fade show " . ($is_success ? "alert-success" : "alert-danger ") . "'>";
            echo "<button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button>";
            echo "$message";
        echo "</div>";
    }
}


/**
 * @param $datetime
 * @param $old_timezone_string
 * @param $new_timezone_string
 * @return DateTime
 */
function convert_timezone($datetime, $old_timezone_string, $new_timezone_string){
    $old_timezone = new DateTimeZone($old_timezone_string);

    // Instantiate the DateTime object, setting it's date, time and time zone.
    $new_datetime = new DateTime($datetime, $old_timezone);

    // Set the DateTime object's time zone to convert the time appropriately.
    $new_timezone = new DateTimeZone($new_timezone_string);
    $new_datetime->setTimeZone($new_timezone);

    return $new_datetime;
}

/**
 * @param $datetime
 * @param $old_timezone_offset
 * @param $new_timezone_offset
 * @return DateTime
 */
function convert_timezone_with_offset($datetime, $old_timezone_offset, $new_timezone_offset){
    // Instantiate the DateTime object, setting it's date, time
    $new_datetime = new DateTime($datetime);

    $total_offset = $new_timezone_offset - $old_timezone_offset;

    // Set the DateTime object's time zone with the offset to convert the time appropriately.
    $new_datetime->modify("+{$total_offset} minutes");

    return $new_datetime;
}

function __autoload($class_name) {
    $class_name = strtolower($class_name);
    $path = LIB_PATH.DS."{$class_name}.php";

    if (file_exists($path)) {
        require_once ($path);
    } else {
        die("The file {$class_name}.php could not be found");
    }
}

function include_layout_template($template="") {
    require SITE_ROOT.DS.'public_html'.DS.'layouts'.DS.'templates'.DS.$template;
}



function datetime_to_text($datetime=""){
    $unixdatetime = strtotime($datetime);
    return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
}

function generateApiKey() {
    return md5(uniqid(rand(), true));
}