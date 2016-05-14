<?php

/**
 * the Route object allows us to create routes which your app can respond to with the respective controllers
 * and action methods that have been assigned to that particular route. the object provides static functions which
 * are used to map controllers and their action methods to specific urls to create our routes.
 */
class Route {


    public function __construct(){}

    /**
     * @var array this array will store all the registered routes with their base url as their associative key
     */
    public static $mappings = array ();

    /**
     * @var string a base url which must be assigned a value before the creation of any route
     */
    private static $base = "";


    /**
     * this is a function to create a route to respond to POST http request to the url provided
     * @param $url string the url you want to map a controller and or action method to
     * @param $controller string the name of the controller you want to bind to the url
     * @param string $action the name of the action method of the controller you want to bind to the url
     */
    public static function post($url,$controller,$action)
    {
        array_push(self::$mappings[self::$base], array ("method" => "POST", "url" => $url, "controller" => $controller, "action" => $action));
    }

    /**
     * this is a function to create a route to respond to GET http request to the url provided
     * @param $url string the url you want to map a controller and or action method to
     * @param $controller string the name of the controller you want to bind to the url
     * @param string $action the name of the action method of the controller you want to bind to the url
     */
    public static function get($url,$controller,$action)
    {
        array_push(self::$mappings[self::$base], array ("method" => "GET", "url" => $url, "controller" => $controller, "action" => $action));
    }

    /**
     * this is a function to create a route to respond to PUT http request to the url provided
     * @param $url string the url you want to map a controller and or action method to
     * @param $controller string the name of the controller you want to bind to the url
     * @param string $action the name of the action method of the controller you want to bind to the url
     */
    public static function put($url,$controller,$action)
    {
        array_push(self::$mappings[self::$base], array ("method" => "PUT", "url" => $url, "controller" => $controller, "action" => $action));
    }

    /**
     * this is a function to create a route to respond to DELETE http request to the url provided
     * @param $url string the url you want to map a controller and or action method to
     * @param $controller string the name of the controller you want to bind to the url
     * @param string $action the name of the action method of the controller you want to bind to the url
     */
    public static function delete($url,$controller,$action)
    {
        array_push(self::$mappings[self::$base], array ("method" => "DELETE", "url" => $url, "controller" => $controller, "action" => $action));
    }

    /**
     * this is a function which allows you to create a custom http request which the specified route will respond to
     * @param $method string the name of the custom request the route is supposed to respond to
     * @param $url string the url you want to map a controller and or action method to
     * @param $controller string the name of the controller you want to bind to the url
     * @param string $action the name of the action method of the controller you want to bind to the url
     */
    public static function custom($method, $url, $controller, $action)
    {
        array_push(self::$mappings[self::$base], array ("method" => $method, "url" => $url, "controller" => $controller, "action" => $action));
    }

    public static function base($name)
    {
        self::$base = $name;
        self::$mappings[self::$base] = array();
    }

}