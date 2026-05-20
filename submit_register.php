<?php
// =====================================================
//  Registration Form Submit Handler (AJAX/API Endpoint)
//  สำหรับ project ใน folder new/
//  Table: register_uat
// =====================================================

include 'appconfig.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Method not allowed'));
    exit;
}

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Clean up ID and phone
    $idRaw = preg_replace('/[^0-9]/', '', isset($_POST['idCard']) ? $_POST['idCard'] : '');
    $phoneRaw = preg_replace('/[^0-9]/', '', isset($_POST['phone']) ? $_POST['phone'] : '');
    $emergPhoneRaw = preg_replace('/[^0-9]/', '', isset($_POST['emergencyContactPhone']) ? $_POST['emergencyContactPhone'] : '');

    // Checkbox arrays to comma-separated strings
    $deductionPrivilege = isset($_POST['deductionPrivilege']) ? implode(',', $_POST['deductionPrivilege']) : '';
    $previousCourses = isset($_POST['previousCourses']) ? implode(',', $_POST['previousCourses']) : '';
    $salesTerritories = isset($_POST['salesTerritories']) ? implode(',', $_POST['salesTerritories']) : '';
    $otherInsuranceCompanies = isset($_POST['otherInsuranceCompanies']) ? implode(',', $_POST['otherInsuranceCompanies']) : '';

    $stmt = $pdo->prepare("INSERT INTO register_uat (
        pdpa_consent, national_id, id_card_expiry,
        title_th, title_custom,
        first_name_th, middle_name_th, last_name_th,
        first_name_en, middle_name_en, last_name_en,
        has_changed_name, title_prev, title_custom_prev,
        first_name_th_prev, middle_name_th_prev, last_name_th_prev,
        first_name_en_prev, middle_name_en_prev, last_name_en_prev,
        birth_date, religion, gender, blood_group,
        phone_otp, email,
        line_id, facebook, instagram,
        food_allergy, medical_condition,
        emergency_contact_name, emergency_contact_phone,
        addr_house_no, addr_moo, addr_village, addr_soi, addr_road,
        addr_province, addr_district, addr_subdistrict, addr_postcode,
        shipping_address_type,
        ship_house_no, ship_moo, ship_village, ship_soi, ship_road,
        ship_province, ship_district, ship_subdistrict, ship_postcode,
        agent_type, license_status, license_no, license_issue_date, license_expiry_date,
        agent_region, agent_branch, broker_affiliation, viriyah_agent_code,
        course_type, training_date, training_format,
        deduction_privilege, previous_courses, additional_course_requirement,
        highest_education, occupation, branch_recommender, insurance_experience_years,
        sales_territories, other_insurance_companies,
        has_experience, expectation, certify_true,
        created_at
    ) VALUES (
        :pdpa_consent, :national_id, :id_card_expiry,
        :title_th, :title_custom,
        :first_name_th, :middle_name_th, :last_name_th,
        :first_name_en, :middle_name_en, :last_name_en,
        :has_changed_name, :title_prev, :title_custom_prev,
        :first_name_th_prev, :middle_name_th_prev, :last_name_th_prev,
        :first_name_en_prev, :middle_name_en_prev, :last_name_en_prev,
        :birth_date, :religion, :gender, :blood_group,
        :phone_otp, :email,
        :line_id, :facebook, :instagram,
        :food_allergy, :medical_condition,
        :emergency_contact_name, :emergency_contact_phone,
        :addr_house_no, :addr_moo, :addr_village, :addr_soi, :addr_road,
        :addr_province, :addr_district, :addr_subdistrict, :addr_postcode,
        :shipping_address_type,
        :ship_house_no, :ship_moo, :ship_village, :ship_soi, :ship_road,
        :ship_province, :ship_district, :ship_subdistrict, :ship_postcode,
        :agent_type, :license_status, :license_no, :license_issue_date, :license_expiry_date,
        :agent_region, :agent_branch, :broker_affiliation, :viriyah_agent_code,
        :course_type, :training_date, :training_format,
        :deduction_privilege, :previous_courses, :additional_course_requirement,
        :highest_education, :occupation, :branch_recommender, :insurance_experience_years,
        :sales_territories, :other_insurance_companies,
        :has_experience, :expectation, :certify_true,
        NOW()
    )");

    // Helper to get POST value safely
    function p($key) {
        return isset($_POST[$key]) ? trim($_POST[$key]) : '';
    }

    $stmt->execute([
        ':pdpa_consent' => p('pdpaConsent'),
        ':national_id' => $idRaw,
        ':id_card_expiry' => p('idCardExpiry'),
        ':title_th' => p('titleName'),
        ':title_custom' => p('titleNameOther'),
        ':first_name_th' => p('firstNameTh'),
        ':middle_name_th' => p('middleNameTh'),
        ':last_name_th' => p('lastNameTh'),
        ':first_name_en' => p('firstNameEn'),
        ':middle_name_en' => p('middleNameEn'),
        ':last_name_en' => p('lastNameEn'),
        ':has_changed_name' => p('hasChangedName'),
        ':title_prev' => p('titleNamePrev'),
        ':title_custom_prev' => p('titleNameOtherPrev'),
        ':first_name_th_prev' => p('firstNameThPrev'),
        ':middle_name_th_prev' => p('middleNameThPrev'),
        ':last_name_th_prev' => p('lastNameThPrev'),
        ':first_name_en_prev' => p('firstNameEnPrev'),
        ':middle_name_en_prev' => p('middleNameEnPrev'),
        ':last_name_en_prev' => p('lastNameEnPrev'),
        ':birth_date' => p('birthDate'),
        ':religion' => p('religion'),
        ':gender' => p('gender'),
        ':blood_group' => p('bloodGroup'),
        ':phone_otp' => $phoneRaw,
        ':email' => p('email'),
        ':line_id' => p('lineId'),
        ':facebook' => p('facebook'),
        ':instagram' => p('instagram'),
        ':food_allergy' => p('foodAllergy'),
        ':medical_condition' => p('chronicDisease'),
        ':emergency_contact_name' => p('emergencyContactName'),
        ':emergency_contact_phone' => $emergPhoneRaw,
        ':addr_house_no' => p('houseNo'),
        ':addr_moo' => p('moo'),
        ':addr_village' => p('village'),
        ':addr_soi' => p('soi'),
        ':addr_road' => p('road'),
        ':addr_province' => p('province'),
        ':addr_district' => p('district'),
        ':addr_subdistrict' => p('subDistrict'),
        ':addr_postcode' => p('zipcode'),
        ':shipping_address_type' => p('shippingAddress'),
        ':ship_house_no' => p('shipHouseNo'),
        ':ship_moo' => p('shipMoo'),
        ':ship_village' => p('shipVillage'),
        ':ship_soi' => p('shipSoi'),
        ':ship_road' => p('shipRoad'),
        ':ship_province' => p('shipProvince'),
        ':ship_district' => p('shipDistrict'),
        ':ship_subdistrict' => p('shipSubDistrict'),
        ':ship_postcode' => p('shipZipcode'),
        ':agent_type' => p('agentType'),
        ':license_status' => p('licenseStatus'),
        ':license_no' => p('licenseNo'),
        ':license_issue_date' => p('licenseIssue'),
        ':license_expiry_date' => p('licenseExpire'),
        ':agent_region' => p('agentRegion'),
        ':agent_branch' => p('agentBranch'),
        ':broker_affiliation' => p('brokerAffiliation'),
        ':viriyah_agent_code' => p('viriyahAgentCode'),
        ':course_type' => p('courseType'),
        ':training_date' => p('trainingDate'),
        ':training_format' => p('trainingFormat'),
        ':deduction_privilege' => $deductionPrivilege,
        ':previous_courses' => $previousCourses,
        ':additional_course_requirement' => p('additionalCourseRequirement'),
        ':highest_education' => p('education'),
        ':occupation' => p('occupation'),
        ':branch_recommender' => p('branchRecommender'),
        ':insurance_experience_years' => p('insuranceExperienceYears'),
        ':sales_territories' => $salesTerritories,
        ':other_insurance_companies' => $otherInsuranceCompanies,
        ':has_experience' => p('hasExperience'),
        ':expectation' => p('expectation'),
        ':certify_true' => p('certifyTrue'),
    ]);

    echo json_encode(array('success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว'));

} catch (PDOException $e) {
    echo json_encode(array('success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()));
}
?>
