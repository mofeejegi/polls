<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 01/07/2018
 * Time: 1:52 PM
 */

class Poll extends DatabaseObject {

    protected static $table_name = "polls";
    protected static $db_fields = array('id', 'user_id', 'title', 'description',
        'is_open', 'date_created', 'expiry_date', 'total_votes', 'images_present', 'total_options');

    public $id;
    public $user_id;
    public $title;
    public $description;
    public $is_open;
    /**
     * @var string
     */
    protected $date_created;
    /**
     * @var string
     */
    protected $expiry_date;
    public $total_votes;
    public $images_present;
    public $total_options;

    public static function make($user_id, $title, $expiry_date, $total_options,
                                $description="", $images_present=false, $should_validate=true){
        global $errors;

        //firstly validate the presence of the values
        if ($should_validate) {
            validate_presences([self::$db_fields[2] => $title, self::$db_fields[6] => $expiry_date]);
        }


        if (empty($errors)) {
            $poll = new Poll();
            $poll->user_id = $user_id;
            $poll->title = $title;
            $poll->description = $description;
            $poll->is_open = true;
            $poll->setCreationDate();
            $poll->setEndDate($expiry_date);
            $poll->total_votes = 0;
            $poll->images_present = $images_present;
            $poll->total_options = $total_options;
            return $poll;
        } else {
            return false;
        }
    }


    public function setCreationDate(){
        $date = 'now'; //Server's date now (already in UTC)

        //CONVERT SERVER'S DATE TO UTC (already in UTC)
        $this->date_created = (new DateTime($date, new DateTimeZone('UTC')))->format("Y-m-d H:i:s");
    }

    public function setEndDate($end_date){
        global $tz_offset;
        //CONVERT USER'S DATE TO UTC
        $this->expiry_date = convert_timezone_with_offset($end_date, $tz_offset,
            0)->format("Y-m-d H:i:s");
    }

    public function getCreationDate(){
        global $tz_offset;
        $date = convert_timezone_with_offset($this->date_created, 0, $tz_offset);
        return $date->format("Y-m-d H:i:s");
    }

    public function getEndDate(){
        global $tz_offset;
        $date = convert_timezone_with_offset($this->expiry_date, 0, $tz_offset);
        return $date->format("Y-m-d H:i:s");
    }

    /**
     * @return string|bool
     */
    public function get_time_remaining(){
        $date1 = new DateTime('now', new DateTimeZone('UTC'));
        //echo $date1->format("d/m/Y, H:i:s") ."<br/>";
        $date2 = new DateTime($this->expiry_date, new DateTimeZone('UTC'));
        //echo $date2->format("d/m/Y, H:i:s"). "<br/>";
        $interval = $date1->diff($date2);


        if ($date2>$date1)
            // shows the total amount of days, hours and minutes left
            return $interval->days . " days " . $interval->h . " hour(s) " . $interval->i . " minute(s)";
        else
            return false;

    }

    public function is_active(){
        return $this->get_time_remaining() && $this->is_open;
    }

    /**
     * @return PollOption[]|bool
     */
    public function get_poll_options(){
        global $database;

        $sql = "SELECT * FROM poll_options ";
        $sql.= "WHERE poll_id = {$database->escape_value($this->id)} ";
        $sql.= "LIMIT {$database->escape_value($this->total_options)}";

        $options = PollOption::find_by_sql($sql);

        return (!empty($options)) ? $options : false;
    }


    /**
     * @param $user User
     * @param $option PollOption
     * @return bool
     */
    public function vote_option_with_user($user, $option){
        global $database;
        global $session;

        //BEGIN TRANSACTION (Turn off auto commit)
        $database->begin_transaction();

        $option->votes = $option->votes+1;

        $this->total_votes = $this->total_votes+1;

        if ($option->save() && $this->save() && $user->register_vote($option->id)){
            $database->commit();
            return true;
        } else {
            $database->rollback();
            $session = "Error: Something went wrong, please try voting again";
            return false;
        }
    }



    /**
     * @param $user User
     * @return bool
     */
    public function remove_user_vote($user){
        global $database;

        if (!$user) {return false;}

        foreach ($this->get_poll_options() as $option){

            if ($user->did_vote_for($option)){
                //BEGIN TRANSACTION (Turn off auto commit)
                $database->begin_transaction();
                //reduce the option votes
                $option->votes = $option->votes-1;
                //reduce the polls total votes
                $this->total_votes = $this->total_votes-1;
                //save to db
                if ($option->save() && $this->save() && $user->unregister_vote($option->id)){
                    $database->commit();
                    return true;
                } else {
                    $database->rollback();
                    return false;
                }
            }

        }

        return false;
    }

    /**
     * Show votes if poll is inactive and user has voted in it already
     * @param $user User
     * @return bool
     */
    public function can_user_view_votes($user){
        return (!$this->is_active() || ($user && $user->has_voted_in_poll($this)));


    }


    /**
     * @param string $search_term
     * @param string $sort
     * @param bool $is_desc
     * @return array|Poll[]
     */
    public static function search_polls($search_term="", $sort="date_created", $is_desc=true){
        global $database;

        $direction = $is_desc? "DESC" : "ASC";
        $search_term = $database->escape_value($search_term);
        $sort = $database->escape_value($sort);

        $sql = "SELECT * FROM polls ";
        $sql.= empty($search_term) ? "" : "WHERE (title LIKE '%{$search_term}%')";
        $sql.= empty($sort) ? "" : "ORDER BY {$sort} {$direction} ";

        $polls = Poll::find_by_sql($sql);

        return empty($polls)? array() : $polls;

    }

    /**
     * @param $response
     * @param $user User
     */
    public function generate_poll_response(&$response, $user){
        try {
            $poll_object = array();
            $poll_object['id'] = $this->id;
            $poll_object['owner_id'] = $this->user_id;
            $poll_object['owner'] = User::find_by_id($this->user_id)->username;
            $poll_object['title'] = $this->title;
            $poll_object['description'] = $this->description;
            $poll_object['is_open'] = (bool) $this->is_open;
            $poll_object['date_created'] = $this->getCreationDate();
            $poll_object['expiry_date'] = $this->getEndDate();
            $poll_object['total_votes'] = $this->total_votes;
            $poll_object['total_options'] = $this->total_options;
            $poll_object['images_present'] = (bool) $this->images_present;
            $poll_object['poll_options'] = array();

            if (!empty($this->get_poll_options())){

                foreach ($this->get_poll_options() as $poll_option){

                    $option_object = array();
                    $option_object['id'] = $poll_option->id;
                    $option_object['name'] = $poll_option->name;
                    $option_object['votes'] = (int) $poll_option->votes;
                    $option_object['votes_percent'] = $poll_option->get_percentage_in_poll();
                    $option_object['imagefilename'] = $poll_option->imagefilename;
                    $option_object['user_vote'] = ($user->did_vote_for($poll_option));

                    // push single poll object into final options object array
                    array_push($poll_object['poll_options'], $option_object);
                }

            }

        } catch (Exception $e){

            $response["error"] = true;
            $response['message'] = $e->getMessage();

        } finally {
            // make single poll in response array
            $response["poll"] = $poll_object;
        }
    }









}