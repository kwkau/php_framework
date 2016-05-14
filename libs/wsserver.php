<?php

abstract class wsserver extends WebSocketServer{
    /**
     * @var $channel string the name of the socket
     */
    public $channel = "default";

    /**
     * @var $server_sockets array a list of sockets that are connected to the socket server
     */
    public $server_sockets = array();

    public function __construct($address, $port)
    {
         parent::__construct($address,$port);
    }

    public function connected(WebSocket $socket,$msg)
    {
        /*------------------------------------
         * parse data into a websocket packet
         *----------------------------------*/
        $packet = new Packet($msg);
        $this->onOpen($socket,$packet);
    }

    public function process(WebSocket $socket, $msg)
    {
        /*------------------------------------
         * parse data into a websocket packet
         *----------------------------------*/
        $packet = new Packet($msg);
        if($packet->valid){
            $this->onMessage($socket,$packet);
        }
    }



    public function closed(WebSocket $socket)
    {
        $this->onClose($socket);
    }

    //Web Socket server events
    abstract protected function onOpen(WebSocket $socket, Packet $packet);//executed whenever the socket server establishes a new connection
    abstract protected function onMessage(WebSocket $socket, Packet $packet);//executed when the socket sever receives data from a socket
    abstract protected function onClose(WebSocket $socket);//executed when a socket closes the connection to the socket server
    /*abstract protected function onerror(Socket $socket);*/

    /**
     * function to send data to all the sockets that are connected to this server
     * @param WebSocket $main the socket that sent the initial data
     * @param $channel string the channel on which to send the data
     * @param $data_type string the name of the data that is being sent
     * @param $data mixed the data to be sent
     */
    public function broadcast (WebSocket $main,$channel,$data_type,$data)
    {
        foreach($this->server_sockets as $id => $socket){
            if($main->id != $socket->id){
                $this->post($socket,$channel,$data_type,$data);
            }
        }
    }



    /**
     * function to send data to the socket provided
     * @param WebSocket $socket the socket to receive the data
     * @param $channel string a description for the data being sent
     * @param $data_type string a description for the data being sent
     * @param $data mixed the data to be sent, could either be a string, Object or array
     */
    public function post(WebSocket $socket,$channel,$data_type,$data)
    {
        $this->send($socket,Packet::cook($channel,$data_type,$data));
    }



    public function find_socket_by_phone_number($phone_number)
    {
        $socket = false;
        foreach($this->server_sockets as $sckt){
            if($sckt->user->phone == $phone_number){
                $socket = $sckt;
            }
        }
        return $socket;
    }



    /**
     * finds a particular socket when provided the user id of the user of that socket
     * @param $id int the user id of the user the socket belongs to
     * @return WebSocket|bool returns a socket if the user id provided belongs to a valid socket that is connected to the socket
     */
    public function find_socket($id)
    {
        $socket = false;
        foreach($this->server_sockets as $socket){
            if($socket->user->id == $id){
                $socket = $socket;
            }
        }
        return $socket;
    }

    /**
     * function to check if a user is online
     * @param $id int the user id of the user
     * @return bool returns true if the user is online and false if otherwise
     */
    protected function online($id)
    {
        $online = false;
        foreach($this->server_sockets as $socket){
            if($socket->user->id == $id){
                $online = true;
            }
        }
        return $online;
    }



    /**
     * adds  a socket to the list of sockets that are connected to this socket server, the socket to be added must be a new socket
     * @param WebSocket $socket the socket to be added to the server
     * @return bool returns true if the socket is successfully added to the server and false if otherwise
     */
    protected function add_socket(WebSocket $socket)
    {
        /*---------------------------------------------------------------------------------------
         * a user who connects to a socket will be added to the server sockets array to keep
         * record of them
         *--------------------------------------------------------------------------------------*/

        if(!array_key_exists($socket->id, $this->server_sockets)){
            $this->server_sockets[$socket->id] = $socket;
            return true;
        }
        return false;
    }

    /**
     * removes a socket from the list of sockets that are connected to the socket server
     * @param WebSocket $socket the socket to be removed
     */
    protected function remove_socket(WebSocket $socket)
    {
        unset($this->server_sockets[$socket->id]);
    }

    public function log_err(Exception $ex, Packet $packet)
    {
        echo "webscoket channel: {$packet->channel}\r\n".
        "error message: ".$ex->getMessage();
    }

    /**
     * Called when the Websocket server is started
     * @return mixed
     */
    static function onStart()
    {
        // TODO: Implement onStart() method.
    }

    /**
     * Called when the WebSocket server is stopped
     * @return mixed
     */
    static function onStop()
    {
        // TODO: Implement onStop() method.
    }

    /**
     * Called when the WebSocket server is paused whiles it was running
     * @return mixed
     */
    static function onPause()
    {
        // TODO: Implement onPause() method.
    }

    /**
     * Called when the WebSocket server resumes operation after it has been paused
     * @return mixed
     */
    static function onResume()
    {
        // TODO: Implement onResume() method.
    }

}