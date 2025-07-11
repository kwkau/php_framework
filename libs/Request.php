<?php

class Request{

    public function __construct()
    {
        $this->get_request_method();
        $this->get_request_info();
    }

    public $content =null;

    public $params;
    public $request_method;
    public $query_string;
    public $ip;
    public $port;
    public $user_agent;
    public $redirect_status;
    public $https;
    public $content_type;


    /**
     * function to assign the request method of our http request
     */
    private function get_request_method()
    {
        $this->request_method = $_SERVER["REQUEST_METHOD"];
    }

    /**
     * function to assign the various http headers
     */
    private function get_request_info()
    {
        $this->query_string = $_SERVER["QUERY_STRING"];
        $this->https = isset($_SERVER["HTTPS"]);
        $this->ip = $_SERVER["REMOTE_ADDR"];
        $this->port = $_SERVER["REMOTE_PORT"];
        $this->user_agent = $_SERVER["HTTP_USER_AGENT"];
        $this->redirect_status = $_SERVER["REDIRECT_STATUS"];
        $this->port = $_SERVER["REMOTE_PORT"];
    }

    /**
     * Returns the request body content.
     *
     * @param bool $asResource If true, a resource will be returned
     *
     * @return string|resource The request body content or a resource to read the body stream.
     *
     * @throws \LogicException
     */
    public function getContent($asResource = false)
    {
        $currentContentIsResource = is_resource($this->content);
        /*if (PHP_VERSION_ID < 50600 && false === $this->content) {
            throw new \LogicException('getContent() can only be called once when using the resource return type and PHP below 5.6.');
        }*/

        if (true === $asResource) {
            if ($currentContentIsResource) {
                rewind($this->content);

                return $this->content;
            }

            // Content passed in parameter (test)
            if (is_string($this->content)) {
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $this->content);
                rewind($resource);

                return $resource;
            }

            $this->content = false;

            return fopen('php://input', 'rb');
        }

        if ($currentContentIsResource) {
            rewind($this->content);

            return stream_get_contents($this->content);
        }

        if (null === $this->content) {
            $this->content = file_get_contents('php://input');
        }

