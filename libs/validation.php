<?php
class validation {

    function __construct() {
    }
    /*
     * we will need to provide validation for POST array values from forms
     * and GET array values from urls. We will need to use regular expressions
     * to achieve this
     *
     * empty field check
     * field character check
     * field space character check
     * unwanted input value check
     * harmful input value check
     */

    public function email() {
        //email validation
        if (preg_match('/@.+\..+$/', $_POST["email"])) {
            //email is correct
        }
    }

    public function profile() {
        //email validation
        if (preg_match('/@.+\..+$/', $_POST["email"])) {
            //email is correct
        }
    }

}

?>
