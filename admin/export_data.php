<?php
require_once 'includes/auth.php';

if (isset($_GET['search_person'])) {
    header('Content-Type: application/json; charset=utf-8');
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) { echo json_encode([]); exit; }
    $db->set_charset("utf8mb4");
    $table = defined('DB_TABLE_REGISTER') ? DB_TABLE_REGISTER : 'register';
    
    $term = $db->real_escape_string($_GET['search_person']);
    
    $query = "SELECT r.id, r.first_name_th, r.last_name_th, r.national_id, r.license_no, r.course_type 
              FROM {$table} r
              INNER JOIN (
                  SELECT MAX(t1.id) AS id
                  FROM {$table} t1
                  INNER JOIN (
                      SELECT national_id, MAX(updated_at) AS max_updated
                      FROM {$table}
                      WHERE confirmed = 'ยืนยันการสมัคร'
                      GROUP BY national_id
                  ) t2 ON t1.national_id = t2.national_id AND t1.updated_at <=> t2.max_updated
                  WHERE t1.confirmed = 'ยืนยันการสมัคร'
                  GROUP BY t1.national_id
              ) ru ON r.id = ru.id
              WHERE r.first_name_th LIKE '%$term%' OR r.last_name_th LIKE '%$term%' OR r.national_id LIKE '%$term%' OR r.license_no LIKE '%$term%'
              LIMIT 20";
    
    $res = $db->query($query);
    $results = [];
    if ($res) {
        while($row = $res->fetch_assoc()) {
            $results[] = $row;
        }
    }
    echo json_encode($results);
    exit;
}

if (isset($_GET['fetch_dates'])) {
    header('Content-Type: application/json; charset=utf-8');
    $col = $_GET['fetch_dates'];
    $allowed = ['renew_broker_1', 'renew_broker_2', 'renew_broker_3', 'renew_agent_1', 'renew_agent_2', 'renew_agent_3', 'นว.4', 'ตว.4'];
    if (!in_array($col, $allowed)) {
        echo json_encode([]);
        exit;
    }
    
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) {
        echo json_encode([]);
        exit;
    }
    $db->set_charset("utf8mb4");
    $table = defined('DB_TABLE_REGISTER') ? DB_TABLE_REGISTER : 'register';
    
    $all_dates = [];
    
    if ($col === 'นว.4' || $col === 'ตว.4') {
        $course_filter = ($col === 'นว.4') ? "นายหน้า" : "ตัวแทน";
        $course_filter = $db->real_escape_string($course_filter);
        $query = "SELECT renew_other FROM {$table} WHERE renew_other IS NOT NULL AND renew_other != '' AND course_type LIKE '%{$course_filter}%'";
        $result = $db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $text = $row['renew_other'];
                preg_match_all('/\[\s*(\d{1,2})\s+([ก-๙]+)\s+(\d{4})\s*\]/u', $text, $matches, PREG_SET_ORDER);
                if (!empty($matches)) {
                    foreach ($matches as $m) {
                        $normalized_date = $m[1] . ' ' . $m[2] . ' ' . $m[3];
                        $all_dates[] = $normalized_date;
                    }
                }
            }
        }
    } else {
        $col = $db->real_escape_string($col);
        $query = "SELECT DISTINCT `{$col}` FROM {$table} WHERE `{$col}` IS NOT NULL AND `{$col}` != ''";
        $result = $db->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $d = trim($row[$col]);
                if (!empty($d)) {
                    $all_dates[] = $d;
                }
            }
        }
    }
    
    $all_dates = array_unique($all_dates);
    
    $thai_months = [
        'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4,
        'พฤษภาคม' => 5, 'มิถุนายน' => 6, 'กรกฎาคม' => 7, 'สิงหาคม' => 8,
        'กันยายน' => 9, 'ตุลาคม' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12
    ];
    usort($all_dates, function($a, $b) use ($thai_months) {
        preg_match('/(\d{1,2})\s+([ก-๙]+)\s+(\d{4})/', $a, $ma);
        preg_match('/(\d{1,2})\s+([ก-๙]+)\s+(\d{4})/', $b, $mb);
        $ya = isset($ma[3]) ? (int)$ma[3] : 0;
        $yb = isset($mb[3]) ? (int)$mb[3] : 0;
        if ($ya !== $yb) return $ya - $yb;
        
        $ma_m = isset($ma[2]) ? ($thai_months[$ma[2]] ?? 0) : 0;
        $mb_m = isset($mb[2]) ? ($thai_months[$mb[2]] ?? 0) : 0;
        if ($ma_m !== $mb_m) return $ma_m - $mb_m;
        
        $da = isset($ma[1]) ? (int)$ma[1] : 0;
        $db = isset($mb[1]) ? (int)$mb[1] : 0;
        return $da - $db;
    });
    
    echo json_encode(array_values($all_dates));
    exit;
}

