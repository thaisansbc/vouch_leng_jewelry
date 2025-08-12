<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once 'vendor/autoload.php';
require_once 'vendor/phpoffice/phpspreadsheet/src/PhpSpreadsheet/Spreadsheet.php';
use PhpOffice\phpspreadsheet\Spreadsheet;
use PhpOffice\phpspreadsheet\Writer\Xlsx;

class PhpSpreadsheet extends Spreadsheet
{
    public function __construct()
    {
        parent::__construct();
    }
}
?>