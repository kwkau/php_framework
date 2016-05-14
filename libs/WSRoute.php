<?php

class WSRoute {

    const DATA_TYPE_KEY = "data_type";
    const CONTROLLER_KEY = "controller";
    const ACTION_METHOD_KEY = "action";

    public function __construct(){}

    /**
     * @var array this array will store all the registered routes with their channel as their associative key
     */
    public static $mappings = array ();

    /**
     * @var string the name of the channel to which websocket routes will be bound to
     */
    public static $channel = "default";


    /**
     * function to create a websocket route to respond to the specified data type received on a
     * channel
     * @param $data_type string the data type name this websocket route is being bound to
     * @param $controller string the name of the controller that will receive data from this websocket route
     * @param $action string the name of the action method in the specified controller that will perform
     * action on the data that will be received be this websocket route
     */
    public static function register($data_type,$controller,$action)
    {
        array_push(self::$mappings[self::$channel], array ("data_type" => $data_type, "controller" => $controller, "action" => $action));
    }

    /**
     * function to set the channel name for a socket route
     * @param $name string the name of the channel to the bind the preceding routes to
     */
    public static function channel($name)
    {
        self::$channel = $name;
        self::$mappings[self::$channel] = array();
    }

}