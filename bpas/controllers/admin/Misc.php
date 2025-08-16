<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Misc extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function barcode($product_code = null, $bcs = 'code128', $height = 40, $text = true, $encoded = false)
    {
        $product_code = $encoded ? $this->bpas->base64url_decode($product_code) : $product_code;
        if ($this->Settings->barcode_img) {
            header('Content-Type: image/png');
        } else {
            header('Content-type: image/svg+xml');
        }
        echo $this->bpas->barcode($product_code, $bcs, $height, $text, false, true);
    }

    public function index()
    {
        show_404();
    }

    public function ex () 
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'AAAAA');
        $writer = new Xlsx($spreadsheet);
        $writer->save('AAAAA.xlsx');
    }

    public function ex2 () 
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'AAAAA');

        $fileName = "AAAAA";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function ex3 () 
    {
        $spreadsheet = new Spreadsheet();
        $this->excel = $spreadsheet;
        $this->excel->getActiveSheet()->setCellValue('A1', 'AAAAA');
        $fileName = "AAAAA";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
    }

    public function ex4 () 
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'AAAAA');
        for ($i=0; $i < 3; $i++) { 
            $writer = new Xlsx($spreadsheet);
            // $writer->save(base_url() . 'assets/uploads/' . 'AAAAA_' . $i . '.xlsx');
            $writer->save('save_files/AAAAA_' . $i . '.xlsx');
        }
    }

    public function ex5 () 
    {
        $this->load->helper('file');
        delete_files(FCPATH . 'temp/', TRUE);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'AAAAA');
        for ($i=0; $i < 3; $i++) { 
            $writer = new Xlsx($spreadsheet);
            $writer->save('temp/file_' . ($i + 1) . '.xlsx');
        }
        $this->load->library('zip');
        $this->zip->read_dir('temp');
        $this->zip->download('Singapore_sales_report_' . date('Y_m_d_H_i_s') .'.zip'); 
    }
}
