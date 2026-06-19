<?php
include 'appconfig.php';

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

if (isset($_GET['action']) && in_array($_GET['action'], ['csv', 'excel'])) {
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

        $where_clauses = [];
        if (isset($_GET['filter_level']) && $_GET['filter_level'] !== '') {
            $level = $_GET['filter_level'];
            if ($level === 'นว.4' || $level === 'ตว.4') {
                $course_filter = ($level === 'นว.4') ? "นายหน้า" : "ตัวแทน";
                $where_clauses[] = "r.course_type LIKE '%{$course_filter}%'";
                
                if (isset($_GET['filter_date']) && $_GET['filter_date'] !== '') {
                    $parts = explode(' ', $_GET['filter_date']);
                    if (count($parts) === 3) {
                        $p0 = $db->real_escape_string($parts[0]);
                        $p1 = $db->real_escape_string($parts[1]);
                        $p2 = $db->real_escape_string($parts[2]);
                        $mysql_regex = "\\\\[[[:space:]]*{$p0}[[:space:]]+{$p1}[[:space:]]+{$p2}[[:space:]]*\\\\]";
                        $where_clauses[] = "(v.renew_other_all REGEXP '{$mysql_regex}' OR r.renew_other REGEXP '{$mysql_regex}')";
                    } else {
                        $fdate = $db->real_escape_string($_GET['filter_date']);
                        $where_clauses[] = "(v.renew_other_all LIKE '%[{$fdate}]%' OR r.renew_other LIKE '%[{$fdate}]%')";
                    }
                } else {
                    $where_clauses[] = "(v.renew_other_all IS NOT NULL OR (r.renew_other IS NOT NULL AND r.renew_other != ''))";
                }
            } else {
                $col_map = [
                    'นว.3' => 'renew_broker_3',
                    'นว.2' => 'renew_broker_2',
                    'นว.1' => 'renew_broker_1',
                    'นว.0' => 'renew_broker_1',
                    'ตว.3' => 'renew_agent_3',
                    'ตว.2' => 'renew_agent_2',
                    'ตว.1' => 'renew_agent_1',
                    'ตว.0' => 'renew_agent_1'
                ];
                if (isset($col_map[$level])) {
                    if (isset($_GET['filter_date']) && $_GET['filter_date'] !== '') {
                        $fdate = $db->real_escape_string($_GET['filter_date']);
                        $fcol = $col_map[$level];
                        $where_clauses[] = "r.{$fcol} = '{$fdate}'";
                    }
                }
            }
        }
        
        if (!empty($where_clauses)) {
            $query .= " WHERE " . implode(' AND ', $where_clauses);
        }

        $result = $db->query($query);
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

        // Precalculate check columns for all rows
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

        $level_str = '';
        if (isset($_GET['filter_level']) && $_GET['filter_level'] !== '') {
            $level_str = "_" . preg_replace('/[^a-zA-Z0-9ก-๙.-]/u', '', $_GET['filter_level']);
        }
        $date_str = '';
        if (isset($_GET['filter_date']) && $_GET['filter_date'] !== '') {
            $clean_date = str_replace('/', '-', $_GET['filter_date']);
            $date_str = "_" . preg_replace('/[^a-zA-Z0-9ก-๙.-]/u', '', $clean_date);
        }

        $export_types = isset($_GET['export_type']) ? (array)$_GET['export_type'] : ['all'];
        if (empty($export_types)) {
            $export_types = ['all'];
        }

        $is_zip = count($export_types) > 1;
        $zip = null;
        $zip_path = '';
        $zip_filename = '';

        if ($is_zip) {
            $zip_filename = "export_data" . $level_str . $date_str . "_" . date('Ymd_His') . ".zip";
            $zip_path = sys_get_temp_dir() . '/' . $zip_filename;
            $zip = new ZipArchive();
            if ($zip->open($zip_path, ZipArchive::CREATE) !== TRUE) {
                throw new Exception("Cannot create zip file.");
            }
        }

        $visible_cols_1 = ['title_th', 'first_name_th', 'last_name_th', 'email_alt', 'national_id', 'phone_otp', 'license_no'];
        $visible_cols_2 = ['id', 'title_th', 'first_name_th', 'last_name_th', 'email_alt', 'national_id', 'phone_otp', 'license_no', 'license_issue_date', 'license_expiry_date', 'agent_level', 'broker_level', 'Check_Duplicate', 'Check_License_Course', 'Check_Level'];

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
            
            $current_filename = "export_data" . $level_str . $date_str . $type_suffix . "_" . date('Ymd_His');
            
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
                    $row_index = 2;
                    
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
                        $row_index++;
                    }
                    
                    echo '</table>';
                    echo '</body></html>';
                }
            }
            
            $file_content = ob_get_clean();
            
            if ($is_zip) {
                $ext = ($format === 'csv') ? '.csv' : '.xls';
                $zip->addFromString($current_filename . $ext, $file_content);
            } else {
                if ($format === 'csv') {
                    header('Content-Type: text/csv; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $current_filename . '.csv"');
                } else {
                    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
                    header('Content-Disposition: attachment; filename="' . $current_filename . '.xls"');
                }
                echo $file_content;
                exit;
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Export</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0033A2;
            --card-bg: #FFFFFF;
            --text-color: #333333;
            --accent-primary: #FCAF17;
            --accent-secondary: #0033A2;
            --accent-hover-primary: #D98C04;
            --accent-hover-secondary: #00227A;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            background-image: radial-gradient(circle at 50% -20%, #0044CC, #0033A2);
            color: var(--text-color);
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.1);
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        h1 {
            margin-top: 0;
            font-weight: 600;
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--accent-secondary);
        }
        p {
            color: #666666;
            margin-bottom: 2.5rem;
            line-height: 1.5;
        }
        .btn-container {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
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
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .btn:active {
            transform: translateY(0);
        }
        .btn-csv {
            background-color: var(--accent-primary);
        }
        .btn-csv:hover {
            background-color: var(--accent-hover-primary);
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
            color: #fca5a5;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>ระบบส่งออกข้อมูล</h1>
    <p>กรุณาเลือกรูปแบบที่ต้องการเพื่อดาวน์โหลดข้อมูลการลงทะเบียนล่าสุด ระบบจะทำการรวบรวมข้อมูลจากฐานข้อมูลให้โดยอัตโนมัติ</p>
    
    <?php if (isset($error)): ?>
        <div class="error">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <form method="GET" action="">
        <div style="margin-bottom: 2rem; text-align: left; background: #F4F7F6; padding: 1rem; border-radius: 0.5rem; border: 1px solid #DDDDDD;">
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.95rem; color: #333333; font-weight: 600; margin-bottom: 0.5rem;">เลือกระดับ (กรองข้อมูลตามระดับ)</label>
                <select name="filter_level" id="filter_level" style="width: 100%; padding: 0.75rem; border-radius: 0.25rem; border: 1px solid #DDDDDD; background: #FFFFFF; color: #333333; font-size: 1rem; cursor: pointer;">
                    <option value="">-- ทั้งหมด (ไม่กรอง) --</option>
                    <option value="นว.4">นว.4</option>
                    <option value="ตว.4">ตว.4</option>
                    <option value="นว.3">นว.3</option>
                    <option value="ตว.3">ตว.3</option>
                    <option value="นว.2">นว.2</option>
                    <option value="ตว.2">ตว.2</option>
                    <option value="นว.1">นว.1</option>
                    <option value="ตว.1">ตว.1</option>
                    <option value="นว.0">นว.0</option>
                    <option value="ตว.0">ตว.0</option>
                </select>
            </div>
            
            <div id="date_filter_container" style="margin-bottom: 1.5rem; display: none;">
                <label style="display: block; font-size: 0.95rem; color: #333333; font-weight: 600; margin-bottom: 0.5rem;">เลือกวันที่</label>
                <select name="filter_date" id="filter_date" style="width: 100%; padding: 0.75rem; border-radius: 0.25rem; border: 1px solid #DDDDDD; background: #FFFFFF; color: #333333; font-size: 1rem; cursor: pointer;">
                    <option value="">-- โปรดเลือกระดับก่อน --</option>
                </select>
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
            <button type="submit" name="action" value="csv" class="btn btn-csv">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.8,20H14L12,16.6L10,20H8.2L11.1,15.5L8.2,11H10L12,14.4L14,11H15.8L12.9,15.5L15.8,20M13,9V3.5L18.5,9H13Z" /></svg>
                Export to CSV
            </button>
            <button type="submit" name="action" value="excel" class="btn btn-excel">
                <svg class="icon" viewBox="0 0 24 24"><path d="M14,2H6A2,2 0 0,0 4,4V20A2,2 0 0,0 6,22H18A2,2 0 0,0 20,20V8L14,2M15.8,20H14L12,16.6L10,20H8.2L11.1,15.5L8.2,11H10L12,14.4L14,11H15.8L12.9,15.5L15.8,20M13,9V3.5L18.5,9H13Z" /></svg>
                Export to Excel
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

    const filterLevel = document.getElementById('filter_level');
    const dateContainer = document.getElementById('date_filter_container');
    const filterDate = document.getElementById('filter_date');
    
    const colMap = {
        'นว.4': 'นว.4',
        'ตว.4': 'ตว.4',
        'นว.3': 'renew_broker_3',
        'นว.2': 'renew_broker_2',
        'นว.1': 'renew_broker_1',
        'นว.0': 'renew_broker_1',
        'ตว.3': 'renew_agent_3',
        'ตว.2': 'renew_agent_2',
        'ตว.1': 'renew_agent_1',
        'ตว.0': 'renew_agent_1'
    };

    filterLevel.addEventListener('change', function() {
        const val = this.value;
        if (colMap[val]) {
            // Show dropdown and fetch
            dateContainer.style.display = 'block';
            filterDate.innerHTML = '<option value="">-- กำลังโหลด... --</option>';
            
            fetch('?fetch_dates=' + encodeURIComponent(colMap[val]))
                .then(response => response.json())
                .then(dates => {
                    if (dates.length > 0) {
                        let html = '<option value="">-- เลือกวันที่ --</option>';
                        dates.forEach(d => {
                            html += '<option value="'+d+'">'+d+'</option>';
                        });
                        filterDate.innerHTML = html;
                    } else {
                        filterDate.innerHTML = '<option value="">-- ไม่พบข้อมูลวันที่ --</option>';
                    }
                })
                .catch(err => {
                    filterDate.innerHTML = '<option value="">-- โหลดล้มเหลว --</option>';
                });
        } else {
            // Hide for นว.4, ตว.4, or empty
            dateContainer.style.display = 'none';
            filterDate.innerHTML = '<option value=""></option>';
        }
    });
});
</script>

</body>
</html>
