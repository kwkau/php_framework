<?php

/**
 * Class Model
 * <p>Enables your class to function as a model in this framework</p>
 */
class Model extends alpha
{
    private static $db;
    private static $dt;
    private $related = false;
    private $related_props = array();
    public static $schema = null;
    const ONE_TO_ONE = 0;
    const ONE_TO_MANY = 1;
    const MANY_TO_MANY = 2;
    const MANY_TO_ONE = 3;

    /**
     * Turns your class into a model to allow it to connect to databases an perform other model functions
     */
    public function __construct()
    {
        //self::$db = new database();
        self::$dt = new date_time();
    }

    private function enc_params(&$params)
    {
        $exclusions = isset($this->exclusions)?$this->exclusions:array();
        foreach ($params as $key => $value) {
            if($key != "id" && $key != "created_at" && $key != "updated_at" && !$this->array_search($key,$exclusions)){
                $params[$key] = $this->aes_encrypt($value);
            }
        }
    }



    private function dec_data(&$rows)
    {
        $table = function_exists("get_called_class")?get_called_class():__CLASS__;
        $model = new $table;
        $exclusions = isset($model->exclusions)?$model->exclusions:array();
        foreach ($rows as $key => $value) {
            if(is_array($value)){
                foreach ($value as $k => $val) {
                    if($k != "id" && $k != "created_at" && $k != "updated_at" && !self::array_search($k,$exclusions)){
                        $value[$k] = parent::aes_decrypt($val);
                    }
                }
                $rows[$key] = $value;
            }else{
                if($key != "id" && $key != "created_at" && $key != "updated_at" && !self::array_search($key,$exclusions)){
                    $rows[$key] = parent::aes_decrypt($value);
                }
            }
        }
    }

    /**
     * function to return an instance of a model object
     * @param null|String $model_name the name of the name of the model, this parameter will be need to be provided
     * if version PHP < 5.4
     * @return Model an instance of this model
     */
    public static function instance($model_name=null){
        $model = is_null($model_name)? self::get_static_table_name():$model_name;
        return new $model;
    }


    /*-------------------------------
     * pdo database helper functions
     *------------------------------*/

    private function pdo_gen($query, $params, $fetch_mode = null, $fetch_all = false)
    {
        @self::reset();
        try {

            $user_sth = self::$db->prepare($query);
            $user_sth->execute($params);
            if(!is_null($fetch_mode)){
                $result['count'] = $user_sth->rowCount();
                $result["data"] = $fetch_all ? $user_sth->fetchAll($fetch_mode) : $user_sth->fetch($fetch_mode);
                $result["data"];
            }
            $check = self::$db->check_error($user_sth->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $user_sth = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error);
        }
    }

    /**
     * function to fetch data from the database using PDO fetch
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter" => value)
     * @param $fetch_mode int PDO constant values to determine the method for fetching the data
     * @param $fetch_all bool set to true if query will return multiple rows and false for a single
     * e.g. PDO::FETCH_ASSOC, PDO::FETCH_NUM, PDO::FETCH_CLASS etc
     * @return array returns a multidimensional array with associative index of count for the number of rows
     * returned and data for the array that stores the rows that have been fetched.
     */
    public function pdo_fetch($query, $params, $fetch_mode, $fetch_all = false)
    {
        @self::reset();
        try {
            $user_sth = self::$db->prepare($query);
            $user_sth->execute($params);
            $result['count'] = $user_sth->rowCount();
            $result["data"] = $fetch_all ? $user_sth->fetchAll($fetch_mode) : $user_sth->fetch($fetch_mode);
            $check = self::$db->check_error($user_sth->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $user_sth = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error);
        }
    }

