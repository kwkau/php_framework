<?php



class Response {

    public function __construct()
    {

    }

    /**
     * this function is used to respond to an http request with json data
     * @param $data mixed the data to be sent in json format
     */
    public static function json($data)
    {
        header('Content-Type: application/json',true);
        print json_encode($data,15);
    }
}