<?php 

require "vendor/autoload.php";

use mikehaertl\wkhtmlto\Image;
use mikehaertl\wkhtmlto\Pdf;

// You can pass a filename, a HTML string or an URL to the constructor
$pdf = new Pdf('<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
</head><body><p>បងស្រលាញ់</p></body></html>');
// On some systems you may have to set the binary path.
$pdf->binary = 'C:\Program Files\wkhtmltopdf\bin\wkhtmltopdf.exe';

if (!$pdf->saveAs('vendor/page.pdf')) {
    echo $pdf->getError();
}