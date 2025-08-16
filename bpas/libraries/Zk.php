<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . "/third_party/zklib/zklib.php";

class Zk extends ZKLib
{
    public function __construct()
    {
        parent::__construct();
    }
}
