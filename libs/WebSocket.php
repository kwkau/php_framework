<?php
class WebSocket {

    //user profile information
    /**
     * @var $user user_mdl the user that connects to the websocket server
     */
    public $user;

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

    function __construct($id, $socket){
        $this->id = $id;
        $this->socket = $socket;
    }


}