    /**
     * function to map data fetched from the database to a class using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @param $class string the name of the class who's properties will be mapped to the returned data set
     * @return array|mixed returns an array with indexes count for the number of row returned and instances
     * for the mapped class and index state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_fetchClass($query, $params, $class)
    {
        $this->reset();
        try {
            $result = array();
            $prof_sth = self::$db->prepare($query);
            $prof_sth->setFetchMode(PDO::FETCH_CLASS, $class);
            $prof_sth->execute($params);
            $result['count'] = $prof_sth->rowCount();
            $result['instances'] = $prof_sth->fetch(PDO::FETCH_CLASS);
            $check = self::$db->check_error($prof_sth->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $prof_sth = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error);
        }
    }


    /**
     * function to insert data into a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_insert($query, $params)
    {
        $this->reset();
        try {
            $result = array();
            $sth_log = self::$db->prepare($query);
            $sth_log->execute($params);
            $check = self::$db->check_error($sth_log->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $result["id"] = self::$db->lastInsertId();
            $sth_log = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error->getMessage());
        }
    }


    /**
     * function to update data stored in a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_update($query, $params)
    {
        $this->reset();
        try {
            $result = array();
            $sth_log = self::$db->prepare($query);
            $sth_log->execute($params);
            $check = self::$db->check_error($sth_log->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $sth_log = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error);
        }
    }


    /**
     * function to delete data stored in a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_delete($query, $params)
    {
        $this->reset();
        try {
            $result = array();
            $sth_log = self::$db->prepare($query);
            $sth_log->execute($params);
            $check = self::$db->check_error($sth_log->errorInfo());
            $result['state'] = $check["state"];
            $result['error_info'] = $check["error_info"];
            $sth_log = null;
            self::$db = null;
            return $result;
        } catch (PDOException $error) {
            return array('state' => false, 'error_info' => $error);
        }
    }

    private function reset()
    {
        self::$db = new database();
        self::$dt = new date_time();
    }


    /**
     * function to insert a model into the database
     * the name of the model class must be the same as the table its being mapped to
     * in the database
     * @param bool $model_return specify if you want the model you just inserted returned
     * @param bool $set_timestamps
     * @return array the status of our insertion ['state'=>bool, 'error_info'=>string, 'model'=>Model] is returned
     */
    public function insert($model_return=false,$set_timestamps = true)
    {
        $model = $this->get_model_props();
        $date = self::$dt->get_date("");
        if (!isset($model["props"]["created_at"]) && $set_timestamps) {
            $model["props"]["created_at"] = $date;
        }
        if (!isset($model["props"]["updated_at"]) && $set_timestamps) {
            $model["props"]["updated_at"] = $date;
        }

        $result = $this->pdo_insert($this->build_insert_query($set_timestamps), array_values($model["props"]));
        $model = $this->get_instance_table_name();

        if($model_return){
            $result["model"] = $model::where("id", "=", $result["id"],$model);
        }
        unset($result["id"]);

        return $result;
    }


    /**
     * function to fetch the data of a particular record in our db with the provided id
     * the data received is mapped unto the instance of the model that requested the data
     * @param $id int the unique identifier of the record to fetched
     * @return $this
     */
    public function get($id)
    {
        $result = $this->pdo_fetch($this->build_get_query(), array($id), PDO::FETCH_ASSOC, false);
        if ($result["count"] > 0) {
            $this->mapper($this, $result["data"]);
        }
        return $this;
    }

    private static function get_static_table_name()
    {
        return function_exists("get_called_class")?get_called_class():__CLASS__;
    }

    /**
     * function to fetch a model from the database based on the condition provided
     * @param $column
     * @param $operation
     * @param $val
     * @param null $mdl
     * @return Model|array|bool
     */
    public static function where($column, $operation, $val,$mdl=null)
    {
        $table = self::get_static_table_name();
        $result = @self::pdo_fetch(@self::build_where_query($column, $operation), array($val), PDO::FETCH_ASSOC, true);
        if ($result["count"] == 1) {
            //we need to return only a single instance of this model
            $model = new $table;
            foreach ($result["data"] as $row) {
                @self::mapper($model, $row);
            }
            return $model;
        } elseif ($result["count"] > 1) {
            //we need to return an array of instances of this model
            $models = array();
            foreach ($result["data"] as $row) {
                $model = new $table;
                @self::mapper($model, $row);
                $models[] = $model;
            }
            return $models;
        }
        return false;
    }

