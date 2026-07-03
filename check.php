<?php
require 'appconfig.php';
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$res1 = $db->query("SELECT COUNT(*) FROM mst_provinces")->fetch_row()[0];
$res2 = $db->query("SELECT COUNT(*) FROM mst_districts")->fetch_row()[0];
$res3 = $db->query("SELECT COUNT(*) FROM mst_sub_districts")->fetch_row()[0];
echo "Provinces: $res1, Districts: $res2, Sub-districts: $res3\n";

$db->query("DELETE FROM mst_provinces WHERE province_id=0 OR province_code=''");
$db->query("DELETE FROM mst_districts WHERE district_id=0 OR district_code=''");
$db->query("DELETE FROM mst_sub_districts WHERE sub_district_id=0 OR sub_district_code=''");
echo "Cleaned up invalid rows.\n";
