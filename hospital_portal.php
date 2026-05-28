<?php
/**
 * FILE: hospital_portal.php
 * PURPOSE: Internal hospital workspace to manage local triage queues and push diagnostic lab outcomes.
 */
session_start();
require_once('db_connect.php');

$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Initial Self-Registration Logic Block
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $name     = mysqli_real_escape_string($conn, $_POST['name']);
        $address  = mysqli_real_escape_string($conn, $_POST['address']);
        $location = mysqli_real_escape_string($conn, $_POST['location']);

        $sql = "INSERT INTO hospital (hospital_name, address, location_details, functional_status) 
                VALUES ('$name', '$address', '$location', 'PENDING')";
        if (mysqli_query($conn, $sql)) {
            $msg = "SUCCESS: Access application lodged. Waiting for central Administrative sign-off.";
        }
    }

    // 2. Mock Internal Authentication for Core Execution Layout
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $h_id = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        $check = mysqli_query($conn, "SELECT * FROM hospital WHERE id='$h_id' AND functional_status='APPROVED'");
        if (mysqli_fetch_assoc($check)) {
            $_SESSION['hospital_id'] = $h_id;
            header("Location: hospital_portal.php");
            exit;
        } else {
            $msg = "ERROR: Node authentication dropped. Ensure entry validation status is live.";
        }
    }

    // 3. Triage Step Matrix Modification: Accept / Reject Actions
    if (isset($_POST['action']) && $_POST['action'] == 'mutate_status') {
        $app_id = mysqli_real_escape_string($conn, $_POST['app_id']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        mysqli_query($conn, "UPDATE appointment_registry SET request_status='$status' WHERE id='$app_id'");
    }

    // 4. Lab Tracking Mutation: Update Diagnostics Outcome States
    if (isset($_POST['action']) && $_POST['action'] == 'update_result') {
        $app_id = mysqli_real_escape_string($conn, $_POST['app_id']);
        $result = mysqli_real_escape_string($conn, $_POST['result']);
        mysqli_query($conn, "UPDATE appointment_registry SET execution_result='$result' WHERE id='$app_id'");
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: hospital_portal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clinical Operational Board | Hospital Interface</title>
    <style>
        /* The Bright & Shiny Theme Configuration Matrix */
        body { background-color: #f8fafc; color: #1e293b; font-family: -apple-system, BlinkMacSystemFont, Arial, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 1100px; margin: auto; }
        header { background: #fff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 25px;}
        h1 { color: #0f172a; margin: 0; font-size: 1.6em; border-left: 4px solid #00b4d8; padding-left: 10px; }
        .section-card { background: #ffffff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 25px; }
        label { font-weight: bold; font-size: 0.85em; color: #475569; display: block; margin-bottom: 5px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; box-sizing: border-box; background: #fff; margin-bottom: 12px; }
        .btn-action { background: #00b4d8; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; }
        .btn-action:hover { background: #0077b6; }
        table { width: 100%; border-collapse: collapse; background: #fff; font-size: 0.9em; }
        th, td { padding: 10px; border: 1px solid #e2e8f0; text-align: left; }
        th { background: #f1f5f9; color: #334155; }
        .grid-split { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .inline-form { display: inline-block; margin: 0; }
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Clinical Queue Board <span style="font-size:0.55em; color:#00b4d8;">// Bright & Shiny Node</span></h1>
        <?php if(isset($_SESSION['hospital_id'])): ?>
            <div>Node Session Asset Identifier: <strong>#00<?php echo $_SESSION['hospital_id']; ?></strong> | <a href="?logout=1" style="color:#ef4444;">Disconnect System</a></div>
        <?php endif; ?>
    </header>

    <?php if(!empty($msg)) echo "<div style='padding:12px; background:#dbeafe; color:#1e40af; font-weight:bold; margin-bottom:20px; border-radius:4px;'>$msg</div>"; ?>

    <?php if(!isset($_SESSION['hospital_id'])): ?>
        <div class="grid-split">
            <div class="section-card">
                <h2>Clinical Access Authorization</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <label>Select Your Approved Infrastructure Facility Node</label>
                    <select name="hospital_id" required>
                        <option value="">-- Query System Manifest Node --</option>
                        <?php 
                        $res = mysqli_query($conn, "SELECT id, hospital_name FROM hospital WHERE functional_status='APPROVED'");
                        while($r = mysqli_fetch_assoc($res)) echo "<option value='{$r['id']}'>".htmlspecialchars($r['hospital_name'])."</option>";
                        ?>
                    </select>
                    <button type="submit" class="btn-action">Activate Operational Desk</button>
                </form>
            </div>
            <div class="section-card">
                <h2>Onboard New Facility Instance</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <label>Hospital Facility Name</label>
                    <input type="text" name="name" required placeholder="Seattle Grace Hospital">
                    <label>Physical Address</label>
                    <input type="text" name="address" required placeholder="Plaza Pipeline Drive">
                    <label>Geographic Hub Sector Location</label>
                    <input type="text" name="location" required placeholder="King County Zone">
                    <button type="submit" class="btn-action">File Core Network Onboarding Request</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Interactive Triage Tracking Dashboard Matrix View -->
        <div class="section-card">
            <h2>Active Patient Operational Workflow Ledger</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Demographics Profile</th>
                        <th>Target Action Category</th>
                        <th>Target Date</th>
                        <th>Current Queue State</th>
                        <th>Manage Queue Workflow</th>
                        <th>Diagnostic Result State Matrix</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $h_id = $_SESSION['hospital_id'];
                    $queue = mysqli_query($conn, "SELECT a.*, p.full_name, p.mobile_number, p.location_details 
                                                  FROM appointment_registry a 
                                                  JOIN patient p ON a.patient_id = p.id 
                                                  WHERE a.hospital_id = '$h_id' ORDER BY a.id DESC");
                    if(mysqli_num_rows($queue) == 0) {
                        echo "<tr><td colspan='7' style='text-align:center; color:#64748b;'>No active triage metrics found for this network block.</td></tr>";
                    }
                    while($row = mysqli_fetch_assoc($queue)) {
                        echo "<tr>
                                <td>#AR-{$row['id']}</td>
                                <td><strong>".htmlspecialchars($row['full_name'])."</strong><br><small style='color:#64748b;'>Cell: {$row['mobile_number']}</small></td>
                                <td>{$row['appointment_type']}</td>
                                <td>{$row['scheduled_date']}</td>
                                <td><strong>{$row['request_status']}</strong></td>
                                <td>";
                        if($row['request_status'] == 'PENDING') {
                            echo "<form method='POST' class='inline-form' style='margin-right:5px;'>
                                    <input type='hidden' name='action' value='mutate_status'>
                                    <input type='hidden' name='app_id' value='{$row['id']}'>
                                    <input type='hidden' name='status' value='APPROVED'>
                                    <button type='submit' class='btn-action' style='background:#22c55e;'>Approve</button>
                                  </form>";
                            echo "<form method='POST' class='inline-form'>
                                    <input type='hidden' name='action' value='mutate_status'>
                                    <input type='hidden' name='app_id' value='{$row['id']}'>
                                    <input type='hidden' name='status' value='REJECTED'>
                                    <button type='submit' class='btn-action' style='background:#ef4444;'>Reject</button>
                                  </form>";
                        } else {
                            echo "<span style='color:#94a3b8;'>Triage Evaluation Locked</span>";
                        }
                        echo "</td><td>";
                        if($row['request_status'] == 'APPROVED') {
                            echo "<form method='POST'>
                                    <input type='hidden' name='action' value='update_result'>
                                    <input type='hidden' name='app_id' value='{$row['id']}'>
                                    <select name='result' onchange='this.form.submit()' style='margin:0; padding:4px;'>
                                        <option value=''>-- Mutate Outcome --</option>";
                            if($row['appointment_type'] == 'TEST') {
                                echo "<option value='COVID-19 NEGATIVE'>COVID-19 NEGATIVE</option>
                                      <option value='COVID-19 POSITIVE'>COVID-19 POSITIVE</option>";
                            } else {
                                echo "<option value='FIRST DOSE COMPLETED'>FIRST DOSE COMPLETED</option>
                                      <option value='IMMUNIZATION FULLY ARCHIVED'>IMMUNIZATION FULLY ARCHIVED</option>";
                            }
                            echo "  </select>
                                  </form><br>";
                        }
                        echo "<small>Current State: <strong>".htmlspecialchars($row['execution_result'])."</strong></small>";
                        echo "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