if (isset($_GET['fetch_tree'])) {
    header('Content-Type: application/json; charset=utf-8');
    $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($db->connect_errno) { echo json_encode([]); exit; }
    $db->set_charset("utf8mb4");
    $table = defined('DB_TABLE_REGISTER') ? DB_TABLE_REGISTER : 'register';
    
    $levels = [
        'นว.4' => 'นว.4', 'ตว.4' => 'ตว.4',
        'นว.3' => 'renew_broker_3', 'นว.2' => 'renew_broker_2', 'นว.1' => 'renew_broker_1', 'นว.0' => 'renew_broker_1',
        'ตว.3' => 'renew_agent_3', 'ตว.2' => 'renew_agent_2', 'ตว.1' => 'renew_agent_1', 'ตว.0' => 'renew_agent_1'
    ];
    
    $thai_months = [
        'มกราคม' => 1, 'กุมภาพันธ์' => 2, 'มีนาคม' => 3, 'เมษายน' => 4,
        'พฤษภาคม' => 5, 'มิถุนายน' => 6, 'กรกฎาคม' => 7, 'สิงหาคม' => 8,
        'กันยายน' => 9, 'ตุลาคม' => 10, 'พฤศจิกายน' => 11, 'ธันวาคม' => 12
    ];

    $tree = [];
    foreach ($levels as $level_name => $col) {
        $all_dates = [];
        if ($level_name === 'นว.4' || $level_name === 'ตว.4') {
            $course_filter = ($level_name === 'นว.4') ? "นายหน้า" : "ตัวแทน";
            $course_filter = $db->real_escape_string($course_filter);
            $query = "SELECT renew_other FROM {$table} WHERE renew_other IS NOT NULL AND renew_other != '' AND course_type LIKE '%{$course_filter}%'";
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $text = $row['renew_other'];
                    preg_match_all('/\[\s*(\d{1,2})\s+([ก-๙]+)\s+(\d{4})\s*\]/u', $text, $matches, PREG_SET_ORDER);
                    if (!empty($matches)) {
                        foreach ($matches as $m) {
                            $normalized_date = $m[1] . ' ' . $m[2] . ' ' . $m[3];
                            $all_dates[] = $normalized_date;
                        }
                    }
                }
            }
        } else {
            $col_safe = $db->real_escape_string($col);
            $query = "SELECT DISTINCT `{$col_safe}` FROM {$table} WHERE `{$col_safe}` IS NOT NULL AND `{$col_safe}` != ''";
            $result = $db->query($query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $d = trim($row[$col_safe]);
                    if (!empty($d)) {
                        $all_dates[] = $d;
                    }
                }
            }
        }
        
        $all_dates = array_unique($all_dates);
        usort($all_dates, function($a, $b) use ($thai_months) {
            preg_match('/(\d{1,2})\s+([ก-๙]+)\s+(\d{4})/', $a, $ma);
            preg_match('/(\d{1,2})\s+([ก-๙]+)\s+(\d{4})/', $b, $mb);
            $ya = isset($ma[3]) ? (int)$ma[3] : 0;
            $yb = isset($mb[3]) ? (int)$mb[3] : 0;
            if ($ya !== $yb) return $ya - $yb;
            
            $ma_m = isset($ma[2]) ? ($thai_months[$ma[2]] ?? 0) : 0;
            $mb_m = isset($mb[2]) ? ($thai_months[$mb[2]] ?? 0) : 0;
            if ($ma_m !== $mb_m) return $ma_m - $mb_m;
            
            $da = isset($ma[1]) ? (int)$ma[1] : 0;
            $db = isset($mb[1]) ? (int)$mb[1] : 0;
            return $da - $db;
        });
        
        $tree[] = [
            "level" => $level_name,
            "dates" => array_values($all_dates)
        ];
    }
    
    echo json_encode($tree);
    exit;
}