    /**
     * function to fetch data with the help of compound logical statements joined together by
     * the logical operators AND, OR
     * condition format:
     *  array("column"=>[string, the name of column], "operation"=>[string, the operation you want to perform], "value"=>[mixed, the value to be used in the condition], [string, Logical operator])
     * example:
     * array(
     *    array("column"=>"age", "operation"=>">", value"=>18)
     * )
     * @param null|String $mdl the name of the model
     * @param $conditions array an array of conditions with their specified values
     * @return array|bool|Model
     */
    public static function wheres($conditions, $mdl=null)
    {
        $table = self::get_static_table_name();
        if(!is_null(self::$schema)){
            $table = self::$schema.".".$table;
        }

        $result = @self::build_conditions("SELECT * FROM {$table} WHERE ", $conditions);

        $result = @self::pdo_fetch($result["query"], $result["values"], PDO::FETCH_ASSOC, true);
        if ($result["count"] == 1) {
            //we need to return only a single instance of this model
            $model = new $table;
            foreach ($result["data"] as $row) {
                @self::mapper($model, $row);
            }
            return $model;
        } elseif ($result["count"] > 1) {
            //we need to return an array of instances of this model
            $models = array();
            foreach ($result["data"] as $row) {
                $model = new $table;
                @self::mapper($model, $row);
                $models[] = $model;
            }
            return $models;
        }
        return false;
    }

    /**
     * @param $query
     * @param $conditions
     * @return array ["query"=>$query,"values"=>$values]
     */
    public function build_conditions($query, $conditions)
    {

        $values = array();
        foreach ($conditions as $condition) {
            if (count($condition) > 3) {
                $query = $query . "{$condition['column']} {$condition['operation']} ? {$condition[0]} ";
                $values[] = $condition["value"];
            } else {
                $query = $query . "{$condition['column']} {$condition['operation']} ?";
                $values[] = $condition["value"];
            }
        }

        return array("query"=>$query.";","values"=>$values);
    }

    /**
     * @param $model
     * @param $data
     */
    private function mapper(&$model, $data)
    {
        foreach ($data as $key => $value) {
            $model->{$key} = $value;
        }
    }


    /**
     * function to update a model in the database with the current properties that have been set on the model
     * instance
     * @return array
     */
    public function update()
    {
        $model = $this->get_model_props();
        //print_r($model);
        $values = array_values($model["props"]);
        $values[] = self::$dt->get_date("");
        $values[] = $this->id;
        return $this->pdo_update($this->build_update_query(), $values);
    }

    /**
     * function to delete a model record from the database
     * @param null $id the id of the record to delete. the parameter can be left out if the models id property is already set
     * @return array
     */
    public function delete($id = null)
    {
        return $this->pdo_delete($this->build_delete_query(), array(is_null($id) ? $this->id : $id));
    }

    /**
     * function to map the array items in an array unto this model
     * @param $data array the array whos items you want to map unto this model
     * @param array $exclude array of key names in your $data array that you dont want mapped unto your model
     * @return $this
     */
    public function populate($data,$exclude=array("default"))
    {
        $exclude = array_flip($exclude);
        $data = is_array($data)?$data:is_object($data)?(Array)$data:null;
        foreach ($data as $key => $val) {
            if(!array_key_exists($key, $exclude)){
                if(is_array($val)){
                    //search if the array has an id key value
                    if(array_key_exists("id", $val)){
                        $this->{$key."_id"} = $val["id"];
                    }
                }elseif(is_object($val)){
                    //search if the object has an id property
                    if(isset($val->id)){
                        $this->{$key."_id"} = $val->id;
                    }
                }else{
                    $this->{$key} = $val;
                }
            }
        }
        return $this;
    }


    /**
     * function to toggle the flag state of a model
     * @return array
     */
    public function flagToggle()
    {
        return $this->pdo_update($this->build_flag_query(), array($this->flag));
    }


    /**
     * function to fetch all the records that belong to a model
     * @return array an array of records that belong to this model
     */
    public function getAll()
    {
        $table = $this->get_instance_table_name();
        $result = $this->pdo_fetch($this->build_get_all_query(), array(), PDO::FETCH_ASSOC, true);
        $models = array();
        foreach ($result["data"] as $row) {
            $model = new $table;
            $this->mapper($model, $row);
            $models[] = $model;
        }
        return $models;
    }


