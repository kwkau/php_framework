<?php
   namespace Oracle;

class Oracle {

    /**
     * @var resource The connection resource
     * @access protected
     */
    protected $conn = null;
    /**
     * @var resource The statement resource identifier
     * @access protected
     */
    protected $stid = null;
    /**
     * @var integer The number of rows to prefetch with queries
     * @access protected
     */
    protected $prefetch = 100;

    /**
     * Constructor opens a connection to the database
     * @param string $module Module text for End-to-End Application Tracing
     * @param string $cid Client Identifier for End-to-End Application Tracing
     * @throws \Exception
     */
    public function __construct($module,$cid){
        $this->conn = @oci_new_connect(O_USERNAME,O_PASSWORD,O_DATABASE,O_CHARSET);
        if(!$this->conn){
            $m = oci_error();
            throw new \Exception("Cannot open connection to Oracle db ".$m["message"]);
        }

        // Record the "name" of the web user, the client info and the module.
        // These are used for end-to-end tracing in the DB.
        oci_set_client_info($this->conn, CLIENT_INFO);
        oci_set_module_name($this->conn, $module);
        oci_set_client_identifier($this->conn, $cid);
    }


    /**
     * Destructor closes the statement and connection
     */
    function __destruct() {
        if ($this->stid)
            oci_free_statement($this->stid);
        if ($this->conn)
            oci_close($this->conn);
    }

    /*---------------------------
     * Database CRUD functions
     *-------------------------*/

    /**
     * @param $query
     * @param $params
     * @param $action
     */
    public function execute($query, $action,$params=array())
    {
        $this->stid = oci_parse($this->conn, $query);
        if ($this->prefetch >= 0) {
            oci_set_prefetch($this->stid, $this->prefetch);
        }
        foreach ($params as $bv) {
            oci_bind_by_name($this->stid, $bv[0], $bv[1], $bv[2]);
        }
        oci_set_action($this->conn, $action);
        oci_execute($this->stid);
    }

    public function o_fetch_all($query, $action,$params = array(), $f_mode)
    {
        $result = array();
        $this->execute($query,$action,$params);

        $result["count"] = oci_num_fields($this->stid);
        oci_fetch_all($this->stid, $res, 0, -1, $f_mode);
        $result["data"] = $res;
        $this->stid = null;
        return $result;
    }

    public function o_fetch_assoc($query, $action, $params = array())
    {
        $result = array();
        $this->execute($query,$action,$params);

        $result["count"] = oci_num_rows($this->stid);
        while($row = oci_fetch_assoc($this->stid)){
            $result["data"][] = $row;
        }
        return $result;
    }

}