<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 01/07/2018
 * Time: 2:39 PM
 */

class PollOption extends DatabaseObject {

    protected static $table_name = "poll_options";
    protected static $db_fields = array('id', 'poll_id', 'name', 'votes', 'imagefilename');

    public $id;
    public $poll_id;
    public $name;
    public $votes;
    public $imagefilename;


    public static function make($poll_id, $name, $imagefilename=""){
        global $errors;


        if (empty($errors)) {
            $poll_option = new PollOption();
            $poll_option->poll_id = $poll_id;
            $poll_option->name = $name;
            $poll_option->votes = 0;
            $poll_option->imagefilename = $imagefilename;
            return $poll_option;
        } else {
            return false;
        }

    }

    public function delete_associated_votes(){
        global $database;

        $sql = "DELETE FROM votes WHERE option_id={$database->escape_value($this->id)}";

        if ($database->query($sql)){
            return true;
        } else {
            return false;
        }
    }

    public function get_percentage_in_poll(){

        $poll = Poll::find_by_id($this->poll_id);

        if ($poll->total_votes == 0) return 100;

        $percent = round( (((double)$this->votes)/((double)$poll->total_votes)) * 100 );

        return $percent;
    }

}