        return $this->content;
    }

    /**
     * Get the JSON payload for the request.
     *
     * @return array returns an array of the json object from our request
     */
    public function json_content()
    {
        return (array)json_decode($this->getContent(), true);
    }

    /**
     * this function fetches values from the data sent along with an http request by using the specified key which corresponds
     * to the resource be searched for
     *
     * @param $key string the key of the value being searched, this key must be the same as the key value stated in the data
     * that was sent along with the request
     * @param $sanitize bool whether to sanitize the input (default: true)
     * @return bool|mixed returns the corresponding value for key specified
     */
    public function input($key, $sanitize = true)
    {
        $json_val = false;
        //check if the request has json data of form data
        if ($this->is_json() || $this->is_formData()) {
            $json_content = $this->json_content();
            $json_val = isset($json_content[$key])?$json_content[$key]:false;
        }

        if(!$json_val){
            switch($this->get_method()){
                case "GET":
                    $value = isset($_GET[$key]) ? $_GET[$key] : false;
                    break;
                case "POST":
                    $value = filter_input(INPUT_POST, $key);
                    break;
                default:
                    $value = isset($_GET[$key]) ? $_GET[$key] : false;
                    break;
            }
        } else {
            $value = $json_val;
        }
        
        // Sanitize input if requested
        if ($sanitize && $value !== false) {
            $value = $this->sanitizeInput($value);
        }
        
        return $value;
    }
    
    /**
     * Sanitize input to prevent XSS and other attacks
     * @param mixed $input
     * @return mixed
     */
    private function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        if (is_string($input)) {
            // Remove null bytes
            $input = str_replace("\0", '', $input);
            // HTML encode special characters
            $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
            // Remove excessive whitespace
            $input = trim($input);
        }
        
        return $input;
    }
    
    /**
     * Get raw unsanitized input (use with caution)
     * @param string $key
     * @return mixed
     */
    public function raw_input($key) {
        return $this->input($key, false);
    }

    /**
     * function to check if the data sent in our request is the json format
     * @return bool true is the data sent along with the request is json and false otherwise
     */
    public function is_json()
    {
        return isset($_SERVER["CONTENT_TYPE"]) && is_string(strstr($_SERVER["CONTENT_TYPE"],"/json"));
    }

    /**
     * function to check if the data sent in our request is in then form data format
     * @return bool true if data is in the form data format
     */
    public function is_formData()
    {
        return isset($_SERVER["CONTENT_TYPE"]) && is_string(strstr($_SERVER["CONTENT_TYPE"],"/x-www-form-urlencoded"));
    }

    /**
     * function to check the request method of the current http request
     * @return string the request method
     */
    public function get_method()
    {
        return $this->request_method;
    }

    /*
     *Array
(
    [ALLUSERSPROFILE] => C:\ProgramData
    [ANDROID_HOME] => C:\Users\kwaku\AppData\Local\Android\sdk
    [APPDATA] => C:\Users\kwaku\AppData\Roaming
    [CommonProgramFiles] => C:\Program Files\Common Files
    [COMPUTERNAME] => KWAKU-PC
    [ComSpec] => C:\WINDOWS\system32\cmd.exe
    [FP_NO_HOST_CHECK] => NO
    [HerokuPath] => C:\Program Files\Heroku
    [HOMEDRIVE] => C:
    [HOMEPATH] => \Users\kwaku
    [JAVA_HOME] => C:\Program Files\Java\jdk1.7.0_71
    [LOCALAPPDATA] => C:\Users\kwaku\AppData\Local
    [LOGONSERVER] => \\MicrosoftAccount
    [NUMBER_OF_PROCESSORS] => 2
    [OS] => Windows_NT
    [Path] => C:\Users\kwaku\AppData\Roaming\Composer\vendor\bin;C:\WINDOWS\system32;C:\WINDOWS;C:\WINDOWS\System32\Wbem;C:\WINDOWS\System32\WindowsPowerShell\v1.0\;C:\Program Files\Microsoft ASP.NET\ASP.NET Web Pages\v1.0\;C:\Program Files\Windows Kits\8.0\Windows Performance Toolkit\;C:\Program Files\Microsoft SQL Server\110\Tools\Binn\;C:\Program Files\Heroku\bin;C:\Program Files\git\cmd;c:\Program Files\Microsoft SQL Server\110\DTS\Binn\;c:\Program Files\Microsoft SQL Server\110\Tools\Binn\ManagementStudio\;c:\Program Files\Microsoft Visual Studio 10.0\Common7\IDE\PrivateAssemblies\;C:\Program Files\nodejs\;C:\Program Files\Microsoft\Web Platform Installer\;C:\wamp\bin\php\php5.5.12;C:\ProgramData\ComposerSetup\bin;C:\Program Files\Git\cmd;C:\Users\kwaku\AppData\Roaming\npm;C:\Users\kwaku\AppData\Roaming\Composer\vendor\bin\laravel.bat
    [PATHEXT] => .COM;.EXE;.BAT;.CMD;.VBS;.VBE;.JS;.JSE;.WSF;.WSH;.MSC
    [PROCESSOR_ARCHITECTURE] => x86
    [PROCESSOR_IDENTIFIER] => x86 Family 6 Model 42 Stepping 7, GenuineIntel
    [PROCESSOR_LEVEL] => 6
    [PROCESSOR_REVISION] => 2a07
    [ProgramData] => C:\ProgramData
    [ProgramFiles] => C:\Program Files
    [PSModulePath] => C:\WINDOWS\system32\WindowsPowerShell\v1.0\Modules\;c:\Program Files\Microsoft SQL Server\110\Tools\PowerShell\Modules\
    [PT5HOME] => C:\Program Files\Cisco Packet Tracer 6.1sv
    [PT6HOME] => C:\Program Files\Cisco Packet Tracer 6.1sv
    [PUBLIC] => C:\Users\Public
    [SESSIONNAME] => Console
    [SystemDrive] => C:
    [SystemRoot] => C:\WINDOWS
    [TEMP] => C:\Users\kwaku\AppData\Local\Temp
    [TMP] => C:\Users\kwaku\AppData\Local\Temp
    [USERDOMAIN] => KWAKU-PC
    [USERDOMAIN_ROAMINGPROFILE] => KWAKU-PC
    [USERNAME] => kwaku
    [USERPROFILE] => C:\Users\kwaku
    [VS110COMNTOOLS] => C:\Program Files\Microsoft Visual Studio 11.0\Common7\Tools\
    [windir] => C:\WINDOWS
    [SCRIPT_NAME] => /educell/index.php
    [SCRIPT_FILENAME] => C:/laragon/www/educell/index.php
    [DOCUMENT_ROOT] => C:/laragon/www/educell
    [HTTP_CONTENT_LENGTH] => 0
    [HTTP_COOKIE] => _k%26a=ne2cc642gpmthu16negghbg1md2l381k9rb6gn3r6dn1bac69fbeh863pnva14of4k0q6i63s052n0r0633iu3024op5cspl9ri01f0
    [HTTP_ACCEPT_LANGUAGE] => en-US,en;q=0.8
    [HTTP_ACCEPT_ENCODING] => gzip, deflate, sdch
    [HTTP_DNT] => 1
    [HTTP_USER_AGENT] => Mozilla/5.0 (Windows NT 10.0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.73 Safari/537.36
    [HTTP_UPGRADE_INSECURE_REQUESTS] => 1
    [HTTP_ACCEPT] => text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,/*;q=0.8
    [HTTP_CONNECTION] => keep-alive
    [HTTP_HOST] => localhost:63342
    [CONTENT_LENGTH] => 0
    [QUERY_STRING] =>
    [REDIRECT_STATUS] => 200
    [SERVER_PROTOCOL] => HTTP/1.1
    [GATEWAY_INTERFACE] => CGI/1.1
    [SERVER_PORT] => 63342
    [SERVER_ADDR] => 127.0.0.1
    [SERVER_NAME] => PhpStorm 8.0.2
    [SERVER_SOFTWARE] => PhpStorm 8.0.2
    [REMOTE_PORT] => 49507
    [REMOTE_ADDR] => 127.0.0.1
    [REQUEST_METHOD] => GET
    [REQUEST_URI] => /educell/index.php
    [FCGI_ROLE] => RESPONDER
    [PHP_SELF] => /educell/index.php
    [REQUEST_TIME_FLOAT] => 1449630670.1362
    [REQUEST_TIME] => 1449630670
)
     * */
}

