<?php
/**
 * Created by PhpStorm.
 * User: mofeejegi
 * Date: 30/06/2018
 * Time: 1:40 PM
 */

class DatabaseObject {

    protected static $table_name;
    protected static $db_fields = array();
    protected $id;//doesn't really matter though

    // Common Database Methods
    protected static function instantiate($record){
        // Could check that $record exists and is an array
        // if (is_array($record)){}
        // Simple, long form approach:
        $db_object = new static(); //static not self, so as to reference called class

        // More dynamic, short-form approach:
        foreach ($record as $attribute=>$value){
            if ($db_object->has_attribute($attribute)) {
                $db_object->$attribute = $value; //dynamically creating an attribute $(attribute)
            }
        }

        return $db_object;
    }

    protected function has_attribute($attribute) {
        // returns all attributes of this instance of the class as an
        // associative array (including private attrs)
        $object_vars = get_object_vars($this);

        // if the attribute (key) already exists, return true
        return array_key_exists($attribute, $object_vars);
    }




    protected function attributes() {
        //return an array of attribute keys and their values
        $attributes = array();

        foreach (static::$db_fields as $field) {
            if (property_exists($this, $field)){
                $attributes[$field] = $this->$field; //get value of dynamic var/attribute $(field)
            }
        }
        return $attributes;
    }

    protected function sanitized_attributes() {
        global $database;

        //sanitize the attributes keys and values by escaping the characters
        $clean_attributes = array();

        foreach ($this->attributes() as $key => $value){
            $clean_attributes[$key] = $database->escape_value($value);
        }

        return $clean_attributes;
    }

    /**
     * @return bool
     */
    public function save(){
        // A new record won't have an id from the db yet
        return isset($this->id) ? $this->update() : $this->create();
    }

    /**
     * @return bool
     */
    protected function create(){
        global $database;

        $attributes = $this->sanitized_attributes();

        $sql = "INSERT INTO ".static::$table_name. " (";
        $sql .= join(", ", array_keys($attributes));
        $sql .= ") VALUES ('";
        $sql .= join("', '", array_values($attributes));
        $sql .= "')";

        if ($database->query($sql)){
            $this->id = $database->inserted_id();
            return true;
        } else {
            return false;
        }

    }

    /**
     * @return bool
     */
    protected function update(){
        global $database;

        $attributes = $this->sanitized_attributes();
        $attribute_pairs = array();
        foreach ($attributes as $key => $value){
            $attribute_pairs[] = "{$key}='$value'";
        }

        $sql = "UPDATE ".static::$table_name. " SET ";
        $sql .= join(", ", $attribute_pairs);
        $sql .= " WHERE id=" . $database->escape_value($this->id); //this escape not too necessary though

        $database->query($sql);
        return ($database->affected_rows() == 1) ? true : false;
    }

    /**
     * @return bool
     */
    public function delete(){
        global $database;

        $sql = "DELETE FROM ".static::$table_name. " ";
        $sql .= "WHERE id=" . $database->escape_value($this->id) . " ";
        $sql .= "LIMIT 1";

        $database->query($sql);
        return ($database->affected_rows() == 1) ? true : false;

    }


    public static function find_all(){
        return static::find_by_sql("SELECT * FROM ".static::$table_name);
    }

    public static function find_by_id($id=0){
        global $database;

        $result_array = static::find_by_sql("SELECT * FROM " .
            static::$table_name . " WHERE id=". $database->escape_value((int) $id) . " LIMIT 1");

        return !empty($result_array) ? $result_array[0] : false;
    }

    /**
     * @param string $sql
     * @return static[]
     */
    public static function find_by_sql($sql=""){
        global $database;
        $result_set = $database->query($sql);
        $object_array = array();
        while ($row = $database->fetch_array($result_set)){
            $object_array[] = static::instantiate($row); //create each object from row append to object_array
        }
        return $object_array;
    }

    public static function count_all(){
        global $database;
        $sql = "SELECT COUNT(*) FROM " . static::$table_name;
        $result_set = $database->query($sql);
        $row = $database->fetch_array($result_set);
        return array_shift($row);
    }

}