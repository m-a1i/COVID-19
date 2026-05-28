<?php
/**
 * FILE: admin_export_xls.php
 * PURPOSE: Tabular parsing engine utilizing standard tab-delimited spacing formats.
 * HARDWARE STRATEGY: Streams data dynamically row-by-row, ensuring stability under 128MB RAM configurations.
 */
session_start();
require_once('db_connect.php');

// Security Access Verification
if (!isset($_SESSION['admin_authenticated']) || $_SESSION['admin_authenticated'] !== true) {
    die("CRITICAL RUNTIME ERROR: Session credentials validation failure.");
}

// Intercept form filter data parameters
$start_date = mysqli_real_escape_string($conn, $_GET['start']);
$end_date   = mysqli_real_escape_string($conn, $_GET['end']);

// Compile multi-table query join map
$query = "SELECT p.full_name, p.mobile_number, a.appointment_type, a.scheduled_date, a.execution_result 
          FROM appointment_registry a
          INNER JOIN patient p ON a.patient_id = p.id
          WHERE a.scheduled_date BETWEEN '$start_date' AND '$end_date'
          ORDER BY a.scheduled_date ASC";

$result = mysqli_query($conn, $query);
if (!$result) {
    die("DATABASE SELECTION ERROR: Execution loop aborted.");
}

// Define the precise targeted binary output header fields
$filename = "COVID_DATA_METRIC_REPORT_" . $start_date . "_TO_" . $end_date . ".xls";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

// Render out explicit tabular column spacing maps
echo "Patient Name\tMobile Tracking ID\tService Component Track\tTarget Evaluation Date\tClinical Outcome Status\n";

// Stream records one by one directly onto system transport outputs
while ($row = mysqli_fetch_assoc($result)) {
    echo htmlspecialchars($row['full_name']) . "\t";
    echo htmlspecialchars($row['mobile_number']) . "\t";
    echo $row['appointment_type'] . "\t";
    echo $row['scheduled_date'] . "\t";
    echo htmlspecialchars($row['execution_result']) . "\n";
}

// Clean connection references and terminate the thread
mysqli_free_result($result);
mysqli_close($conn);
exit;
?>
