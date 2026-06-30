<?php
$f = 'index.php';
$c = file_get_contents($f);

$pattern_data = '/const\s+courseScheduleData\s*=\s*\{.*?\};\s*/is';
$replacement_data = 'const courseScheduleData = <?php echo $courseScheduleJson; ?>' . ";\n";
$c = preg_replace($pattern_data, $replacement_data, $c, 1);

// Rewrite renderCourseTypes
$pattern_render = '/function renderCourseTypes\(courseType\)\s*\{(.*?)\}\s*function renderTrainingDates/is';
$replacement_render = <<<'EOF'
function renderCourseTypes(courseType) {
    let container = document.getElementById("courseTypeContainer");
    container.innerHTML = "";

    if (courseScheduleData[courseType]) {
        let courses = courseScheduleData[courseType];
        for (let courseId in courses) {
            let course = courses[courseId];

            let label = document.createElement("label");
            label.className = "radio-item custom-radio";
            label.style.cursor = "pointer";

            let input = document.createElement("input");
            input.type = "radio";
            input.name = "courseTypeUI";
            input.value = courseId;
            input.required = true;
            input.dataset.name = course.name;

            // Retain selected value
            <?php if (isset($formData['courseType'])): ?>
                if ("<?php echo $formData['courseType']; ?>" === courseId || "<?php echo $formData['courseType']; ?>" === course.name) {
                    input.checked = true;
                }
            <?php endif; ?>

            input.onchange = function () {
                document.getElementById("courseTypeHidden").value = courseId;
                renderTrainingDates(courseType, courseId);
                
                let p = document.getElementById("deductionPrivilegeGroup");
                if (course.is_complex || course.name.includes("4 เป็นต้นไป")) {
                    p.style.display = "block";
                } else {
                    p.style.display = "none";
                    document.querySelectorAll('input[name="deductionPrivilege[]"]').forEach(cb => cb.checked = false);
                    document.getElementById("masterDegreeRadios").style.display = "none";
                }

                let prev = document.getElementById("previousCoursesSection");
                if (course.is_complex || course.name.includes("4 เป็นต้นไป")) {
                    prev.style.display = "block";
                } else {
                    prev.style.display = "none";
                }
            };

            label.appendChild(input);
            label.appendChild(document.createTextNode(" " + course.name));
            container.appendChild(label);
        }
        document.getElementById("courseTypeGroup").style.display = "block";

        let checkedInput = container.querySelector('input[type="radio"]:checked');
        if (checkedInput) {
            checkedInput.onchange();
        }
    } else {
        document.getElementById("courseTypeGroup").style.display = "none";
        document.getElementById("trainingDateGroup").style.display = "none";
        document.getElementById("deductionPrivilegeGroup").style.display = "none";
        document.getElementById("previousCoursesSection").style.display = "none";
    }
}

function renderTrainingDates
EOF;

$c = preg_replace($pattern_render, $replacement_render, $c, 1);

// Rewrite renderTrainingDates
$pattern_dates = '/function renderTrainingDates\(courseType, courseName\)\s*\{(.*?)\}\s*document\.addEventListener/is';
$replacement_dates = <<<'EOF'
function renderTrainingDates(courseType, courseId) {
    let container = document.getElementById("trainingDateContainer");
    container.innerHTML = "";

    if (courseScheduleData[courseType] && courseScheduleData[courseType][courseId]) {
        let course = courseScheduleData[courseType][courseId];
        let dates = course.dates;

        if (course.is_complex || course.name.includes("4 เป็นต้นไป")) {
            // Complex structure (checkboxes)
            dates.forEach((dateStr) => {
                let label = document.createElement("label");
                label.className = "radio-item custom-checkbox";
                label.style.cursor = "pointer";

                let input = document.createElement("input");
                input.type = "checkbox";
                input.name = "trainingDate[]";
                input.value = dateStr;
                
                <?php if (isset($formData['trainingDate'])): ?>
                    let selectedDates = <?php echo json_encode(is_array($formData['trainingDate']) ? $formData['trainingDate'] : [$formData['trainingDate']]); ?>;
                    if (selectedDates.includes(dateStr)) {
                        input.checked = true;
                    }
                <?php endif; ?>

                input.onchange = function () {
                    if (document.querySelectorAll('input[name="trainingDate[]"]:checked').length > 5) {
                        alert("คุณสามารถเลือกได้สูงสุด 5 วิชา");
                        this.checked = false;
                    }
                };

                label.appendChild(input);
                label.appendChild(document.createTextNode(" " + dateStr));
                container.appendChild(label);
            });
            document.getElementById("trainingDateLabel").innerText = "เลือกวิชาอบรม (เลือก 5 วิชา)";
        } else {
            // Simple structure (radio)
            let dateStr = dates[0];
            let label = document.createElement("label");
            label.className = "radio-item custom-radio";
            label.style.cursor = "pointer";

            let input = document.createElement("input");
            input.type = "radio";
            input.name = "trainingDate[]";
            input.value = dateStr;
            input.required = true;
            
            <?php if (isset($formData['trainingDate'])): ?>
                let selectedDate = <?php echo json_encode(is_array($formData['trainingDate']) ? $formData['trainingDate'][0] : $formData['trainingDate']); ?>;
                if (selectedDate === dateStr) {
                    input.checked = true;
                }
            <?php endif; ?>

            label.appendChild(input);
            label.appendChild(document.createTextNode(" " + dateStr));
            container.appendChild(label);
            document.getElementById("trainingDateLabel").innerText = "เลือกรอบวันอบรม";
        }
        document.getElementById("trainingDateGroup").style.display = "block";
    }
}

document.addEventListener
EOF;

$c = preg_replace($pattern_dates, $replacement_dates, $c, 1);

file_put_contents($f, $c);
echo "Updated JS logic in index.php\n";
