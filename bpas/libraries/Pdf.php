<?php 
	if (!defined('BASEPATH')) exit('No direct script access allowed');  
	 
	require_once 'dompdf/autoload.inc.php';

	use Dompdf\Dompdf;

	class Pdf extends Dompdf
	{
		protected $ci;
		public function __construct()
		{
		   $this->ci =& get_instance();
		} 

		public function generate_pdf($view,$data = array())
		{
			$dompdf= new Dompdf();
			$html = $this->ci->load->view($view,$data,TRUE);
			$dompdf->loadHtml($html);
			$dompdf->setPaper('A4','portail');
			$this->pdf->render();
			$this->pdf->stream("categories.pdf", array("Attachment"=>0));
		}
	}

?>
