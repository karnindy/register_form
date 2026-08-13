<?php
$f = 'index.php';
$c = file_get_contents($f);

// Let's replace the whole renderTrainingDates function
$pattern = '/function renderTrainingDates\(agentType, courseType\)\s*\{[\s\S]*?(?=\s*function checkPDPA\(\))/';

$replacement = <<<'EOF'
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
        input.name = isMultiple ? "trainingDate[]" : "trainingDate";
        input.value = dateStr;
        if (!isMultiple) input.required = true;

        <?php if (isset($formData['trainingDate'])): ?>
            let selectedDates = <?php echo json_encode(is_array($formData['trainingDate']) ? $formData['trainingDate'] : [$formData['trainingDate']]); ?>;
            if (selectedDates.includes(dateStr)) {
                input.checked = true;
            }
        <?php endif; ?>

        input.addEventListener('change', function() {
            container.classList.remove('invalid');
            removeErrorMsg(container);
            let items = container.querySelectorAll('.radio-item');
            items.forEach(el => {
                el.classList.remove('invalid');
                removeErrorMsg(el);
            });
            if (isMultiple) {
                let checkedCount = container.querySelectorAll('input[type="checkbox"]:checked').length;
                if (checkedCount > 5) {
                    alert("คุณสามารถเลือกได้สูงสุด 5 วิชา");
                    this.checked = false;
                }
            }
        });
        
        labelEl.appendChild(input);
        labelEl.appendChild(document.createTextNode(" " + dateStr));
        container.appendChild(labelEl);
    });

    let oldNote = document.getElementById("trainingNoteMsg");
    if (oldNote) oldNote.remove();

    if (isMultiple) {
        let note = document.createElement("div");
        note.id = "trainingNoteMsg";
        note.style.marginTop = "5px";
        note.style.marginBottom = "10px";
        note.innerHTML = "<span style='color: var(--error-color); font-size: 14px;'>* เลือกได้มากกว่า 1 วิชา *</span><br><span style='color: var(--error-color); font-size: 14px; font-weight: normal;'>* หากเลือกวิชาที่เคยอบรม จะไม่นับรวมรอบปัจจุบัน (5 ปี) *</span>";
        label.insertAdjacentElement('afterend', note);
    }

    if (typeof updateDisabledTrainingDates === 'function') {
        updateDisabledTrainingDates();
    }
}
EOF;

$c = preg_replace($pattern, $replacement, $c, -1, $count);

if ($count > 0) {
    file_put_contents($f, $c);
    echo "Successfully replaced renderTrainingDates! ($count)\n";
} else {
    echo "Failed to replace! Regex didn't match.\n";
}
