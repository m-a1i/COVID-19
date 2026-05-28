<?php
/**
 * FILE: patient_portal.php
 * PURPOSE: Patient interface dashboard allowing users to register, book operations, and check states.
 */
session_start();
require_once('db_connect.php');

$error_msg = "";
$success_msg = "";

// Track Action Form Submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Handling Registrations
    if (isset($_POST['action']) && $_POST['action'] == 'register') {
        $mobile    = mysqli_real_escape_string($conn, $_POST['mobile']);
        $name      = mysqli_real_escape_string($conn, $_POST['name']);
        $password  = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $address   = mysqli_real_escape_string($conn, $_POST['address']);
        $location  = mysqli_real_escape_string($conn, $_POST['location']);

        // Uniqueness validation
        $check = mysqli_query($conn, "SELECT id FROM patient WHERE mobile_number='$mobile'");
        if (mysqli_num_rows($check) > 0) {
            $error_msg = "Registration Error: This phone indexing already exists.";
        } else {
            $sql = "INSERT INTO patient (mobile_number, full_name, password_hash, physical_address, location_details) 
                    VALUES ('$mobile', '$name', '$password', '$address', '$location')";
            if (mysqli_query($conn, $sql)) {
                $success_msg = "Account created. Please login below.";
            } else {
                $error_msg = "Structural Database failure encountered.";
            }
        }
    }

    // 2. Handling Authentication Login Check
    if (isset($_POST['action']) && $_POST['action'] == 'login') {
        $mobile   = mysqli_real_escape_string($conn, $_POST['mobile']);
        $password = $_POST['password'];

        $res = mysqli_query($conn, "SELECT * FROM patient WHERE mobile_number='$mobile'");
        if ($row = mysqli_fetch_assoc($res)) {
            if (password_verify($password, $row['password_hash'])) {
                $_SESSION['patient_id'] = $row['id'];
                $_SESSION['patient_name'] = $row['full_name'];
                header("Location: patient_portal.php");
                exit;
            }
        }
        $error_msg = "Authentication rejected: Invalid access parameters.";
    }

    // 3. Handling Scheduling Appointments
    if (isset($_POST['action']) && $_POST['action'] == 'book') {
        if (!isset($_SESSION['patient_id'])) die("Session expired context anomaly.");
        $p_id   = $_SESSION['patient_id'];
        $h_id   = mysqli_real_escape_string($conn, $_POST['hospital_id']);
        $type   = mysqli_real_escape_string($conn, $_POST['type']);
        $date   = mysqli_real_escape_string($conn, $_POST['date']);

        $sql = "INSERT INTO appointment_registry (patient_id, hospital_id, appointment_type, scheduled_date) 
                VALUES ('$p_id', '$h_id', '$type', '$date')";
        if (mysqli_query($conn, $sql)) {
            $success_msg = "Clinical application filed for scheduling triage.";
        } else {
            $error_msg = "Failed to complete appointment booking request routing.";
        }
    }
}

