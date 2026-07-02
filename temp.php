<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
$_SESSION['admin_logged_in'] = true;
$_GET['export_type'] = ['all'];
$_GET['action'] = 'xlsx';
require 'admin/export_data.php';
