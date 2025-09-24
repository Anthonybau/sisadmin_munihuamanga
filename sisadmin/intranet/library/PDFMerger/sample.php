<?php
include 'PDFMerger.php';
include 'setasign/fpdi/src/autoload.php';
include 'setasign/fpdf/fpdf.php';

$pdf = new PDFMerger\PDFMerger;

$pdf->addPDF('samplepdfs/one.pdf', 'all')
	->addPDF('samplepdfs/two.pdf', 'all')
	->addPDF('samplepdfs/three.pdf', 'all')
	->merge('file', 'samplepdfs/TEST2.pdf');
	
	//REPLACE 'file' WITH 'browser', 'download', 'string', or 'file' for output options
	//You do not need to give a file path for browser, string, or download - just the name.
