<?php
require_once('../../vendor/autoload.php');
require_once('../../includes/db_config.php');  // Adjust the path as necessary
$dbHandler = new DatabaseHandler();
$conn = $dbHandler->getConnection(); // This returns mysqli connection

// Fetch medical records from the database
// (Ensure your database connection and querying are correctly set up)
$sql = "SELECT * FROM medical_records";
$result = mysqli_query($conn, $sql);
$records = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Initialize TCPDF
$pdf = new TCPDF();
$pdf->AddPage();

// Set document title
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Medical Records Report', 0, 1, 'C');

// Add headers
$pdf->SetFont('Helvetica', 'B', 12);
$pdf->Cell(30, 10, 'Record ID', 1);
$pdf->Cell(50, 10, 'Patient Name', 1);
$pdf->Cell(40, 10, 'Diagnosis', 1);
$pdf->Cell(40, 10, 'Treatment Plan', 1);
$pdf->Cell(30, 10, 'Date', 1);
$pdf->Ln();

// Add data rows
$pdf->SetFont('Helvetica', '', 10);
foreach ($records as $record) {
    $pdf->Cell(30, 10, $record['record_id'], 1);
    $pdf->Cell(50, 10, $record['patient_name'], 1);
    $pdf->Cell(40, 10, $record['diagnosis'], 1);
    $pdf->Cell(40, 10, $record['treatment_plan'], 1);
    $pdf->Cell(30, 10, $record['record_date'], 1);
    $pdf->Ln();
}

// Output PDF to browser
$pdf->Output('medical_report.pdf', 'I');
?>
