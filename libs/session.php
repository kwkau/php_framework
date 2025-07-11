<?php

class session
{


    function __construct($session_name, $secure)
    {
        // set our custom session functions.

        if (!session::get('session_start')) {

            $this->start_session($session_name, $secure);
        }
    }

    /*
     * This function will be called every time you want to start a new session, use it instead of session_start();
     */

    public function start_session($session_name, $secure)
    {
        // Make sure the session cookie is not accessable via javascript.
        $http_only = true;

        // Hash algorithm to use for the sessionid. (use hash_algos() to get a list of available hashes.)
        $session_hash = 'sha512';

        // Check if hash is available
        if (in_array($session_hash, hash_algos())) {
            ini_set('session.hash_function', $session_hash);
        }


        // How many bits per character of the hash.
        // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
        ini_set('session.hash_bits_per_character', 5);


        // Force the session to only use cookies, not URL variables.
        ini_set('session.use_only_cookies', 1);


        //get session cookie parameters 
        $cookie_params = session_get_cookie_params();

        //set the parameters
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookie_params['domain'], $secure, $http_only);

        //change the session name
        session_name($session_name);

        //now we can start the session
        if (!session_id()) {
            @session_start();
        }
        // This line regenerates the session and deletes the old one. 
        // It also generates a new encryption key in the database.
        @session_regenerate_id(true);
        session::set('session_start', true);
    }


    /*
     * These functions encrypt the data of the sessions, they use a encryption key from the database which is different 
     * for each session. We don't directly use that key in the encryption but we use it to make the key hash even more
     * random.
     */

    private function encrypt($data, $key)
    {
        $salt = 'cH!swe!retReGu7W6bEDRuk7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
        $key = substr(hash('sha256', $salt . $key . $salt), 0, 32);
        
        // Use OpenSSL instead of deprecated mcrypt
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        
        return base64_encode($iv . $encrypted);
    }

    private function decrypt($data, $key)
    {
        $salt = 'cH!swe!retReGu7W6bEDRuk7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
        $key = substr(hash('sha256', $salt . $key . $salt), 0, 32);
        
        // Use OpenSSL instead of deprecated mcrypt
        $data = base64_decode($data);
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }


    /**
     * function to set a session value in the $_SESSION global array
     * @param $key string the associative name for the array to store the value
     * @param $value mixed the value to be stored
     */
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * function to retrieve a session variable from the $_SESSION global variable with the specified key
     * @param $key string the associative name of the $_SESSION array variable
     * @return mixed|bool returns the variable for the key supplied if it exists or false if otherwise
     */
    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } else {
            return false;
        }
    }

    /**
     * function to remove a value for the global $_SESSION array variable
     * @param $key string the associative name of the variable you want to remove
     * @return bool returns true if the key exists and has been removed and false if otherwise
     */
    public static function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        } else {
            return false;
        }
    }

    /**
     * function to end the current session and empty the $_SESSION array variable
     */
    public static function end()
    {
        unset($_SESSION);
        return session_destroy();
    }

}