    /**
     * function to fetch data for a model as well the relations which have been specified for the model
     * in the $relations property
     * @param $id int the primary key of the model
     * @param array $conditions array of conditions to be applied to the relational fetching of data
     * @param array $relation_spec an array of the names of the tables you want your model to relate to from the
     * $relations array specified in your model
     * <h3>Example of a condition array</h3>
     * <p>
     *   <pre>
     *      array(
     *           "the name of the relating model"=>[
     *               [
     *                   "column" => "the name of the column to use in our condition",
     *                   "operation" => "the logical operator to use for the condition",
     *                   "value" => "the value to test for our condition",
     *                   "Logical operator without a specified key"
     *               ]
     *            ]
     *      )
     *   </pre>
     * </p>
     *
     * <h3>Example of the $relations array</h3>
     * <p>
     *   <pre>
     *      protected $relations = array(
     *          [
     *              "table" => "the name of the table this model relates to",
     *              "type" => Model::MANY_TO_ONE,
     *              "foreign_key" => "the name of the foreign key used in the relation"
     *          ],
     *          [
     *              "table" => "the name of the table this model relates to",
     *              "type" => Model::ONE_TO_MANY,
     *              "foreign_key" => "the name of the foreign key used in the relation"
     *          ],
     *          [
     *              "table" => "the name of the table this model relates to",
     *              "type" => Model::ONE_TO_ONE,
     *              "foreign_key" => "the name of the foreign key used in the relation",
     *              "foreign_key_location" => "the name of the table where the foreign key is located"
     *          ]
     *          [
     *              "table" =>  "the name of the table this model relates to",
     *              "type" => Model::MANY_TO_MANY,
     *              "mid_table" => "name of intersecting table",
     *              "foreign_key_1" => "foreign key for the first table in the mid table",
     *              "foreign_key_2" => "foreign key for the second table in the mid table"
     *          ]
     *      );
     *   </pre>
     * </p>
     * <h3>Types of Relations</h3>
     * <ol>
     *      <li>
     *          Model::ONE_TO_ONE:
     *          a single instance of this model is related to only one instance of its related model. the
     *          foreign key can either be located in this model or the related table  hence the need to
     *          specify where the foreign key is located.
     *      </li>
     *      <li>
     *          Model::ONE_TO_MANY:
     *          a single instance of this model is related to two or more instances of its related model.
     *          foreign key is located in the related model.
     *      </li>
     *      <li>
     *          Model::MANY_TO_ONE:
     *          two or more instances of this model can be related to only one instance of the related
     *          model. the foreign key is located in this model.
     *      </li>
     *      <li>
     *          Model::MANY_TO_MANY:
     *          two or more instances of this model can be related to two or more instances of the related table,
     *          this relation involves the inclusion of a middle table made up of the foreign keys of the two relating
     *          tables hence the need to specify the name of the middle table and the column names of the two foreign keys
     *      </li>
     * </ol>
     *
     * @throws Exception when a foreign_key_location is not specified for a one to one relation
     */
    public function getRelations($id = null, $conditions = array(), $relation_spec = array())
    {
        $this->related = true;

        //fetch the data that belongs to this model
        if(!is_null($id)){
            $this->get($id);
        }


        if (isset($this->relations)) {//this model has relation to other models
            $relations = $this->relations;
            /*---------------------------------------------------------------------
             * if a user specifies the relations they want to retrieve the we must
             * trim down the relations object to match the relations the
             * user specifies
             *--------------*/
            if(count($relation_spec) > 0){
                foreach ($relations as $key => $relation) {
                    if (!$this->array_search($relation["table"], $relation_spec)) {
                        unset($relations[$key]);
                    }
                }
            }

            foreach ($relations as $relation) {
                /*-------------------------------------------------------------------------
                 * we need to check if the user has specified conditions for this relation
                 *-----------------------------------------------------------------------*/
                $rel_conditions = array();
                if(count($conditions) > 0){
                    if(array_key_exists($relation["table"],$conditions)){
                        $rel_conditions = $conditions[$relation["table"]];
                    }
                }
                $this->related_props[] = $relation["table"];
                if ($relation["type"] == Model::ONE_TO_ONE) {
                    if (isset($relation["foreign_key_location"])) {
                        if ($relation["foreign_key_location"] == $this->get_instance_table_name()) {
                            /*------------------------------------------
                             * the foreign key is located in this model
                             *----------------------------------------*/
                            $this->{$relation["table"]} = $this->fetchRelated($relation["table"], "id", $this->{$relation["foreign_key"]}, $relation["type"],$rel_conditions);
                        } else {
                            /*-------------------------------------------------
                             * the foreign key is located in the related table
                             *-----------------------------------------------*/
                            $this->{$relation["table"]} = $this->fetchRelated($relation["table"], $relation["foreign_key"], $this->id, $relation["type"],$rel_conditions);
                        }
                    } else {
                        throw new Exception('for a one to one relation a value must be specified for
                        foreign_key_location in the $relations array specified in your model');
                    }
                } elseif ($relation["type"] == Model::MANY_TO_MANY) {
                    /*----------------------------------------------------------------------------------
                     * first we need to search the middle table for the value of the second foreign key
                     *--------------------------------------------------------------------------------*/
                    $keys = $this->fetch_foreign_key_2($relation["mid_table"],$relation["foreign_key_1"],$this->id,$relation["foreign_key_2"]);
                    /*-------------------------------------------------------
                     * then now we can fetch the data from our related table
                     *-----------------------------------------------------*/
                    if($keys["count"] > 0){
                        $models = array();
                        foreach ($keys["data"] as $key) $models[] = $this->fetchRelated($relation["table"],"id",$key[$relation["foreign_key_2"]],$relation["type"],$rel_conditions);
                        $this->{$relation["table"]} = $models;
                    }else{
                        $this->{$relation["table"]} = null;
                    }


                } elseif ($relation["type"] == Model::MANY_TO_ONE) {
                    $this->{$relation["table"]} = $this->fetchRelated($relation["table"], "id", $this->{$relation["foreign_key"]}, $relation["type"],$rel_conditions);

                } else if ($relation["type"] == Model::ONE_TO_MANY) {
                    $this->{$relation["table"]} = $this->fetchRelated($relation["table"], $relation["foreign_key"], $this->id, $relation["type"],$rel_conditions);
                }
            }

        }
    }


    private function fetchRelated($relating_model, $foreign_key, $foreign_key_value, $relation_type, $conditions = array())
    {
        /*---------------------------------------------------------------------------
         * generate our query to fetch data, conditions, must be included if present
         *-------------------------------------------------------------------------*/
        if(is_array($result = $this->build_related_query($relating_model, $foreign_key,$conditions))){
            $query = $result["query"];
            $params = array_merge(array($foreign_key_value),$result["values"]);
        }else{
            $query = $result;
            $params = array($foreign_key_value);
        }

        if ($relation_type == Model::ONE_TO_ONE || $relation_type == Model::MANY_TO_ONE || $relation_type == Model::MANY_TO_MANY) {
            $result = $this->pdo_fetch($query, $params, PDO::FETCH_ASSOC, false);
            $rel = new $relating_model();
            $rel->get($result["data"]["id"]);
            return $rel;
        } elseif ($relation_type == Model::ONE_TO_MANY) {
            $models = array();
            $result = $this->pdo_fetch($query, $params, PDO::FETCH_ASSOC, true);
            foreach ($result["data"] as $row) {
                $rel = new $relating_model();
                $rel->get($row["id"]);
                $models[] = $rel;
            }
            return $models;
        }
    }

    private function array_search($needle,$haystack){
        foreach ($haystack as $item) {
            if($item == $needle){
                return true;
            }
        }
        return false;
    }

    /**
     * function to fetch the second foreign key, specified in $relations array, from the middle table of a
     * many to many relation
     * @param $mid_table string the name of the middle table
     * @param $foreign_key_1 string the column name of the first foreign key
     * @param $foreign_key_1_value int the value of the first foreign key
     * @param $foreign_key_2 string the column name of the second foreign key
     * @return array ["state"=>bool, "data"=>array, "count"=>int]
     */
    private function fetch_foreign_key_2($mid_table, $foreign_key_1, $foreign_key_1_value, $foreign_key_2)
    {
        $query = "SELECT {$foreign_key_2} FROM {$mid_table} WHERE {$foreign_key_1} = ?;";
        return $this->pdo_fetch($query,array($foreign_key_1_value),PDO::FETCH_ASSOC,true);
    }


    /**
     * function to obtain the table name to use for our queries
     * @return string
     */
    private function get_instance_table_name()
    {
        /*-------------------------------------------------------------------
         * we need to check if the this model belongs to a particular schema
         *-----------------------------------------------------------------*/
        $table_name = get_class($this);
        if (!is_null(self::$schema)) {
            $table_name = self::$schema . "." . $table_name;
        }
        return $table_name;
    }

    /**
     * function to build a where query
     * @param $column
     * @param $operation
     * @return string
     */
    public function build_where_query($column, $operation)
    {
        $table = function_exists("get_called_class")?get_called_class():__CLASS__;
        return "SELECT * FROM {$table} WHERE {$table}.{$column} {$operation} ?;";
    }

    /**
     * function to build a compound where query
     * @param $column
     * @param $operation
     * @return string
     */
    public function build_multiple_conditions_query($column, $operation,$mdl)
    {
        $table = $mdl;
        return "SELECT * FROM {$table} WHERE {$table}.{$column} {$operation} ?;";
    }


    /**
     * function to build a select query to fetch data for a related table
     * @param $relating_table
     * @param $foreign_key
     * @param array $conditions
     * @return array|string
     */
    private function build_related_query($relating_table, $foreign_key, $conditions = array())
    {
        $query = "SELECT id FROM {$relating_table} WHERE {$relating_table}.{$foreign_key} = ?";
        if (count($conditions) > 0) {
            $result = $this->build_conditions($query." AND ", $conditions);
        } else {
            $result = $query;
        }
        return $result;
    }


    /**
     * function to build an update query to update the flag column of a table
     * @return string
     */
    private function build_flag_query()
    {
        $table = $this->get_instance_table_name();

        return "UPDATE {$table} SET {$table}.flag = ?;";
    }


    /**
     * function to build an insert query to insert a model record into the database
     * @return string
     */
    private function build_insert_query($timestamps)
    {
        $model = $this->get_model_props();

        $columns = implode(", ", array_keys($model["props"]));
        $values = implode(", ", $model["values"]);
        if($timestamps){
            return "INSERT INTO {$this->get_instance_table_name()} ({$columns}, created_at, updated_at) VALUES ({$values}, ?, ?);";
        }else{
            return "INSERT INTO {$this->get_instance_table_name()} ({$columns}, created_at, updated_at) VALUES ({$values});";
        }
    }


    /**
     * function to build a select query to fetch data for a particular model
     * @return string
     */
    private function build_get_query()
    {
        $table = $this->get_instance_table_name();
        return "SELECT * FROM {$table} WHERE {$table}.id = ?;";
    }


    /**
     * function to build a select query to fetch all the records that belong to a particular model
     * @return string
     */
    private function build_get_all_query()
    {
        $table = $this->get_instance_table_name();
        return "SELECT * FROM {$table};";
    }


    /**
     * function to build an update query to update the model properties specified on an instance of a model into its
     * respective table in the database
     * @return string
     */
    private function build_update_query()
    {
        $table = $this->get_instance_table_name();

        $model = $this->get_model_props();

        $columns = array();
        foreach ($model["props"] as $key => $value) {
            $columns[] = "{$table}.{$key} = ?";
        }
        $updates = implode(', ', $columns);
        return "UPDATE {$table} SET {$updates}, {$table}.updated_at = ? WHERE {$table}.id = ?;";
    }


    /**
     * function to build a delete query to delete a model from the database
     * @return string
     */
    private function build_delete_query()
    {
        $table = $this->get_instance_table_name();
        return "DELETE FROM {$table} WHERE {$table}.id = ?;";
    }


    /**
     * function to retrieve the properties that have been set on an instance of a model
     * @return array ["props" => [model_property=>property_value,...], "values" => ['?','?'..]]
     */
    private function get_model_props()
    {
        /*-------------------------------------------------------
         * obtain the properties that has been set on our object
         *-----------------------------------------------------*/

        $props = Introspection::getObjectProperties($this);
        $values = array();
        for ($i = 0; $i < count(array_keys($props)); ++$i) {
            $values[] = "?";
        }

        if (is_null(self::$db)) {
            $this->reset();
        }

        if ($this->related) {
            //we need to get rid of the relations assigned to this model if there is any present
            foreach ($this->related_props as $prop) {
                if (isset($props[$prop])) {
                    unset($props[$prop]);
                }
            }
        }

        /*--------------------------------------------------------------------------------------
         * we are encrypting the data set on any instance if the ENCRYPT setting is set to true
         *------------------------------------------------------------------------------------*/
        if(defined("ENCRYPT") && ENCRYPT){
            $this->enc_params($props);
        }

        return array("props" => $props, "values" => $values);
    }

    public static function query($query_String, $params)
    {
        $result = self::pdo_gen($query_String,$params,PDO::FETCH_ASSOC,true);
        return $result["state"]?$result["data"]:$result["error_info"];
    }
}