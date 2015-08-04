<?php

class filePaths{
    private $paths = array();
    public function __construct(){
        $this->load_config();
        $this->set_constants();
    }
    private function load_config(){
        $config_json = fread(fopen( __DIR__.'/config.json','r'),4*1024);
        $obj = (array)json_decode($config_json);
        $this->paths = (array)$obj['file_paths'];
    }


    private function set_constants()
    {
        foreach ($this->paths as $key => $val) {
            defined($key) ? null : define($key, $val);
        }
    }
}