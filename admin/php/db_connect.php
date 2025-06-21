<?php
date_default_timezone_set('Asia/Kuala_Lumpur');

// First DB: Dynamic company DB
$companyDB = mysqli_connect(
    "srv597.hstgr.io",
    "u664110560_tsp3g_portal",
    "@Sync5500",
    "u664110560_tsp3g_portal"
);

// Second DB: chickenweigher
$tsp3gDB = mysqli_connect(
    "srv597.hstgr.io",
    "u664110560_tsp3g",
    "Aa@111222333",
    "u664110560_tsp3g"
);

// Third DB: dglink
$ps3gDB = mysqli_connect(
    "srv597.hstgr.io",
    "u664110560_tsp3g_demo",
    "Aa@111222333",
    "u664110560_tsp3g_demo"
);

// Check connections
if (mysqli_connect_errno()) {
    echo 'Database connection failed: ' . mysqli_connect_error();
    die();
}

// Optionally, verify each connection individually:
if (!$companyDB) die("Failed to connect to company DB: " . mysqli_connect_error());
if (!$tsp3gDB) die("Failed to connect to tsp3g DB: " . mysqli_connect_error());
if (!$ps3gDB) die("Failed to connect to ps3g DB: " . mysqli_connect_error());
