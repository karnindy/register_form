<?php
$f = 'index.php';
$c = file_get_contents($f);

// 1. We replace updateCourseTypeOptions
$pattern1 = '/function updateCourseTypeOptions\(selectedType\)\s*\{.*?\}(?=\s*function renderTrainingDates)/is';
$replacement1 = <<<'EOF'
function updateCourseTypeOptions(selectedType) {
    let courseGroup = document.getElementById("courseTypeGroup");
    let dateGroup = document.getElementById("trainingDateGroup");
    let container = document.getElementById("courseTypeContainer");
    let label = document.getElementById("courseTypeLabel");
    let hiddenInput = document.getElementById("courseTypeHidden");

    // Reset
    container.innerHTML = "";
    hiddenInput.value = "";
    dateGroup.style.display = "none";
    let previousCoursesSection = document.getElementById("previousCoursesSection");
    if (previousCoursesSection) {
        previousCoursesSection.style.display = "none";
        let checkboxes = previousCoursesSection.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => { cb.checked = false; });
    }

    let deductionGroup = document.getElementById("deductionPrivilegeGroup");
    if (deductionGroup) {
        deductionGroup.style.display = "none";
        let checkboxes = deductionGroup.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => { cb.checked = false; });
        if (typeof toggleMasterDegreeRadios === 'function') toggleMasterDegreeRadios();
    }

    let baseType = (selectedType && selectedType.includes("นายหน้า")) ? "นายหน้าประกันวินาศภัย" : "ตัวแทนประกันวินาศภัย";

    if (!selectedType || !courseScheduleData[baseType]) {
        courseGroup.style.display = "none";
        return;
    }

    courseGroup.style.display = "block";
    label.textContent = "หลักสูตร";

    let courses = courseScheduleData[baseType];
    
    for (let courseId in courses) {
        let course = courses[courseId];
        let labelEl = document.createElement("label");
        labelEl.className = "radio-item custom-radio";
        
        let input = document.createElement("input");
        input.type = "radio";
        input.name = "courseTypeUI";
        input.value = courseId; // ID
        input.required = true;
        input.dataset.name = course.name;

        // Restore if matches
        <?php if (isset($formData['courseType'])): ?>
            if ("<?php echo $formData['courseType']; ?>" === courseId || "<?php echo $formData['courseType']; ?>" === course.name) {
                input.checked = true;
            }
        <?php endif; ?>
        
        input.addEventListener("change", function() {
            hiddenInput.value = this.value; // Store ID
            renderTrainingDates(selectedType, this.value);

            let deductionGroup = document.getElementById("deductionPrivilegeGroup");
            if (deductionGroup) {
                if (course.is_complex || course.name.includes("4 เป็นต้นไป")) {
                    deductionGroup.style.display = "block";
                } else {
                    deductionGroup.style.display = "none";
                    let checkboxes = deductionGroup.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => { cb.checked = false; });
                    if (typeof toggleMasterDegreeRadios === 'function') toggleMasterDegreeRadios();
                }
            }

            let previousCoursesSection = document.getElementById("previousCoursesSection");
            if (previousCoursesSection) {
                if (course.is_complex || course.name.includes("4 เป็นต้นไป")) {
                    previousCoursesSection.style.display = "block";
                } else {
                    previousCoursesSection.style.display = "none";
                    let checkboxes = previousCoursesSection.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(cb => { cb.checked = false; });
                }
            }
        });

        labelEl.appendChild(input);
        labelEl.appendChild(document.createTextNode(" " + course.name));
        container.appendChild(labelEl);
    }
}
EOF;
$c = preg_replace($pattern1, $replacement1 . "\n\n", $c, 1);

// 2. We replace renderTrainingDates
$pattern2 = '/function renderTrainingDates\(agentType, courseType\)\s*\{.*?\}(?=\s*function validateTab1)/is';
$replacement2 = <<<'EOF'
function renderTrainingDates(agentType, courseId) {
    let dateGroup = document.getElementById("trainingDateGroup");
    let container = document.getElementById("trainingDateContainer");
    let label = document.getElementById("trainingDateLabel");
    
    container.innerHTML = "";
    
    let baseType = (agentType && agentType.includes("นายหน้า")) ? "นายหน้าประกันวินาศภัย" : "ตัวแทนประกันวินาศภัย";
    let course = courseScheduleData[baseType][courseId];

    if (!course || !course.dates || course.dates.length === 0) {
        dateGroup.style.display = "none";
        return;
    }

    dateGroup.style.display = "block";
    label.textContent = course.name; 
    
    let isMultiple = course.is_complex || course.name.includes("4 เป็นต้นไป");
    let inputType = isMultiple ? "checkbox" : "radio";
    
    course.dates.forEach((dateStr) => {
        let labelEl = document.createElement("label");
        labelEl.className = "radio-item " + (isMultiple ? "custom-checkbox" : "custom-radio");
        
        let input = document.createElement("input");
        input.type = inputType;
        input.name = "trainingDate[]";
        input.value = dateStr;
        if (!isMultiple) input.required = true;

        <?php if (isset($formData['trainingDate'])): ?>
            let selectedDates = <?php echo json_encode(is_array($formData['trainingDate']) ? $formData['trainingDate'] : [$formData['trainingDate']]); ?>;
            if (selectedDates.includes(dateStr)) {
                input.checked = true;
            }
        <?php endif; ?>

        if (isMultiple) {
            input.addEventListener("change", function() {
                let checkedCount = container.querySelectorAll('input[type="checkbox"]:checked').length;
                if (checkedCount > 5) {
                    alert("คุณสามารถเลือกได้สูงสุด 5 วิชา");
                    this.checked = false;
                }
            });
        }
        
        labelEl.appendChild(input);
        labelEl.appendChild(document.createTextNode(" " + dateStr));
        container.appendChild(labelEl);
    });
}
EOF;
$c = preg_replace($pattern2, $replacement2 . "\n\n", $c, 1);

file_put_contents($f, $c);
echo "Done\n";
