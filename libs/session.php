<?php

class session extends PDO{
    /**
     * @name this is a custom session management system for sswap
     */
    function __construct($session_name,$secure){
        // set our custom session functions.
        session_set_save_handler(array($this, 'open'), array($this,'close'), array($this,'read'), array($this,'write'), array($this,'destroy'), array($this,'gc'));       
        // This line prevents unexpected effects when using objects as save handlers.
        register_shutdown_function('session_write_close');
        $this->start_session($session_name,$secure);
    }
/*
 * This function will be called every time you want to start a new session, use it instead of session_start();
 */
    public function start_session($session_name,$secure){
        // Make sure the session cookie is not accessable via javascript.
        $http_only = true;
        // Hash algorithm to use for the sessionid. (use hash_algos() to get a list of available hashes.)
        $session_hash = 'sha512';
        // Check if hash is available
        if(in_array($session_hash, hash_algos())){
            ini_set('session.hash_function', $session_hash);
        }
   // How many bits per character of the hash.
   // The possible values are '4' (0-9, a-f), '5' (0-9, a-v), and '6' (0-9, a-z, A-Z, "-", ",").
   ini_set('session.hash_bits_per_character', 5);
   // Force the session to only use cookies, not URL variables.
   ini_set('session.use_only_cookies',1);
        //get session cookie parameters 
        $cookie_params = session_get_cookie_params();
        //set the parameters
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookie_params['domain'], $secure, $http_only);       
        //change the session name
        session_name($session_name);
        //now we can start the session
        if(!session_id()){
        @session_start();
        }
        // This line regenerates the session and deletes the old one. 
        // It also generates a new encryption key in the database.
        session_regenerate_id();
    }
    function open(){
        $session_db = "secure_sessions";
        try{
        $pdo = new PDO(DB_TYPE . ":host=" . HOST_NAME . ";dbname=" . $session_db , DB_USER, DB_PASS);
        $this->db = $pdo;
        }catch(Exception $error_string){
            /**
             * @author Kwaku Appiah-Kubby Osei <kweku55@yahoo.com>
             * we will have to take log of every error that occurs by storing 
             * the time it occured
             * the kind of error that has occured 
             * the location of the error that has occured 
             * and if possible the actual code that actually caused the error
             * the line number on which the code occured
             * these information must be stored in a log database any time a database error occurs
             */
            echo "We are having problems with our servers please try 
                again later sorry for any inconvinience caused <br />".$error_string->getMessage()."<br />";
            echo DB_PASS;
        }
        return true;
    }
    function close(){
        return true;
    }
    /**
     * This function will be called by PHP when we try to access a session for example when we use echo $_SESSION['something'];. 
     * Because there might be many calls to this function on a single page, we take advantage of prepared statements, not only 
     * for security but for performance also. We only prepare the statement once then we can execute it many times.
     * We also decrypt the session data that is encrypted in the database. We are using 256-bit AES encryption in our sessions.
     */
   function read($id){
       if (!isset($this->read_stmt)) {
            $this->read_stmt = $this->db->prepare('select data from sessions where id = ? limit 1');
            $this->read_stmt->execute(array($id));
            $dat = $this->read_stmt->fetch(PDO::FETCH_ASSOC);
            $key = $this->getkey($id);
            $data = $this->decrypt($dat['data'], $key);
            return $data;
       }else return false;          
   }
   /*
    * This function is used when we assign a value to a session, for example $_SESSION['something'] = 'something else';. 
    * The function encrypts all the data which gets inserted into the database.
    */
   function write($id,$data){
       //get unique key
       $key = $this->getkey($id);
       //encrypt the data
       $data = $this->encrypt($data,$key);
       $time = time();
       if(!isset($this->w_stmt)){
           $this->w_stmt = $this->db->prepare("replace into sessions (id, set_time, data, session_key) values (?, ?, ?, ?)");
           $this->w_stmt->execute(array($id,$time,$data,$key));
           return true;
       }else return false;
   } 
    /*
     * This function deletes the session from the database, it is used by php when we call functions like session__destroy().
     */
    function destroy($id){
        if(!isset($this->delete_stmt)){
            $this->delete_stmt = $this->db->prepare('delete from sessions where id = ?');
        }
        $this->delete_stmt->execute(array($id));
        return true;
    }
    /*
     * This function is the garbage collecter function it is called to delete old sessions. The frequency in which this function is 
     * called is determined by two configuration directives, session.gc_probability and session.gc_divisor.
     */
    function gc($max){
        if(!isset($this->gc_stmt)){
            $this->gc_stmt = $this->prepare('delete from sessions where set_time < ?');
        } 
        $old = time() - $max;
        $this->gc_stmt->execute(array($old));
        return true;
    }
    /*
     * This function is used to get the unique key for encryption from the sessions table. If there is no session it just returns 
     * a new random key for encryption.
     */
    private function getkey($id){
        if(!isset($this->key_stmt)){
            $this->key_stmt = $this->db->prepare('select session_key from sessions where id = ? limit 1');
        }
        $this->key_stmt->execute(array($id));
        if($this->key_stmt->rowCount() == 1){
           $key =  $this->key_stmt->fetch(PDO::FETCH_ASSOC);
            return $key['session_key'];
        }
        else{
            $random_key = hash('sha512', uniqid(mt_rand(1, mt_getrandmax()), true));
            return $random_key;
        }
    }
    /*
     * These functions encrypt the data of the sessions, they use a encryption key from the database which is different 
     * for each session. We don't directly use that key in the encryption but we use it to make the key hash even more
     * random.
     */
    private function encrypt($data,$key){
   $salt = 'cH!swe!retReGu7W6bEDRuk7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';  
   $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data,MCRYPT_MODE_ECB, $iv));  
   return $encrypted;
    }
    private function decrypt($data, $key) {
   $salt = 'cH!swe!retReGu7W6bEDRuk7usuDUh9THeD2CHeGE*ewr4n39=E@rAsp7c-Ph@pH';
   $key = substr(hash('sha256', $salt.$key.$salt), 0, 32);
   $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
   $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
   $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv);
   return $decrypted;
} 
    public static function set($key , $value) {         
        $_SESSION[$key] = $value;      
    }
    public static function get($key) {
        if(array_key_exists($key,$_SESSION)){
        return $_SESSION[$key];
        }else{
            return false;
        }
    }
    public static function end() {
        session_unset();
        session_destroy();
    }
}
?>
