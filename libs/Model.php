<?php
/**
 * Class Model
 * <p>Enables your class to function as a model in this framework</p>
 */
class Model extends alpha{

    /**
     * Turns your class into a model to allow it to connect to databases an perform other model functions
     */
    public function __construct(){
       $this->db = new database("pdo");
        $this->dt = new date_time();
    }
    private static $check = 0;


    /*-------------------------------
     * pdo database helper functions
     *------------------------------*/

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
    public function pdo_fetch($query,$params,$fetch_mode,$fetch_all = false){
        try
        {
            $user_sth = $this->db->prepare($query);
            $user_sth->execute($params);
            $result['count'] = $user_sth->rowCount();
            $result["data"] = $fetch_all?$user_sth->fetchAll($fetch_mode):$user_sth->fetch($fetch_mode);
            $result['state'] = true;
            return $result;
        }catch (Exception $error)
        {
            if(self::$check < 20){
                $this->model_error_log($error,$query,$params);
                ++self::$check;
            }
            return array('state' => false,'error_info' => $error);
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
    public function pdo_fetchClass($query,$params,$class){
         try
         {
            $prof_sth = $this->db->prepare($query);
            $prof_sth->setFetchMode(PDO::FETCH_CLASS,$class);
            $prof_sth->execute($params);
             $result['count'] = $prof_sth->rowCount();
             $result['instances'] = $prof_sth->fetch(PDO::FETCH_CLASS);
             $result['state'] = true;
             return $result;
         }
        catch (Exception $error)
        {
            if(self::$check < 20){
                $this->model_error_log($error,$query,$params);
                ++self::$check;
            }
            return array('state' => false,'error_info' => $error);
        }
    }


    /**
     * function to insert data into a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_insert($query,$params){
        try
        {
            $sth_log = $this->db->prepare($query);
            $sth_log->execute($params);
            return array('state' => true,'error_info' => '');
        }catch(Exception $error)
        {
            if(self::$check < 20){
                $this->model_error_log($error,$query,$params);
                ++self::$check;
            }
            return array('state' => false,'error_info' => $error->getMessage());
        }
    }


    /**
     * function to update data stored in a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_update($query,$params){
        try
        {
            $sth_log = $this->db->prepare($query);
            $sth_log->execute($params);
            return array('state' => true,'error_info' => null);
        } catch (Exception $error)
        {
            if(self::$check < 20){
                $this->model_error_log($error,$query,$params);
                self::$check;
            }
            return array('state' => false,'error_info' => $error);
        }
    }


    /**
     * function to delete data stored in a database using PDO
     * @param $query string query string
     * @param $params array parameter value pair eg array(":parameter => value ")
     * @return array returns an array with indexes state which will be false if there was an error in execution
     * and error_info has the string value which contains information about the error
     */
    public function pdo_delete($query,$params){
        try
        {
            $sth_log = $this->db->prepare($query);
            $sth_log->execute($params);
            return array('state' => true,'error_info' => null);
        } catch(Exception $error)
        {
            if(self::$check < 20){
                $this->model_error_log($error,$query,$params);
                self::$check;
            }
            return array('state' => false,'error_info' => $error);
        }
    }



    /*----------------------
     * hcms fetch functions
     *---------------------*/

    /**
     * function to fetch the content for a single main container
     * @param $tagName string the name of the container
     * @param $contentType string the type of data that the container holds e.g.(text, picture or video)
     * @param $pageName string the name of the page that has this container
     * @return array list of the content for the main container
     */
    public function singular_fetch($tagName,$contentType,$pageName){
        return $this->pdo_fetch("CALL hcms.fetch_singular_content(':containerTag',':contentType',':pageName')",array(
            ":containerTag" => $tagName,
            ":contentType" => $contentType,
            ":pageName" => $pageName
        ),PDO::FETCH_ASSOC);
    }

    /**
     * function fetch content for a single main container together with its sub container
     * @param $mainContainerTag string the name of the container
     * @param $mainContentType string the type of data that the main container holds e.g.(text, picture or video)
     * @param $subContentType string the type of data that the sub container holds e.g.(text, picture or video)
     * @param $pageName string the name of the page that has this container
     * @return array
     */
    public function main_sub_fetch($mainContainerTag, $mainContentType, $subContentType, $pageName)
    {
        return $this->pdo_fetch("CALL hcms.fetch_main_sub_content(:containerTag,:mc_contentType,:sc_content_type,:pageName)",array(
            ":containerTag" => $mainContainerTag,
            ":mc_contentType" => $mainContentType,
            ":sc_content_type" => $subContentType,
            ":pageName" => $pageName
        ),PDO::FETCH_ASSOC);
    }

}