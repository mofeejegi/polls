<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 30/06/2018
 * Time: 1:39 PM
 */

class User extends DatabaseObject {

    protected static $table_name = "users";
    protected static $db_fields = array('id', 'username', 'email', 'password', 'role', 'api_key');

    public $id;
    public $username;
    public $email;
    public $password;
    public $role;
    public $api_key;


    /**
     * @param $username
     * @param $email
     * @param $password
     * @param $role
     * @param $should_validate
     * @return bool|User
     */
    public static function make($username, $email, $password, $role, $should_validate=true){
        global $errors;

        //firstly validate the presence of the values
        if ($should_validate){
            validate_presences([self::$db_fields[1]=> $username, self::$db_fields[2]=>$email, self::$db_fields[4]=>$role]);
            validate_presences([self::$db_fields[3]=>$password], false);
        }


        if ($should_validate) {
            if (empty($errors[self::$db_fields[1]])) {
                validate_min_lengths([self::$db_fields[1] => $username], 4);

            }
            if (empty($errors[self::$db_fields[3]])){
                validate_min_lengths([self::$db_fields[3] => $password], 8);
            }
        }

        //then validate the email
        if ($should_validate) {
            if (empty($errors[self::$db_fields[2]])) {
                validate_email($email);
            }
        }


        if (empty($errors)) {
            $user = new User();
            $user->username = $username;
            $user->email = $email;
            $user->password = password_hash($password, PASSWORD_DEFAULT);
            $user->role = $role;
            $user->api_key = generateApiKey();
            return $user;
        } else {
            return false;
        }

    }

    public static function authenticate($username, $password, $should_validate=true){
        global $errors;
        global $database;

        if ($should_validate) {
            validate_presences([self::$db_fields[1] => $username]);
            validate_presences([self::$db_fields[3]=>$password], false);
        }

        if (!empty($errors)){
            return false;
        }

        $username = trim($database->escape_value($username));
        $password = $database->escape_value($password);


        $sql = "SELECT * FROM users ";
        $sql .= "WHERE username = '{$username}' ";
        $sql .= "OR email = '{$username}' ";
        $sql .= "LIMIT 1";

        $result_array = self::find_by_sql($sql);

        $user = "";
        if (count($result_array) > 0){
            $user = $result_array[0];
        }

        if ($user){
            if (password_verify($password, $user->password)){
                return $user;
            } else {
                return false;
            }
        }

        return false;

        //return empty($result_array)? false : $result_array[0];

    }

    /**
     * @param $api_key
     * @return self|bool
     */
    public static function find_by_api_key($api_key){
        global $database;

        $result_array = static::find_by_sql("SELECT * FROM " .
            static::$table_name . " WHERE api_key='". $database->escape_value($api_key) . "' LIMIT 1");

        return !empty($result_array) ? $result_array[0] : false;
    }

    /**
     * @return self[]
     */
    public static function find_all_admins(){
        $sql = "SELECT * FROM users ";
        $sql .= "WHERE role='admin' ";
        $sql .= "OR role='god'";

        return self::find_by_sql($sql);
    }

    public function is_god(){
        return $this->role == "god";
    }

    public function is_admin(){
        return $this->role == "admin";
    }


    public function check_field_exists($fields=array()){
        global $errors;
        $all = User::find_all();

        foreach ($fields as $key=>$field) {

            foreach ($all as $user) {
                if (strtolower($this->$key) === strtolower($user->$key)) {
                    $errors[$key] = $key ." already exists";
                    break;
                }
            }

        }
    }

    /**
     * @param $is_desc
     * @return bool|Poll[]
     */
    public function get_all_polls($is_desc=true){
        global $database;

        $order = $is_desc? "DESC" : "ASC";

        $sql = "SELECT * FROM polls ";
        $sql.= "WHERE user_id={$database->escape_value($this->id)} ";
        $sql.= "ORDER BY date_created {$order}";

        $polls = Poll::find_by_sql($sql);

        return !empty($polls)? $polls : false;
    }

    /**
     * @param $poll Poll
     * @return bool
     */
    public function has_voted_in_poll($poll){
        $options = $poll->get_poll_options();

        foreach ($options as $option){
            if ($this->did_vote_for($option)){
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a user has voted for a poll option before
     * @param $option
     * @return bool
     */
    public function did_vote_for($option){
        global $database;

        $sql = "SELECT * FROM votes ";
        $sql.= "WHERE user_id={$database->escape_value($this->id)} ";
        $sql.= "AND option_id={$database->escape_value($option->id)} ";
        $sql.= "LIMIT 1";

        $result = $database->query($sql);

        $row_arrays = array();

        while ($row = $database->fetch_array($result)) {
            $row_arrays[] = $row;//add the first(only) result array row
        }

        return (!empty($row_arrays));
    }

    public function register_vote($option_id){
        global $database;

        $sql = "INSERT INTO votes (user_id, option_id) ";
        $sql.= "VALUES ({$database->escape_value($this->id)}, {$database->escape_value($option_id)})";
        if ($database->query($sql)){
            return true;
        } else {
            return false;
        }

    }

    public function unregister_vote($option_id){
        global $database;

        $sql = "DELETE FROM votes ";
        $sql.= "WHERE user_id={$database->escape_value($this->id)} ";
        $sql.= "AND option_id={$database->escape_value($option_id)} ";
        $sql.= "LIMIT 1";

        if ($database->query($sql)){
            return true;
        } else {
            return false;
        }

    }
}