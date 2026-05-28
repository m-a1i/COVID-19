#!/usr/bin/perl
# ==========================================================================
# FILE: check_inventory.pl
# PURPOSE: Automated backend script checking availability balances.
# COMPLIANCE STANDARD: Configured for cron deployments on legacy server kernels.
# ==========================================================================

use strict;
use warnings;
use DBI;

# Database configuration settings
my $dsn      = "DBI:mysql:database=ors_covid_db;host=localhost";
use_username = "root";
my $password = "";

# Connect to database using standard error validation controls
my $dbh = DBI->connect($dsn, $user_username, $password, { RaiseError => 1, AutoCommit => 1 })
    or die "CRITICAL SERVER ERR: Failed to hook database connection context: " . DBI->errstr;

# Prepare statement to pull items marked as unavailable
my $sth = $dbh->prepare("SELECT vaccine_name FROM vaccine_inventory WHERE current_availability = 0");
$sth->execute();

print "=== SYSTEM ENGINE ALERT LOG: RUNNING INVENTORY DISPATCH BALANCES SCAN ===\n";

my $depleted_count = 0;

while (my @row = $sth->fetchrow_array()) {
    print "LOGISTICS DEPLETED WARNING: Resource Pool Item [" . $row[0] . "] flagged as OUT OF STOCK.\n";
    $depleted_count++;
}

if ($depleted_count == 0) {
    print "LOGISTICS SYSTEM NORMAL: All critical vaccine configurations are active in inventory pools.\n";
}

print "=== SCAN TERMINATED SUCCESSFULLY ===\n";

$sth->finish();
$dbh->disconnect();
exit;
