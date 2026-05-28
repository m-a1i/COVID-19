<?php
/**
 * FILE: db_connect.php
 * PURPOSE: Secure, low-overhead database pipeline establishment.
 * HARDWARE STANDARDS: Optimized for minimal operational connection pooling.
 */

define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Replace with production DB username
define('DB_PASSWORD', '');     // Replace with production DB password
define('DB_NAME', 'ors_covid_db');

// Instantiate raw connection block
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Intercept pipeline structural failures to shield database layer
if (!$conn) {
    die("CRITICAL ACCESS ERRORED: Database connection matrix severed: " . mysqli_connect_error());
}

// Enforce clean string rendering mapping
mysqli_set_charset($conn, "utf8");
?>
