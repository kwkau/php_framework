<?php

class Packet {

    public function __construct($data = null)
    {
        if(!is_null($data)){
            $parse = $this->decode($data);
            if($this->valid = $this->verify($parse)){
                $this->channel = $parse["channel"];
                $this->token = isset($parse["token"])?$parse["token"]:null;
                $this->payload = self::dec_payload($parse["payload"]);
                $this->data_type = $parse["data_type"];
            }
        }
    }

    public $channel;
    public $payload;
    public $data_type;
    public $token;
    public $valid = false;

    /**
     * parses a json string into an associative array
     * @param $data string|Object json string or json object
     * @return array|bool returns an associative array if the json string or object provided is valid and false if otherwise
     */
    private function decode($data)
    {
        $packet = false;
        if(is_object($data)){
            $packet = (Array)$data;
            $packet["payload"] = isset($packet["token"])?json_decode(self::dec_payload($packet["payload"])):$packet["payload"];
        }elseif(is_string($data)){
            $packet = (Array)json_decode($data);
            $packet["payload"] = isset($packet["token"])?json_decode(self::dec_payload($packet["payload"])):$packet["payload"];
        }
        return $packet;
    }

    private function verify($array)
    {
        return isset($array["channel"]) && isset($array["payload"]);
    }

    public function enc_payload(&$data)
    {
        $rsa = new RSA();
        $rsa->loadKey(session::get("ws_private_key"));
        $data = $rsa->encrypt(json_encode($data));
    }

    private function dec_payload($data)
    {
        $rsa = new RSA();
        $rsa->loadKey($this->token);
        return $rsa->decrypt($data);
    }

    public static function cook($channel, $data_type, $data)
    {
        self::enc_payload($data);
        $packet = new Packet();
        $packet->channel = $channel;
        $packet->data_type = $data_type;
        $packet->token = session::get("ws_token");
        $packet->payload = $data;
        return json_decode($packet);
    }
}