<?php


class MainSocket extends wsserver{



    public function __construct($address,$port)
    {
        parent::__construct($address,$port);
    }


    public function onOpen(WebSocket $socket, Packet $packet)
    {
        /*
         * when a socket is opened we will need to add the phone number of  the phone that
         * opened the socket connection to the socket object
         * */

        //check if we have a valid packet
        if($packet->valid){
            //cache socket if it hasn't been cached yet
            $this->add_socket($socket);

            /*-----------------------------------------------------------
             * we need to send the token to the user that just connected
             *---------------------------------------------------------*/
            $this->post($socket,$packet->channel,"token_data",array());
        }


    }

    protected function onMessage(WebSocket $socket, Packet $packet)
    {
        try{
            /*--------------------------------------------------------
             * check if the channel specified in the packet matches a
             * channel in our registered routes
             *--------------------------------*/
            $websocket_routes = WSRoute::$mappings;

            if(array_key_exists($packet->channel,$websocket_routes)){
                //obtain the routes which belong to the channel
                $channel_routes = $websocket_routes[$packet->channel];

                $route_found = false;
                foreach ($channel_routes as $route) {
                    if(array_search($packet->data_type,$route)){
                        $route_found = $route;
                        break;
                    }
                }

                if($route_found){
                    $controller = $this->get_controller($route_found[WSRoute::CONTROLLER_KEY]);
                    if(method_exists($controller,$route_found[WSRoute::ACTION_METHOD_KEY])){
                        $controller->{$route_found[WSRoute::ACTION_METHOD_KEY]}($this,$packet,$socket);
                        return false;
                    }
                }

            }
        }catch (Exception $e){
            $this->log_err($e,$packet);
            return false;
        }
    }

    private function get_controller($controller_name){
        $file = "controller/{$controller_name}.php";
        /*------------------------------------------------------------------
         * we are checking to see if the requested file in the url exists
         * if it does we load by creating an instance of it's class,  if it
         * doesn't we call the error handler
         *---------------------------------*/
        if (file_exists($file)) {
            return new $controller_name;
        } else {
            return false;
        }
    }

    protected function onClose(WebSocket $socket)
    {
        $this->remove_socket($socket);
    }

    function connecting(WebSocket $user)
    {
        // TODO: Implement connecting() method.
    }
}