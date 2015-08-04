<?php


class wsocket extends WebSocketServer{


    public function  __construct(){

    }


    public function start($addr,$port){
        parent::__construct($addr,$port);
    }

    //this function will be executed when a user sends a message to this socket server
    protected function process($user,$message){
        $this->send($user,$message);
    }

    //this function will be executed when a user opens a connection to this socket server
    protected function connected($sckt){



    }

    //this function will be executed when a user closes connection to this socket server
    protected function closed($sckt){

    }
}