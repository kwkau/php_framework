<?php

class WebSocketUser {

    //user profile information
    public $username;
    public $prof_pic_url;
    public $chat_priv_opt;

    //user websocket information
    public $socket;
    public $name;
    public $id;
    public $headers = array ();
    public $handshake = false;

    public $handlingPartialPacket = false;
    public $partialBuffer = "";

    public $sendingContinuous = false;
    public $partialMessage = "";

    public $hasSentClose = false;

    function __construct($id, $socket) {
        $this->id = $id;
        $this->socket = $socket;
        $this->load_profile();
    }

    public function load_profile () {
//      $this->user_id = session::get();
//      $this->prof_pic_url = session::get();
//      $this->chat_priv_opt = session::get();
    }
}