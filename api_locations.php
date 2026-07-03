<?php
header('Content-Type: application/json; charset=utf-8');
require 'appconfig.php';

$type = $_GET['type'] ?? '';

$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($db->connect_errno) {
    echo json_encode([]);
    exit;
}
$db->set_charset("utf8mb4");

$data = [];

if ($type === 'provinces') {
    $res = $db->query("SELECT province_id AS PROVINCE_ID, province_code AS PROVINCE_CODE, province_thai AS PROVINCE_THAI, province_english AS PROVINCE_ENGLISH, region_id AS REGION_ID FROM mst_provinces ORDER BY province_thai ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $data[] = [
                'PROVINCE_ID' => (int)$r['PROVINCE_ID'],
                'PROVINCE_CODE' => $r['PROVINCE_CODE'],
                'PROVINCE_THAI' => $r['PROVINCE_THAI'],
                'PROVINCE_ENGLISH' => $r['PROVINCE_ENGLISH'],
                'REGION_ID' => (int)$r['REGION_ID']
            ];
        }
    }
} elseif ($type === 'districts') {
    $res = $db->query("SELECT district_id AS DISTRICT_ID, province_id AS PROVINCE_ID, district_code AS DISTRICT_CODE, district_thai AS DISTRICT_THAI, district_english AS DISTRICT_ENGLISH FROM mst_districts ORDER BY district_thai ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $data[] = [
                'DISTRICT_ID' => (int)$r['DISTRICT_ID'],
                'PROVINCE_ID' => (int)$r['PROVINCE_ID'],
                'DISTRICT_CODE' => $r['DISTRICT_CODE'],
                'DISTRICT_THAI' => $r['DISTRICT_THAI'],
                'DISTRICT_ENGLISH' => $r['DISTRICT_ENGLISH']
            ];
        }
    }
} elseif ($type === 'sub_districts') {
    $res = $db->query("SELECT sub_district_id AS SUB_DISTRICT_ID, district_id AS DISTRICT_ID, sub_district_code AS SUB_DISTRICT_CODE, sub_district_thai AS SUB_DISTRICT_THAI, sub_district_english AS SUB_DISTRICT_ENGLISH, postal_code AS POSTAL_CODE, latitude AS LATITUDE, longitude AS LONGITUDE FROM mst_sub_districts ORDER BY sub_district_thai ASC");
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $data[] = [
                'SUB_DISTRICT_ID' => (int)$r['SUB_DISTRICT_ID'],
                'DISTRICT_ID' => (int)$r['DISTRICT_ID'],
                'SUB_DISTRICT_CODE' => $r['SUB_DISTRICT_CODE'],
                'SUB_DISTRICT_THAI' => $r['SUB_DISTRICT_THAI'],
                'SUB_DISTRICT_ENGLISH' => $r['SUB_DISTRICT_ENGLISH'],
                'POSTAL_CODE' => $r['POSTAL_CODE'] ? (int)$r['POSTAL_CODE'] : null,
                'LATITUDE' => (float)$r['LATITUDE'],
                'LONGITUDE' => (float)$r['LONGITUDE']
            ];
        }
    }
}

echo json_encode([$type => $data], JSON_UNESCAPED_UNICODE);
$db->close();
