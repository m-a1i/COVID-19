<?php
/**
 * FILE: admin_portal.php
 * PURPOSE: System Administration Control Terminal tracking facilities, inventory states, and logging outputs.
 */
session_start();
require_once('db_connect.php');

// Simple validation pattern for simulation
$_SESSION['admin_authenticated'] = true; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Hospital Node Management Activation Logic
    if (isset($_POST['action']) && $_POST['action'] == 'triage_hospital') {
        $h_id   = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE hospital SET functional_status='$status' WHERE id='$h_id'");
    }

    // 2. Resource Logistics Toggle Configuration Logic
    if (isset($_POST['action']) && $_POST['action'] == 'toggle_vaccine') {
        $v_id   = mysqli_real_escape_string($conn, $_POST['vaccine_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE vaccine_inventory SET current_availability='$status' WHERE id='$v_id'");
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Central Administrative Command Terminal</title>
    <style>
        /* Command Room Hybrid Interface Aesthetics Rules */
        body { background-color: #020617; color: #e2e8f0; font-family: 'Courier New', Courier, monospace; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: auto; }
        header { background: #0f172a; border-bottom: 2px solid #38bdf8; padding: 15px; margin-bottom: 30px; }
        h1, h2 { margin: 0; color: #38bdf8; font-weight: normal; }
        .grid-matrix { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .panel-card { background: #0f172a; border: 1px solid #1e293b; padding: 20px; border-radius: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 0.85em; background: #020617; }
        th, td { padding: 8px; border: 1px solid #1e293b; text-align: left; }
        th { background: #1e293b; color: #38bdf8; }
        .btn-control { background: #334155; color: white; border: 1px solid #475569; padding: 4px 8px; cursor: pointer; font-family: monospace; }
        .btn-control:hover { background: #0284c7; }
        .export-box input { padding: 6px; background: #020617; border: 1px solid #1e293b; color: white; font-family: monospace; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>SYSTEM COMMAND RUNTIME ENGINE <span style="font-size:0.6em; color:#64748b;">// Authorized Personnel Context</span></h1>
    </header>

    <!-- Analytical Export Component Grid Link -->
    <div class="panel-card" style="margin-bottom:25px;">
        <h2>Date-Bound Relational Data Extraction (XLS Streaming Engine)</h2>
        <p style="color:#64748b; font-size:0.85em;">Queries target bounds and triggers immediate content header transfer streams to generate Excel reports cleanly.</p>
        <form method="GET" action="admin_export_xls.php" class="export-box">
            <label>Start Boundary Date:</label>
            <input type="date" name="start" required value="<?php echo date('Y-m-01'); ?>">
            <label style="margin-left:15px;">End Boundary Date:</label>
            <input type="date" name="end" required value="<?php echo date('Y-m-t'); ?>">
            <button type="submit" class="btn-control" style="margin-left:15px; background:#0284c7;">Execute Data Extraction Sequence</button>
        </form>
    </div>

    <div class="grid-matrix">
        <!-- Hospital Clearance Verification Processing Panel -->
        <div class="panel-card">
            <h2>Hospital Facility Node Validation Queue</h2>
            <table>
                <thead>
                    <tr>
                        <th>Node</th>
                        <th>Location Details</th>
                        <th>State</th>
                        <th>Authorize Track Command</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $h_res = mysqli_query($conn, "SELECT * FROM hospital ORDER BY id DESC");
                    while($r = mysqli_fetch_assoc($h_res)) {
                        echo "<tr>
                                <td><strong>".htmlspecialchars($r['hospital_name'])."</strong></td>
                                <td>".htmlspecialchars($r['location_details'])."</td>
                                <td>{$r['functional_status']}</td>
                                <td>";
                        if($r['functional_status'] == 'PENDING') {
                            echo "<form method='POST' style='display:inline;'>
                                    <input type='hidden' name='action' value='triage_hospital'>
                                    <input type='hidden' name='hospital_id' value='{$r['id']}'>
                                    <input type='hidden' name='status' value='APPROVED'>
                                    <button type='submit' class='btn-control' style='color:#4ade80;'>Approve</button>
                                  </form>
                                  <form method='POST' style='display:inline; margin-left:5px;'>
                                    <input type='hidden' name='action' value='triage_hospital'>
                                    <input type='hidden' name='hospital_id' value='{$r['id']}'>
                                    <input type='hidden' name='status' value='REJECTED'>
                                    <button type='submit' class='btn-control' style='color:#f87171;'>Reject</button>
                                  </form>";
                        } else {
                            echo "<span style='color:#64748b;'>Locked</span>";
                        }
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- System Biological Asset Logistical Track Controller -->
        <div class="panel-card">
            <h2>Biological Vaccine Lots Allocation Map</h2>
            <table>
                <thead>
                    <tr>
                        <th>Vaccine Sub-Lot Asset Class Name</th>
                        <th>Logistics Availability Flag State</th>
                        <th>Toggle Distribution Configuration</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $v_res = mysqli_query($conn, "SELECT * FROM vaccine_inventory ORDER BY id ASC");
                    while($r = mysqli_fetch_assoc($v_res)) {
                        $status_str = $r['current_availability'] == 1 ? "AVAILABLE" : "UNAVAILABLE";
                        $toggle_val = $r['current_availability'] == 1 ? 0 : 1;
                        $btn_text   = $r['current_availability'] == 1 ? "Flag Inactive" : "Flag Active";
                        echo "<tr>
                                <td><strong>".htmlspecialchars($r['vaccine_name'])."</strong></td>
                                <td>$status_str</td>
                                <td>
                                    <form method='POST'>
                                        <input type='hidden' name='action' value='toggle_vaccine'>
                                        <input type='hidden' name='vaccine_id' value='{$r['id']}'>
                                        <input type='hidden' name='status' value='$toggle_val'>
                                        <button type='submit' class='btn-control'>$btn_text</button>
                                    </form>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