// Logout Request Handling Method
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: patient_portal.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Patient Portal | Online Registration System</title>
    <style>
        /* The Dark and Twisty Aesthetics Theme Engine Settings */
        body { background-color: #090d16; color: #cbd5e1; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: auto; }
        header { border-bottom: 1px solid #1e293b; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;}
        h1, h2 { color: #38bdf8; font-weight: 300; letter-spacing: -0.5px; }
        .auth-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 20px; }
        .glass-panel { background: rgba(30, 41, 59, 0.4); border: 1px solid #334155; padding: 25px; border-radius: 8px; box-shadow: 0 4px 30px rgba(0, 0, 0, 0.5); backdrop-filter: blur(5px); }
        label { display: block; margin-bottom: 6px; font-size: 0.9em; color: #94a3b8; }
        input, select, textarea { width: 100%; padding: 10px; margin-bottom: 15px; background: #0f172a; border: 1px solid #334155; color: #fff; border-radius: 4px; box-sizing: border-box;}
        button { background: #0284c7; color: white; border: none; padding: 12px 20px; border-radius: 4px; font-weight: bold; cursor: pointer; transition: background 0.2s; width: 100%;}
        button:hover { background: #0369a1; }
        .alert { padding: 12px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; }
        .alert-success { background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; color: #4ade80; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; background: #0f172a; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #334155; }
        th { background: #1e293b; color: #38bdf8; }
        .badge { padding: 4px 8px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
        .badge-PENDING { background: #7c2d12; color: #fdba74; }
        .badge-APPROVED { background: #064e3b; color: #6ee7b7; }
        .badge-REJECTED { background: #7f1d1d; color: #fca5a5; }
        .nav-link { color: #f43f5e; text-decoration: none; font-weight: bold;}
    </style>
</head>
<body>
<div class="container">
    <header>
        <h1>Patient Workspace <span style="font-size:0.5em; color:#64748b;">// Dark & Twisty Mode</span></h1>
        <?php if(isset($_SESSION['patient_id'])): ?>
            <div>Logged in: <strong><?php echo htmlspecialchars($_SESSION['patient_name']); ?></strong> | <a class="nav-link" href="?logout=1">Disconnect</a></div>
        <?php endif; ?>
    </header>

    <?php if(!empty($error_msg)) echo "<div class='alert alert-error'>$error_msg</div>"; ?>
    <?php if(!empty($success_msg)) echo "<div class='alert alert-success'>$success_msg</div>"; ?>

    <?php if(!isset($_SESSION['patient_id'])): ?>
        <!-- Auth View Panel Entry Interface Split -->
        <div class="auth-grid">
            <div class="glass-panel">
                <h2>Access Portal Authentication</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <label>Mobile Contact Index</label>
                    <input type="text" name="mobile" required placeholder="Enter mobile number">
                    <label>Security Password</label>
                    <input type="password" name="password" required placeholder="••••••••">
                    <button type="submit">Authenticate Session</button>
                </form>
            </div>
            <div class="glass-panel">
                <h2>Initialize Account Registration</h2>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <label>Full Name</label>
                    <input type="text" name="name" required placeholder="Meredith Grey">
                    <label>Mobile Number (Primary System ID)</label>
                    <input type="text" name="mobile" required placeholder="0712345678">
                    <label>Account Password</label>
                    <input type="password" name="password" required placeholder="Secure Passphrase">
                    <label>Physical Home Address</label>
                    <textarea name="address" required placeholder="Seattle, WA..."></textarea>
                    <label>Geographic Sub-Location Context</label>
                    <input type="text" name="location" required placeholder="King County">
                    <button type="submit">Complete System Registration</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <!-- Dynamic Patient Application Engine Panel View -->
        <div class="glass-panel" style="margin-bottom:30px;">
            <h2>File New Appointment Request</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="book">
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:15px;">
                    <div>
                        <label>Target Medical Facility Node</label>
                        <select name="hospital_id" required>
                            <option value="">-- Select Active Facility Node --</option>
                            <?php 
                            $h_res = mysqli_query($conn, "SELECT id, hospital_name, location_details FROM hospital WHERE functional_status='APPROVED'");
                            while($h = mysqli_fetch_assoc($h_res)) {
                                echo "<option value='{$h['id']}'>".htmlspecialchars($h['hospital_name'])." ({$h['location_details']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div>
                        <label>Clinical Action Pathway</label>
                        <select name="type" required>
                            <option value="TEST">COVID-19 Diagnostic Screening Test</option>
                            <option value="VACCINATION">Immunization Vaccine Injection Lot</option>
                        </select>
                    </div>
                    <div>
                        <label>Target Execution Date</label>
                        <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <button type="submit">Authorize and Request Booking Slot</button>
            </form>
        </div>

        <div class="glass-panel">
            <h2>Personal Ledger History & Diagnostic Clinical Outcomes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Facility Entity</th>
                        <th>Clinical Track</th>
                        <th>Target Date</th>
                        <th>Triage Allocation Status</th>
                        <th>Laboratory / Vaccine Outcome</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $p_id = $_SESSION['patient_id'];
                    $ledger = mysqli_query($conn, "SELECT a.*, h.hospital_name FROM appointment_registry a 
                                                   JOIN hospital h ON a.hospital_id = h.id 
                                                   WHERE a.patient_id = '$p_id' ORDER BY a.id DESC");
                    if(mysqli_num_rows($ledger) == 0) {
                        echo "<tr><td colspan='6' style='text-align:center; color:#64748b;'>No medical history tracked within this ledger instance.</td></tr>";
                    }
                    while($row = mysqli_fetch_assoc($ledger)) {
                        echo "<tr>
                                <td>#00{$row['id']}</td>
                                <td>".htmlspecialchars($row['hospital_name'])."</td>
                                <td>{$row['appointment_type']}</td>
                                <td>{$row['scheduled_date']}</td>
                                <td><span class='badge badge-{$row['request_status']}'>{$row['request_status']}</span></td>
                                <td><strong>".htmlspecialchars($row['execution_result'])."</strong></td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
