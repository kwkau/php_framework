<?php
require "rb.phar";

/**
 * Class database
 */
class database extends PDO {


    /**
     * provides means to access a database either by using an orm or PDO
     * <p>this framework uses redbean as its orm</p>
     * @param null $config string the name of the method u want to use to manage your databases (orm or pdo)
     */
    public function __construct($config=null) {
        if($config == 'pdo'){
            //use pdo class to connect and manage the database
            //parent::__construct(mysql:host = localhost;dbname = sswap,root,"");
            try {
                @parent :: __construct(DB_TYPE . ":host=" . HOST_NAME . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
            } catch (PDOException $error) {

                die('Oops we seem to be experiencing some technical difficulties, please forgive us and try again later <br/> '. $error->getMessage() . '<br/>');
            }
        }else if($config == 'orm'){
            //use orm to connect and manage the database
            R::setup(DB_TYPE . ":host=" . HOST_NAME . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        }


    }
   
    

}
