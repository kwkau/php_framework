<?php
/**
 * Created by PhpStorm.
 * User: kwaku
 * Date: 8/4/2015
 * Time: 2:56 PM
 */

class home extends Controller{
    public function __construct()
    {
        parent::__construct("home");
    }

    public function index()
    {
        $this->viewBag["Title"] = "Testing App";

        $this->view("Layout");
    }
}