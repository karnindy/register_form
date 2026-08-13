<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require 'appconfig.php';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->set_charset("utf8mb4");

echo "Creating tables...\n";
$sql_prov = "CREATE TABLE IF NOT EXISTS `mst_provinces` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `province_id` int(11) NOT NULL,
  `province_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_thai` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_english` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `region_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `province_id` (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$sql_dist = "CREATE TABLE IF NOT EXISTS `mst_districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `district_id` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `district_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_thai` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_english` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `district_id` (`district_id`),
  KEY `province_id` (`province_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$sql_sub = "CREATE TABLE IF NOT EXISTS `mst_sub_districts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sub_district_id` int(11) NOT NULL,
  `district_id` int(11) NOT NULL,
  `sub_district_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_district_thai` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_district_english` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `latitude` float DEFAULT NULL,
  `longitude` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sub_district_id` (`sub_district_id`),
  KEY `district_id` (`district_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$db->query($sql_prov);
$db->query($sql_dist);
$db->query($sql_sub);

echo "Importing provinces...\n";
$provinces_json = file_get_contents('provinces.json');
if ($provinces_json) {
    $provinces = json_decode($provinces_json, true);
    $stmt = $db->prepare("INSERT IGNORE INTO mst_provinces (province_id, province_code, province_thai, province_english, region_id) VALUES (?, ?, ?, ?, ?)");
    foreach ($provinces as $p) {
        $stmt->bind_param("isssi", $p['PROVINCE_ID'], $p['PROVINCE_CODE'], $p['PROVINCE_THAI'], $p['PROVINCE_ENGLISH'], $p['REGION_ID']);
        $stmt->execute();
    }
    $stmt->close();
}

echo "Importing districts...\n";
$districts_json = file_get_contents('districts.json');
if ($districts_json) {
    $districts = json_decode($districts_json, true);
    $stmt = $db->prepare("INSERT IGNORE INTO mst_districts (district_id, province_id, district_code, district_thai, district_english) VALUES (?, ?, ?, ?, ?)");
    foreach ($districts as $d) {
        $stmt->bind_param("iisss", $d['DISTRICT_ID'], $d['PROVINCE_ID'], $d['DISTRICT_CODE'], $d['DISTRICT_THAI'], $d['DISTRICT_ENGLISH']);
        $stmt->execute();
    }
    $stmt->close();
}

echo "Importing sub-districts...\n";
$sub_districts_json = file_get_contents('sub_districts.json');
if ($sub_districts_json) {
    $sub_districts = json_decode($sub_districts_json, true);
    $stmt = $db->prepare("INSERT IGNORE INTO mst_sub_districts (sub_district_id, district_id, sub_district_code, sub_district_thai, sub_district_english, postal_code, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($sub_districts as $sd) {
        $pc = isset($sd['POSTAL_CODE']) ? (string)$sd['POSTAL_CODE'] : null;
        $lat = isset($sd['LATITUDE']) ? (float)$sd['LATITUDE'] : null;
        $lon = isset($sd['LONGITUDE']) ? (float)$sd['LONGITUDE'] : null;
        $stmt->bind_param("iissssdd", $sd['SUB_DISTRICT_ID'], $sd['DISTRICT_ID'], $sd['SUB_DISTRICT_CODE'], $sd['SUB_DISTRICT_THAI'], $sd['SUB_DISTRICT_ENGLISH'], $pc, $lat, $lon);
        $stmt->execute();
    }
    $stmt->close();
}

echo "Migration complete!\n";
$db->close();