if (isset($_GET['action']) && in_array($_GET['action'], ['csv', 'excel', 'xlsx'])) {
    $format = $_GET['action'];
    
    try {
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($db->connect_errno) {
            throw new Exception("Connection failed: " . $db->connect_error);
        }
        $db->set_charset("utf8mb4");
        $db->query("SET SESSION group_concat_max_len = 1000000;");
        
        $table = defined('DB_TABLE_REGISTER') ? DB_TABLE_REGISTER : 'register';
        $query = "select 
r.id,start_time,completion_time
,form_name,last_modified_time,pdpa_consent
,title_th,title_custom,first_name_th,first_name_old_th
,first_name_en,middle_name_th,middle_name_old_th
,middle_name_en,last_name_th,last_name_old_th
,last_name_en,email_alt,gender,birth_date,blood_group,r.national_id
,id_card_expiry,religion,line_id,facebook,instagram
,food_allergy,medical_condition,phone_otp,emergency_contact_name
,emergency_contact_phone,addr_house_no,addr_soi,addr_moo
,addr_village,addr_road,addr_subdistrict,addr_district
,addr_province,addr_postcode,contact_address,contact_house_no
,contact_soi,contact_moo,contact_village,contact_road
,contact_subdistrict,contact_district,contact_province
,contact_postcode,license_type,license_no,license_status
,license_issue_date,license_expiry_date,region_affiliation
,region_north,region_northeast,region_east,region_central_west
,region_south,region_bangkok,training_exemption
,highest_education,past_training_5y,course_type,agent_level
,renew_agent_1,renew_agent_2,renew_agent_3,broker_level
,renew_broker_1,renew_broker_2,renew_broker_3
,case when v.renew_other_all is not null then v.renew_other_all else r.renew_other end 
as renew_other
,broker_company,viriyah_agent_code,insurance_experience_years
,extra_training_interest,sales_area,other_insurance_companies
,insurance_specialty,broker_branch,main_business
from {$table} r 
inner join 
(
    SELECT MAX(t1.id) AS id
    FROM {$table} t1
    INNER JOIN (
        SELECT national_id, MAX(updated_at) AS max_updated
        FROM {$table}
        WHERE confirmed = 'ยืนยันการสมัคร'
        GROUP BY national_id
    ) t2 ON t1.national_id = t2.national_id AND t1.updated_at <=> t2.max_updated
    WHERE t1.confirmed = 'ยืนยันการสมัคร'
    GROUP BY t1.national_id
) ru 
on r.id = ru.id
left join vw_renew_other_all v on r.national_id = v.national_id";

        $combinations = [];
        if (!empty($_GET['selected_ids']) && is_array($_GET['selected_ids'])) {
            $combinations[] = ['level' => 'CUSTOM', 'date' => ''];
        } else {
            if (isset($_GET['filter_tree']) && is_array($_GET['filter_tree'])) {
                foreach ($_GET['filter_tree'] as $level => $dates) {
                    foreach ((array)$dates as $date) {
                        $combinations[] = ['level' => $level, 'date' => $date];
                    }
                }
            }
            if (empty($combinations)) {
                $combinations[] = ['level' => '', 'date' => ''];
            }
        }

        $export_types = isset($_GET['export_type']) ? (array)$_GET['export_type'] : ['all'];
        if (empty($export_types)) {
            $export_types = ['all'];
        }

        $is_zip = (count($combinations) * count($export_types)) > 1 || (isset($_GET['force_zip']) && $_GET['force_zip'] == 1);
        $zip = null;
        $zip_path = '';
        $zip_filename = '';

        if ($is_zip) {
            $zip_filename = "export_data_" . date('Ymd_His') . ".zip";
            $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
                throw new Exception("Cannot create zip file.");
            }
        }

        $visible_cols_1 = ['title_th', 'first_name_th', 'last_name_th', 'email_alt', 'national_id', 'phone_otp', 'license_no'];
        $visible_cols_2 = ['id', 'title_th', 'first_name_th', 'last_name_th', 'email_alt', 'national_id', 'phone_otp', 'license_no', 'license_issue_date', 'license_expiry_date', 'agent_level', 'broker_level', 'Check_Duplicate', 'Check_License_Course', 'Check_Level'];
        
        $base_query = $query;

        foreach ($combinations as $combo) {
            $level = $combo['level'];
            $fdate = $combo['date'];
            
            $where_clauses = [];
            if ($level === 'CUSTOM') {
                $ids = array_map('intval', $_GET['selected_ids']);
                $where_clauses[] = "r.id IN (" . implode(',', $ids) . ")";
            } else if ($level !== '') {
                if ($level === 'นว.4' || $level === 'ตว.4') {
                    $course_filter = ($level === 'นว.4') ? "นายหน้า" : "ตัวแทน";
                    $where_clauses[] = "r.course_type LIKE '%{$course_filter}%'";
                    
                    if ($fdate !== '' && $fdate !== 'ALL') {
                        $parts = explode(' ', $fdate);
                        if (count($parts) === 3) {
                            $p0 = $db->real_escape_string($parts[0]);
                            $p1 = $db->real_escape_string($parts[1]);
                            $p2 = $db->real_escape_string($parts[2]);
                            $mysql_regex = "\\\\[[[:space:]]*{$p0}[[:space:]]+{$p1}[[:space:]]+{$p2}[[:space:]]*\\\\]";
                            $where_clauses[] = "(v.renew_other_all REGEXP '{$mysql_regex}' OR r.renew_other REGEXP '{$mysql_regex}')";
                        } else {
                            $fdate_safe = $db->real_escape_string($fdate);
                            $where_clauses[] = "(v.renew_other_all LIKE '%[{$fdate_safe}]%' OR r.renew_other LIKE '%[{$fdate_safe}]%')";
                        }
                    } else {
                        $where_clauses[] = "(v.renew_other_all IS NOT NULL OR (r.renew_other IS NOT NULL AND r.renew_other != ''))";
                    }
                } else {
                    $col_map = [
                        'นว.3' => 'renew_broker_3', 'นว.2' => 'renew_broker_2', 'นว.1' => 'renew_broker_1', 'นว.0' => 'renew_broker_1',
                        'ตว.3' => 'renew_agent_3', 'ตว.2' => 'renew_agent_2', 'ตว.1' => 'renew_agent_1', 'ตว.0' => 'renew_agent_1'
                    ];
                    if (isset($col_map[$level])) {
                        if ($fdate !== '' && $fdate !== 'ALL') {
                            $fdate_safe = $db->real_escape_string($fdate);
                            $fcol = $col_map[$level];
                            $where_clauses[] = "r.{$fcol} = '{$fdate_safe}'";
                        }
                    }
                }
            }
            
            $current_query = $base_query;
            if (!empty($where_clauses)) {
                $current_query .= " WHERE " . implode(' AND ', $where_clauses);
            }

            $result = $db->query($current_query);
            if (!$result) {
                throw new Exception("Query failed: " . $db->error);
            }

            $all_rows = [];
            $nat_id_counts = [];
            
            while ($row = $result->fetch_assoc()) {
                $all_rows[] = $row;
                $nid = $row['national_id'] ?? '';
                if ($nid) {
                    if (!isset($nat_id_counts[$nid])) $nat_id_counts[$nid] = 0;
                    $nat_id_counts[$nid]++;
                }
            }

            foreach ($all_rows as &$row) {
                $nid = $row['national_id'] ?? '';
                $row['Check_Duplicate'] = ($nid && isset($nat_id_counts[$nid])) ? $nat_id_counts[$nid] : 0;
                
                $license_no = trim($row['license_no'] ?? '');
                $course_type = $row['course_type'] ?? '';
                $check_license = '-';
                
                if (!empty($license_no)) {
                    if (strlen($license_no) >= 4) {
                        $digits = substr($license_no, 2, 2);
                        if ($digits === '02') {
                            $check_license = (strpos($course_type, 'ตัวแทน') !== false) ? 'ถูกต้อง' : 'ผิด (ควรเป็นตัวแทน)';
                        } elseif ($digits === '04') {
                            $check_license = (strpos($course_type, 'นายหน้า') !== false) ? 'ถูกต้อง' : 'ผิด (ควรเป็นนายหน้า)';
                        } else {
                            $check_license = 'ให้ตรวจสอบข้อมูลอีกครั้ง';
                        }
                    } else {
                        $check_license = 'ให้ตรวจสอบข้อมูลอีกครั้ง';
                    }
                }
                $row['Check_License_Course'] = $check_license;
                
                $agent_level = $row['agent_level'] ?? '';
                $broker_level = $row['broker_level'] ?? '';
                $license_expiry_date = trim($row['license_expiry_date'] ?? '');
                $check_level = '-';
                
                $is_expired = false;
                if (!empty($license_expiry_date) && $license_expiry_date !== '0000-00-00' && $license_expiry_date !== '-') {
                    $ts = strtotime(str_replace('/', '-', $license_expiry_date));
                    if ($ts && date('Y-m-d', $ts) < date('Y-m-d')) {
                        $is_expired = true;
                    }
                }
                
                if ($is_expired || empty($license_no) || strlen($license_no) < 2 || !is_numeric(substr($license_no, 0, 2))) {
                    $expected = 'ขอรับ';
                    $pattern = '/ขอรับ/u';
                    $is_valid = preg_match($pattern, $agent_level) || preg_match($pattern, $broker_level);
                    if ($is_valid) {
                        $check_level = 'ถูกต้อง';
                    } else {
                        $check_level = 'ผิด (ควรเป็น ' . $expected . ')';
                    }
                } else {
                    $license_yy = (int)substr($license_no, 0, 2);
                    $current_yy = (int)substr((string)(date('Y') + 543), 2, 2);
                    $diff_val = ($current_yy - $license_yy) + 1;
                    
                    if ($diff_val >= 1) {
                        if ($diff_val >= 4) {
                            $diff_val = 4;
                        }
                        $expected = 'ต่อ ' . $diff_val;
                        $pattern = '/ต่อ.*' . $diff_val . '/u';
                        $is_valid = preg_match($pattern, $agent_level) || preg_match($pattern, $broker_level);
                        if ($is_valid) {
                            $check_level = 'ถูกต้อง';
                        } else {
                            $check_level = 'ผิด (ควรเป็น ' . $expected . ')';
                        }
                    } else {
                        $check_level = 'ให้ตรวจสอบข้อมูลอีกครั้ง';
                    }
                }
                $row['Check_Level'] = $check_level;
            }
            unset($row);

            $level_str = ($level !== '') ? "_" . preg_replace('/[^a-zA-Z0-9ก-๙.-]/u', '', $level) : '';
            $date_str = ($fdate !== '' && $fdate !== 'ALL') ? "_" . preg_replace('/[^a-zA-Z0-9ก-๙.-]/u', '', str_replace('/', '-', $fdate)) : '';

            foreach ($export_types as $type) {
                $show_specific = ($type === 'specific');
                $show_specific2 = ($type === 'specific2');
                
                $current_visible_cols = [];
                $type_suffix = "_all";
                if ($show_specific) {
                    $current_visible_cols = $visible_cols_1;
                    $type_suffix = "_specific";
                } else if ($show_specific2) {
                    $current_visible_cols = $visible_cols_2;
                    $type_suffix = "_specific_checks";
                }
                
                if ($level === 'CUSTOM') {
                    $current_filename = "export_custom_" . date('Ymd_His');
                } else {
                    $current_filename = "export_data" . $level_str . $date_str . $type_suffix . "_" . date('Ymd_His');
                }
                
                ob_start();
                
                if ($format === 'csv') {
                    $output = fopen('php://output', 'w');
                    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
                    $header_printed = false;

                    foreach ($all_rows as $row) {
                        if ($show_specific || $show_specific2) {
                            $filtered_row = [];
                            foreach ($current_visible_cols as $col) {
                                $filtered_row[$col] = $row[$col] ?? '';
                            }
                            $row = $filtered_row;
                        }
                        if (!$header_printed) {
                            fputcsv($output, array_keys($row));
                            $header_printed = true;
                        }
                        fputcsv($output, $row);
                    }
                    fclose($output);
                } else if ($format === 'excel') {
                    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
                    
                    if (count($all_rows) === 0) {
                        echo '<head><meta http-equiv="Content-type" content="text/html;charset=utf-8" /></head>';
                        echo '<body>No data found</body></html>';
                    } else {
                        $first_row = $all_rows[0];
                        $col_count = count($first_row);
                        if ($show_specific || $show_specific2) {
                            $col_count = count($current_visible_cols);
                        }
                        
                        echo '<head>';
                        echo '<meta http-equiv="Content-type" content="text/html;charset=utf-8" />';
                        echo '<!--[if gte mso 9]><xml>
                        <x:ExcelWorkbook>
                            <x:ExcelWorksheets>
                                <x:ExcelWorksheet>
                                    <x:Name>DataExport</x:Name>
                                    <x:WorksheetOptions>
                                        <x:AutoFilter x:Range="R1C1:R1C' . $col_count . '" xmlns:x="urn:schemas-microsoft-com:office:excel"></x:AutoFilter>
                                    </x:WorksheetOptions>
                                </x:ExcelWorksheet>
                            </x:ExcelWorksheets>
                        </x:ExcelWorkbook>
                        </xml><![endif]-->';
                        echo '</head>';
                        echo '<body>';
                        echo '<table border="1">';
                        
                        $header_printed = false;
                        
                        foreach ($all_rows as $row) {
                            if (!$header_printed) {
                                echo '<colgroup>';
                                foreach (array_keys($row) as $col) {
                                    if (($show_specific || $show_specific2) && !in_array($col, $current_visible_cols)) {
                                        echo '<col width="0" style="display:none; mso-hide:all;">';
                                    } else {
                                        echo '<col>';
                                    }
                                }
                                echo '</colgroup>';

                                echo '<tr style="height:22.0pt;">';
                                foreach (array_keys($row) as $col) {
                                    $hide_style = (($show_specific || $show_specific2) && !in_array($col, $current_visible_cols)) ? ' style="display:none; mso-hide:all;"' : '';
                                    echo '<th' . $hide_style . ' x:autofilter="all">' . htmlspecialchars($col) . '</th>';
                                }
                                echo '</tr>';
                                $header_printed = true;
                            }
                            
                            echo '<tr style="height:22.0pt;">';
                            foreach ($row as $col => $val) {
                                $hide_style = (($show_specific || $show_specific2) && !in_array($col, $current_visible_cols)) ? ' style="display:none; mso-hide:all;"' : '';
                                echo '<td' . $hide_style . '>' . htmlspecialchars($val ?? '') . '</td>';
                            }
                            echo '</tr>';
                        }
                        
                        echo '</table>';
                        echo '</body></html>';
                    }
                } else if ($format === 'xlsx') {
                    require_once 'SimpleXLSXGen.php';
                    $data = [];
                    $header_printed = false;
                    foreach ($all_rows as $row) {
                        if ($show_specific || $show_specific2) {
                            $filtered_row = [];
                            foreach ($current_visible_cols as $col) {
                                $filtered_row[$col] = $row[$col] ?? '';
                            }
                            $row = $filtered_row;
                        }
                        if (!$header_printed) {
                            $data[] = array_keys($row);
                            $header_printed = true;
                        }
                        $data[] = array_values($row);
                    }
                    if (empty($data)) {
                        $data[] = ['No data found'];
                    }
                    $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($data);
                    
                    ob_end_clean();
                    $file_content = (string) $xlsx;
                }
                
                if ($format !== 'xlsx') {
                    $file_content = ob_get_clean();
                }
                
                if ($is_zip) {
                    $ext = ($format === 'csv') ? '.csv' : (($format === 'xlsx') ? '.xlsx' : '.xls');
                    $zip_entry_name = $current_filename . $ext;
                    if ($zip->locateName($zip_entry_name) !== false) {
                        $zip_entry_name = $current_filename . "_" . uniqid() . $ext;
                    }
                    $zip->addFromString($zip_entry_name, $file_content);
                } else {
                    if ($format === 'csv') {
                        header('Content-Type: text/csv; charset=utf-8');
                        header('Content-Disposition: attachment; filename="' . $current_filename . '.csv"');
                    } else if ($format === 'xlsx') {
                        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                        header('Content-Disposition: attachment; filename="' . $current_filename . '.xlsx"');
                    } else {
                        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                        header('Content-Disposition: attachment; filename="' . $current_filename . '.xls"');
                    }
                    echo $file_content;
                    exit;
                }
            }
        }

        if ($is_zip) {
            $zip->close();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
            header('Content-Length: ' . filesize($zip_path));
            readfile($zip_path);
            unlink($zip_path);
            exit;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<?php require_once 'includes/header.php'; ?>
<style>
    :root {
        --accent-primary: #FCAF17;
        --accent-secondary: #0033A2;
        --accent-hover-primary: #D98C04;
        --accent-hover-secondary: #00227A;
    }
    .export-container {
        background: #FFFFFF;
        padding: 3rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        border-top: 4px solid var(--accent-secondary);
        max-width: 800px;
        margin: 2rem auto;
    }
    .export-container h2 {
        margin-top: 0;
        font-weight: 600;
        margin-bottom: 1rem;
        color: var(--accent-secondary);
        text-align: center;
    }
    .export-container p {
        color: #666666;
        margin-bottom: 2.5rem;
        line-height: 1.5;
        text-align: center;
    }
    .btn-container {
        display: flex;
        gap: 1.5rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    .btn-custom {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.875rem 1.5rem;
        font-size: 1rem;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        cursor: pointer;
        border: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    .btn-custom:hover {
        transform: translateY(-2px);
        color: #fff;
    }
    .btn-csv {
        background-color: var(--accent-primary);
        color: #000;
    }
    .btn-csv:hover {
        background-color: var(--accent-hover-primary);
        color: #000;
    }
    .btn-excel {
        background-color: var(--accent-secondary);
    }
    .btn-excel:hover {
        background-color: var(--accent-hover-secondary);
    }
    .icon {
        margin-right: 0.5rem;
        width: 1.25rem;
        height: 1.25rem;
        fill: currentColor;
    }
    .error {
        background-color: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        color: #dc3545;
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        font-size: 0.875rem;
    }
</style>

<div class="export-container">
    <div style="text-align: left; margin-bottom: 1.5rem;">
        <a href="reports.php" class="btn btn-outline-secondary btn-sm" style="text-decoration:none; box-shadow:none;">
            <i class="fa-solid fa-arrow-left"></i> กลับหน้ารายงาน
        </a>
    </div>
    <h2>ระบบส่งออกข้อมูล (Export Data)</h2>
    <p>กรุณาเลือกรูปแบบที่ต้องการเพื่อดาวน์โหลดข้อมูลการลงทะเบียนล่าสุด ระบบจะทำการรวบรวมข้อมูลจากฐานข้อมูลให้โดยอัตโนมัติ</p>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="GET" action="">
        <div style="margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px solid #eee;">
            <h3 style="font-size: 1.15rem; color: var(--accent-secondary); margin-bottom: 1rem;"><i class="fa-solid fa-search"></i> ค้นหาและระบุตัวบุคคล</h3>
            <p style="color: #666; font-size: 0.9rem; margin-bottom: 1rem;">หากเลือกระบุบุคคลในส่วนนี้ ระบบจะทำการส่งออกเฉพาะบุคคลที่อยู่ในรายชื่อ (ข้ามการกรองตามหลักสูตร)</p>
            <div style="display:flex; gap:0.5rem; margin-bottom: 1rem;">
                <input type="text" id="search_person_input" class="form-control" placeholder="ค้นหา ชื่อ, นามสกุล, บัตรประชาชน, เลขใบอนุญาต..." style="flex:1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 0.5rem;">
                <button type="button" id="btn_search_person" class="btn btn-outline-primary" style="padding: 0.5rem 1rem; border-radius: 0.5rem; box-shadow: none;">ค้นหา</button>
            </div>
            <div id="search_results" style="max-height: 200px; overflow-y: auto; margin-bottom: 1rem; background: #fff; border-radius: 0.5rem;"></div>
            
            <div id="selected_persons_container" style="display:none; background: #f8f9fa; padding: 1rem; border-radius: 0.5rem; border: 1px solid #ddd;">
                <h4 style="font-size: 0.95rem; color: #333; margin-bottom: 0.75rem; font-weight: 600;">บุคคลที่เลือกสำหรับส่งออก (<span id="selected_count">0</span> คน)</h4>
                <ul id="selected_persons_list" style="list-style:none; padding:0; margin:0;"></ul>
                <div id="selected_hidden_inputs"></div>
            </div>
        </div>

        <div style="margin-bottom: 2rem; text-align: left; background: #F4F7F6; padding: 1rem; border-radius: 0.5rem; border: 1px solid #DDDDDD;">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.95rem; color: #333333; font-weight: 600; margin-bottom: 0.5rem;">เลือกระดับและวันที่ (ส่งออกแยกไฟล์หากเลือกหลายรายการ)</label>
                <div id="tree_view_container" style="max-height: 300px; overflow-y: auto; background: #FFFFFF; border: 1px solid #DDDDDD; border-radius: 0.25rem; padding: 0.5rem; text-align: left;">
                    <div style="text-align: center; padding: 1rem; color: #666;">กำลังโหลดข้อมูล...</div>
                </div>
            </div>
            
            <label style="display: flex; align-items: flex-start; cursor: pointer; margin-bottom: 0.75rem;">
                <input type="checkbox" name="export_type[]" value="all" id="exp_all" style="width: 1.2rem; height: 1.2rem; margin-right: 0.75rem; margin-top: 0.15rem; flex-shrink: 0; accent-color: var(--accent-primary);">
                <span style="font-size: 0.95rem; color: #333333; font-weight: 500; line-height: 1.5;">แสดงทั้งหมด (ทุกคอลัมน์)</span>
            </label>
            <label style="display: flex; align-items: flex-start; cursor: pointer; margin-bottom: 0.75rem;">
                <input type="checkbox" name="export_type[]" value="specific" id="exp_spec" style="width: 1.2rem; height: 1.2rem; margin-right: 0.75rem; margin-top: 0.15rem; flex-shrink: 0; accent-color: var(--accent-primary);" checked>
                <span style="font-size: 0.95rem; color: #333333; font-weight: 500; line-height: 1.5;">แสดงเฉพาะข้อมูลหลัก (คำนำหน้า, ชื่อ, นามสกุล, อีเมล, บัตรประชาชน, เบอร์โทร, เลขใบอนุญาต)</span>
            </label>
            <label style="display: flex; align-items: flex-start; cursor: pointer;">
                <input type="checkbox" name="export_type[]" value="specific2" id="exp_spec2" style="width: 1.2rem; height: 1.2rem; margin-right: 0.75rem; margin-top: 0.15rem; flex-shrink: 0; accent-color: var(--accent-primary);">
                <span style="font-size: 0.95rem; color: #333333; font-weight: 500; line-height: 1.5;">แสดงเฉพาะข้อมูล (id, คำนำหน้า, ชื่อ, นามสกุล, อีเมล, บัตรประชาชน, เบอร์โทร, เลขใบอนุญาต, license_issue_date, license_expiry_date, agent_level, broker_level, Check_Duplicate, Check_License_Course, Check_Level)</span>
            </label>
        </div>
        <div class="btn-container">
            <button type="submit" name="action" value="csv" class="btn btn-custom btn-csv">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.8,20H14L12,16.6L10,20H8.2L11.1,15.5L8.2,11H10L12,14.4L14,11H15.8L12.9,15.5L15.8,20M13,9V3.5L18.5,9H13Z" /></svg>
                Export to CSV
            </button>
            <button type="submit" name="action" value="excel" class="btn btn-custom btn-excel">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.8,20H14L12,16.6L10,20H8.2L11.1,15.5L8.2,11H10L12,14.4L14,11H15.8L12.9,15.5L15.8,20M13,9V3.5L18.5,9H13Z" /></svg>
                Export to Excel (.xls)
            </button>
            <button type="submit" name="action" value="xlsx" class="btn btn-custom" style="background-color: #217346; color: white;">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.8,20H14L12,16.6L10,20H8.2L11.1,15.5L8.2,11H10L12,14.4L14,11H15.8L12.9,15.5L15.8,20M13,9V3.5L18.5,9H13Z" /></svg>
                Export to Excel (.xlsx)
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const expAll = document.getElementById('exp_all');
        const expSpec = document.getElementById('exp_spec');
        const expSpec2 = document.getElementById('exp_spec2');
        if (!expAll.checked && !expSpec.checked && (!expSpec2 || !expSpec2.checked)) {
            e.preventDefault();
            alert('กรุณาเลือกรูปแบบการแสดงผลอย่างน้อย 1 รายการ');
        }
    });

    const treeContainer = document.getElementById('tree_view_container');
    
    fetch('?fetch_tree=1')
        .then(res => res.json())
        .then(tree => {
            let html = '';
            tree.forEach(node => {
                const level = node.level;
                const dates = node.dates;
                
                html += `<div style="margin-bottom: 0.5rem;">`;
                html += `  <div style="display: flex; align-items: center; cursor: pointer; padding: 0.5rem; background: #F9FAFB; border-radius: 0.25rem; border: 1px solid #E5E7EB; transition: background 0.2s;" onclick="toggleTree('${level}')" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='#F9FAFB'">`;
                html += `    <span id="icon_${level}" style="width: 20px; font-weight: bold; color: var(--accent-primary);">+</span>`;
                html += `    <input type="checkbox" id="chk_level_${level}" style="margin-right: 0.75rem; width: 1rem; height: 1rem; accent-color: var(--accent-primary);" onclick="event.stopPropagation(); toggleLevel('${level}')">`;
                html += `    <strong style="color: #333; font-size: 0.95rem; user-select: none;">${level} <span style="font-size: 0.8rem; color: #888; font-weight: normal;">(${dates.length} รอบ)</span></strong>`;
                html += `  </div>`;
                
                html += `  <div id="child_${level}" style="display: none; padding-left: 2.25rem; margin-top: 0.5rem;">`;
                if (dates.length > 0) {
                    dates.forEach((d, i) => {
                        const idStr = `chk_date_${level}_${i}`;
                        html += `    <label for="${idStr}" style="display: flex; align-items: center; margin-bottom: 0.4rem; cursor: pointer;">`;
                        html += `      <input type="checkbox" id="${idStr}" name="filter_tree[${level}][]" value="${d}" class="chk_date_${level}" style="margin-right: 0.5rem; width: 0.9rem; height: 0.9rem; accent-color: var(--accent-primary);" onchange="updateParent('${level}')">`;
                        html += `      <span style="font-size: 0.9rem; color: #555; user-select: none;">${d}</span>`;
                        html += `    </label>`;
                    });
                } else {
                    html += `    <div style="font-size: 0.9rem; color: #999; font-style: italic; margin-bottom: 0.4rem;">ไม่มีข้อมูล</div>`;
                }
                html += `  </div>`;
                html += `</div>`;
            });
            treeContainer.innerHTML = html;
        })
        .catch(err => {
            treeContainer.innerHTML = '<div style="color: red; text-align: center; padding: 1rem;">โหลดล้มเหลว โปรดลองใหม่อีกครั้ง</div>';
        });

    window.toggleTree = function(level) {
        const child = document.getElementById('child_' + level);
        const icon = document.getElementById('icon_' + level);
        if (child.style.display === 'none') {
            child.style.display = 'block';
            icon.innerText = '-';
        } else {
            child.style.display = 'none';
            icon.innerText = '+';
        }
    };

    window.toggleLevel = function(level) {
        const parentChk = document.getElementById('chk_level_' + level);
        const childChks = document.querySelectorAll('.chk_date_' + level);
        childChks.forEach(chk => {
            chk.checked = parentChk.checked;
        });
    };

    window.updateParent = function(level) {
        const parentChk = document.getElementById('chk_level_' + level);
        const childChks = document.querySelectorAll('.chk_date_' + level);
        let allChecked = true;
        let anyChecked = false;
        
        if (childChks.length === 0) return;
        
        childChks.forEach(chk => {
            if (chk.checked) {
                anyChecked = true;
            } else {
                allChecked = false;
            }
        });
        parentChk.checked = allChecked;
        parentChk.indeterminate = anyChecked && !allChecked;
    };

    const searchBtn = document.getElementById('btn_search_person');
    const searchInput = document.getElementById('search_person_input');
    const searchResults = document.getElementById('search_results');
    const selectedList = document.getElementById('selected_persons_list');
    const selectedInputs = document.getElementById('selected_hidden_inputs');
    const selectedContainer = document.getElementById('selected_persons_container');
    const selectedCount = document.getElementById('selected_count');
    
    let selectedPersons = [];
    
    function updateSelectedUI() {
        selectedList.innerHTML = '';
        selectedInputs.innerHTML = '';
        
        if (selectedPersons.length === 0) {
            selectedContainer.style.display = 'none';
        } else {
            selectedContainer.style.display = 'block';
            selectedCount.innerText = selectedPersons.length;
            
            selectedPersons.forEach((p, idx) => {
                const li = document.createElement('li');
                li.style.padding = '0.5rem';
                li.style.borderBottom = '1px solid #ddd';
                li.style.display = 'flex';
                li.style.justifyContent = 'space-between';
                li.style.alignItems = 'center';
                li.style.backgroundColor = idx % 2 === 0 ? '#fff' : '#fcfcfc';
                
                li.innerHTML = `
                    <div>
                        <strong>${p.first_name_th} ${p.last_name_th}</strong> 
                        <span style="color:#666; font-size:0.85rem; margin-left:0.5rem;">(ID: ${p.national_id}, ใบอนุญาต: ${p.license_no || '-'})</span>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePerson('${p.id}')">
                        <i class="fa-solid fa-times"></i>
                    </button>
                `;
                selectedList.appendChild(li);
                
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'selected_ids[]';
                hidden.value = p.id;
                selectedInputs.appendChild(hidden);
            });
        }
    }
    
    window.removePerson = function(id) {
        selectedPersons = selectedPersons.filter(p => String(p.id) !== String(id));
        updateSelectedUI();
    };
    
    window.addPerson = function(personStr) {
        const p = JSON.parse(decodeURIComponent(personStr));
        if (!selectedPersons.find(exist => exist.id === p.id)) {
            selectedPersons.push(p);
            updateSelectedUI();
        }
    };
    
    searchBtn.addEventListener('click', function() {
        const term = searchInput.value.trim();
        if (term.length < 2) {
            alert('กรุณาพิมพ์คำค้นหาอย่างน้อย 2 ตัวอักษร');
            return;
        }
        
        searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: #666;">กำลังค้นหา...</div>';
        
        fetch('export_data.php?search_person=' + encodeURIComponent(term))
            .then(res => res.json())
            .then(data => {
                searchResults.innerHTML = '';
                if (data.length === 0) {
                    searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: #666;">ไม่พบข้อมูล</div>';
                    return;
                }
                
                data.forEach(p => {
                    const div = document.createElement('div');
                    div.style.padding = '0.5rem 1rem';
                    div.style.border = '1px solid #eee';
                    div.style.marginBottom = '-1px';
                    div.style.display = 'flex';
                    div.style.justifyContent = 'space-between';
                    div.style.alignItems = 'center';
                    
                    const personStr = encodeURIComponent(JSON.stringify(p));
                    
                    div.innerHTML = `
                        <div>
                            <strong>${p.first_name_th} ${p.last_name_th}</strong> 
                            <div style="color:#666; font-size:0.85rem;">
                                บัตรประชาชน: ${p.national_id} | ใบอนุญาต: ${p.license_no || '-'}
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addPerson('${personStr}')">
                            <i class="fa-solid fa-plus"></i> เพิ่ม
                        </button>
                    `;
                    searchResults.appendChild(div);
                });
            })
            .catch(err => {
                searchResults.innerHTML = '<div style="padding: 1rem; text-align: center; color: red;">เกิดข้อผิดพลาดในการค้นหา</div>';
            });
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            searchBtn.click();
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
