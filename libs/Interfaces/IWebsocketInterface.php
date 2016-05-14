<?php


interface IWebsocketInterface {


    /**
     * Called immediately when data is received from a socket.
     * Override this websocket to send data to a websocket that is connected to this server
     * @param WebSocket $socket the socket which sent the data
     * @param $message string the data received from the socket
     * @return mixed
     */
    function process(WebSocket $socket,$message);


    /**
     * Called after the handshake response is sent to the client.
     * Override this method to handle what happens to a websocket when it connects to the server
     * @param WebSocket $socket the socket which has just connected to the server
     * @param $message string a message that is sent from the socket upon connection
     * @return mixed
     */
    function connected(WebSocket $socket,$message);


    /**
     * Called after a connection to a socket is closed.
     * Override this method to handle what happens to a websocket before it is closed
     * @param WebSocket $socket
     * @return mixed
     */
    function closed(WebSocket $socket);



    /**
     * Override to handle a connecting user, after the instance of the User is created, but before
     * the handshake has completed.
     * @param WebSocket $socket the socket trying to connect
     * @return mixed
     */
    function connecting(WebSocket $socket);


    /**
     * Called when the Websocket server is started
     * @return mixed
     */
    static function onStart();

    /**
     * Called when the WebSocket server is stopped
     * @return mixed
     */
    static function onStop();

    /**
     * Called when the WebSocket server is paused whiles it was running
     * @return mixed
     */
    static function onPause();

    /**
     * Called when the WebSocket server resumes operation after it has been paused
     * @return mixed
     */
    static function onResume();
}