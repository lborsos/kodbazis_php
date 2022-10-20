<?php
require('fpdf.php');

$pdf = new fpdf('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);

$pdf->cell(130,5,'',1,1);

$pdf->Rect(20, 20, 10, 10);

$pdf->Output();

?>