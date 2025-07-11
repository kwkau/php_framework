<?php

class database extends PDO {

    /*
     * we will need a way to allow the user to choose if they want to use the orm
     * or if they want to use the native pdo class to access databases.
     * how can we achieve this feet in a way which will be easy to use for users
     */

    public function __construct($config=null) {

            //use pdo class to connect and manage the database
            //parent::__construct(mysql:host = localhost;dbname = sswap,root,"");
            try {
                @parent :: __construct(DB_TYPE . ":host=" . HOST_NAME . ";dbname=" . DB_NAME, DB_USER, DB_PASS,array(
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ));
            } catch (PDOException $error) {
                // Log the actual error for debugging
                error_log("Database connection failed: " . $error->getMessage());
                
                // Show generic error to user - don't expose database details
                die('We are sorry for the inconvenience caused but we are having problems with our server, please try again later.');
            }
    }

    public function check_error($error_info)
    {
        if($error_info[0] == "00000" && is_null($error_info[1]) && is_null($error_info[2])){
            //statement was executed without any errors
            return array("state" => true, "error_info" => "");
        }else{
            return array("state" => false, "error_info" => "SQLSTATE[{$error_info[0]}] Driver Error Code:{$error_info[1]} Driver Error Message:{$error_info[2]}");
        }
    }

    public static function instance()
    {
        return new database();
    }

}
