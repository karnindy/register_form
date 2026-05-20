
        let currentTab = 0;
        showTab(currentTab);

        function showTab(n) {
            let tabs = document.getElementsByClassName("tab");
            tabs[n].style.display = "block";

            // Buttons logic
            if (n == 0) {
                document.getElementById("prevBtn").style.display = "none";
            } else {
                document.getElementById("prevBtn").style.display = "flex";
            }

            if (n == (tabs.length - 1)) {
                document.getElementById("nextBtn").innerHTML = '<i class="fa-solid fa-paper-plane"></i> ส่งข้อมูลลงทะเบียน';
                document.getElementById("nextBtn").className = "btn btn-submit";
            } else {
                document.getElementById("nextBtn").innerHTML = 'ถัดไป <i class="fa-solid fa-arrow-right"></i>';
                document.getElementById("nextBtn").className = "btn btn-next";
            }

            if (n === 0) {
                checkPDPA();
            } else {
                document.getElementById("nextBtn").disabled = false;
                document.getElementById("nextBtn").style.opacity = "1";
                document.getElementById("nextBtn").style.cursor = "pointer";
            }

            updateStepIndicator(n);
        }

        function nextPrev(n) {
            let tabs = document.getElementsByClassName("tab");

            // Exit function if any field in the current tab is invalid
            if (n == 1 && !validateForm()) return false;

            // Hide current tab
            tabs[currentTab].style.display = "none";

            // Increase or decrease current tab
            currentTab = currentTab + n;

            // If reached the end of the form
            if (currentTab >= tabs.length) {
                submitForm();
                return false;
            }

            showTab(currentTab);
        }

        function toggleTitleNameOther() {
            let selectBox = document.getElementsByName("titleName")[0];
            let otherContainer = document.getElementById("titleNameOtherContainer");
            let otherInput = document.getElementById("titleNameOther");

            if (selectBox.value === "อื่นๆ") {
                otherContainer.style.display = "block";
                otherInput.setAttribute("required", "required");
            } else {
                otherContainer.style.display = "none";
                otherInput.removeAttribute("required");
                otherInput.value = "";
                otherInput.classList.remove("invalid");
            }
        }
        function toggleTitleNameOtherPrev() {
            let selectBox = document.getElementById("titleNamePrev");
            let otherContainer = document.getElementById("titleNameOtherContainerPrev");
            let otherInput = document.getElementById("titleNameOtherPrev");

            if (selectBox.value === "อื่นๆ") {
                otherContainer.style.display = "block";
                otherInput.setAttribute("required", "required");
            } else {
                otherContainer.style.display = "none";
                otherInput.removeAttribute("required");
                otherInput.value = "";
                otherInput.classList.remove("invalid");
            }
        }

        function togglePreviousName() {
            let radios = document.getElementsByName("hasChangedName");
            let prevSection = document.getElementById("previousNameSection");
            let reqFields = ["titleNamePrev", "firstNameThPrev", "lastNameThPrev", "firstNameEnPrev", "lastNameEnPrev"];
            let showPrev = false;

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked && radios[i].value === "yes") {
                    showPrev = true;
                    break;
                }
            }

            if (showPrev) {
                prevSection.style.display = "block";
                for (let id of reqFields) {
                    document.getElementById(id).setAttribute("required", "required");
                }
            } else {
                prevSection.style.display = "none";
                for (let id of reqFields) {
                    let el = document.getElementById(id);
                    el.removeAttribute("required");
                    el.value = "";
                    el.classList.remove("invalid");
                }
                let otherTitlePrevInput = document.getElementById("titleNameOtherPrev");
                otherTitlePrevInput.removeAttribute("required");
                otherTitlePrevInput.value = "";
                otherTitlePrevInput.classList.remove("invalid");
                document.getElementById("titleNameOtherContainerPrev").style.display = "none";
            }
        }

        function toggleAgentAffiliation() {
            let radios = document.getElementsByName("agentType");
            let agentSection = document.getElementById("agentAffiliationSection");
            let brokerSection = document.getElementById("brokerAffiliationSection");
            let agentReqFields = ["agentRegion", "agentBranch"];
            let brokerReqFields = ["viriyahAgentCode"];
            let isAgent = false;
            let isBroker = false;
            let selectedType = "";

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked) {
                    selectedType = radios[i].value;
                    if (selectedType === "ตัวแทนประกันวินาศภัย") {
                        isAgent = true;
                    } else if (selectedType === "นายหน้าประกันวินาศภัย") {
                        isBroker = true;
                    }
                    break;
                }
            }

            if (isAgent) {
                agentSection.style.display = "block";
                for (let id of agentReqFields) {
                    let el = document.getElementById(id);
                    if (el) el.setAttribute("required", "required");
                }
            } else {
                agentSection.style.display = "none";
                for (let id of agentReqFields) {
                    let el = document.getElementById(id);
                    if (el) {
                        el.removeAttribute("required");
                        el.value = "";
                        el.classList.remove("invalid");
                    }
                }
            }

            if (isBroker) {
                brokerSection.style.display = "block";
                for (let id of brokerReqFields) {
                    let el = document.getElementById(id);
                    if (el) el.setAttribute("required", "required");
                }
            } else {
                brokerSection.style.display = "none";
                for (let id of brokerReqFields) {
                    let el = document.getElementById(id);
                    if (el) {
                        el.removeAttribute("required");
                        el.value = "";
                        el.classList.remove("invalid");
                    }
                }
                let brokerAffiliation = document.getElementById("brokerAffiliation");
                if (brokerAffiliation) {
                    brokerAffiliation.value = "";
                }
            }

            updateCourseTypeOptions(selectedType);
            updateLicenseStatusOptions(selectedType);
        }

        function updateLicenseStatusOptions(selectedType) {
            let licenseSelect = document.getElementsByName("licenseStatus")[0];
            if (!licenseSelect) return;
            
            // If the user already selected something, try to keep it if it's still valid
            let currentValue = licenseSelect.value;
            
            licenseSelect.innerHTML = '<option value="">- เลือกสถานะใบอนุญาต -</option>';
            
            if (selectedType === "ตัวแทนประกันวินาศภัย") {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            } else if (selectedType === "นายหน้าประกันวินาศภัย") {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            } else {
                licenseSelect.innerHTML += '<option value="ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ">ไม่มีใบอนุญาต/ใบอนุญาตขาดต่อ</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น ตัวแทน ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 1</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 2</option>';
                licenseSelect.innerHTML += '<option value="ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3">ใบอนุญาตเป็น นายหน้า ประกันวินาศภัย ครั้งที่ 3</option>';
                licenseSelect.innerHTML += '<option value="ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป">ขอต่ออายุใบอนุญาตเป็น ตัวแทนหรือนายหน้า ประกันวินาศภัย ครั้งที่ 4 เป็นต้นไป</option>';
            }
            
            // Restore selection if it still exists in the new options
            if (currentValue) {
                let optionExists = Array.from(licenseSelect.options).some(opt => opt.value === currentValue);
                if (optionExists) {
                    licenseSelect.value = currentValue;
                }
            }
        }

        const courseScheduleData = {
            "ตัวแทนประกันวินาศภัย": {
                "ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย": ["18 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1": ["25 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 2": ["2 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 3": ["9 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4": [
                    "[Pillar 1] [22 เมษายน 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [22 เมษายน 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [22 เมษายน 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [13 พฤษภาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [13 พฤษภาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [13 พฤษภาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [20 พฤษภาคม 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [20 พฤษภาคม 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 1] [20 พฤษภาคม 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 3] [20 พฤษภาคม 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 3] [10 มิถุนายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 2] [10 มิถุนายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [10 มิถุนายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 1] [10 มิถุนายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [5 สิงหาคม 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [5 สิงหาคม 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [5 สิงหาคม 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [19 สิงหาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [26 สิงหาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [26 สิงหาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [26 สิงหาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [2 กันยายน 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 1] [2 กันยายน 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [2 กันยายน 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 3] [2 กันยายน 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 2] [9 กันยายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [9 กันยายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [9 กันยายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 3] [9 กันยายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย"
                ]
            },
            "นายหน้าประกันวินาศภัย": {
                "ขอรับใบอนุญาตเป็นนายหน้าประกันวินาศภัย": ["17 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 1": ["24 มิถุนายน 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 2": ["1 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตนายหน้าประกันวินาศภัย 3": ["8 กรกฎาคม 2569"],
                "ขอต่อใบอนุญาตตัวแทน/นายหน้าประกันวินาศภัย 4": [
                    "[Pillar 1] [22 เมษายน 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [22 เมษายน 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [22 เมษายน 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [6 พฤษภาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [13 พฤษภาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [13 พฤษภาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [13 พฤษภาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [20 พฤษภาคม 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [20 พฤษภาคม 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 1] [20 พฤษภาคม 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 3] [20 พฤษภาคม 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 3] [10 มิถุนายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 2] [10 มิถุนายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [10 มิถุนายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 1] [10 มิถุนายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [5 สิงหาคม 2569] : การประกันความเสี่ยงภัยทรัพย์สิน",
                    "[Pillar 1] [5 สิงหาคม 2569] : การกำกับดูแลบริษัทประกันภัยตามระดับความเสี่ยง",
                    "[Pillar 1] [5 สิงหาคม 2569] : การจัดการสินไหมทดแทน Non-Motor",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนเพื่อวัยเกษียณ",
                    "[Pillar 1] [19 สิงหาคม 2569] : การวางแผนภาษีสำหรับตัวแทนและนายหน้าประกันภัย",
                    "[Pillar 1] [19 สิงหาคม 2569] : การประกันภัยต่อ",
                    "[Pillar 3] [26 สิงหาคม 2569] : จรรยาบรรณและศีลธรรมของตัวแทน/นายหน้าประกันภัย",
                    "[Pillar 3] [26 สิงหาคม 2569] : พ.ร.บ. จราจรทางบก พ.ศ.2522 (แก้ไขเพิ่มเติม2562) และการพิจารณาคดีแพ่ง/อาญาเมื่อเกิดอุบัติเหตุจราจร",
                    "[Pillar 3] [26 สิงหาคม 2569] : หัวข้อการบรรยาย : พระราชบัญญัติคุ้มครองข้อมูลส่วนบุคคล",
                    "[Pillar 1] [2 กันยายน 2569] : กรมธรรม์ประกันภัยรถยนต์ไฟฟ้ารวมการคุ้มครองผู้ประสบภัยจากรถ",
                    "[Pillar 1] [2 กันยายน 2569] : เสนอขายถูกหลักประกันภัยเติบโต",
                    "[Pillar 3] [2 กันยายน 2569] : กฏหมายว่าด้วยการป้องกันและปราบปรามการฟอกเงินและต่อต้านการสนับสนุนทางการเงินแก่การก่อการร้าย",
                    "[Pillar 3] [2 กันยายน 2569] : พ.ร.บ.การทวงถามหนี้",
                    "[Pillar 2] [9 กันยายน 2569] : การตลาดยุคใหม่",
                    "[Pillar 1] [9 กันยายน 2569] : การพิจารณารับประกันภัยรถยนต์",
                    "[Pillar 1] [9 กันยายน 2569] : รู้จักประกันภัยสุขภาพ",
                    "[Pillar 3] [9 กันยายน 2569] : ความเสี่ยงต่อความรับผิดในฐานะตัวแทน/นายหน้าประกันภัย"
                ]
            }
        };

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

            if (!selectedType || !courseScheduleData[selectedType]) {
                courseGroup.style.display = "none";
                return;
            }

            courseGroup.style.display = "block";
            let roleLabel = selectedType === "ตัวแทนประกันวินาศภัย" ? "[ตัวแทน]" : "[นายหน้า]";
            label.textContent = `ระดับขอต่อ ${roleLabel}`;

            let courses = Object.keys(courseScheduleData[selectedType]);
            
            courses.forEach((course) => {
                let labelEl = document.createElement("label");
                labelEl.className = "radio-item custom-radio";
                
                let input = document.createElement("input");
                input.type = "radio";
                input.name = "courseTypeUI";
                input.value = course;
                input.required = true;
                
                input.addEventListener("change", function() {
                    hiddenInput.value = this.value;
                    renderTrainingDates(selectedType, this.value);
                });

                labelEl.appendChild(input);
                labelEl.appendChild(document.createTextNode(" " + course));
                container.appendChild(labelEl);
            });
        }

        function renderTrainingDates(agentType, courseType) {
            let dateGroup = document.getElementById("trainingDateGroup");
            let container = document.getElementById("trainingDateContainer");
            let label = document.getElementById("trainingDateLabel");
            
            container.innerHTML = "";
            
            let dataList = courseScheduleData[agentType][courseType];
            if (!dataList || dataList.length === 0) {
                dateGroup.style.display = "none";
                return;
            }

            dateGroup.style.display = "block";
            label.textContent = courseType; // Match screenshot: the label is the course type
            
            let isMultiple = courseType.includes("ครั้งที่ 4") || courseType.includes("4 เป็นต้นไป") || courseType.endsWith(" 4");
            let inputType = isMultiple ? "checkbox" : "radio";
            
            dataList.forEach((item) => {
                let labelEl = document.createElement("label");
                labelEl.className = "radio-item custom-radio";
                
                let input = document.createElement("input");
                input.type = inputType;
                input.name = isMultiple ? "trainingDate[]" : "trainingDate";
                input.value = item;
                if (!isMultiple) input.required = true;
                
                labelEl.appendChild(input);
                labelEl.appendChild(document.createTextNode(" " + item));
                container.appendChild(labelEl);
            });
            
            if (isMultiple) {
                // Add an asterisk note for multiple checkboxes
                let note = document.createElement("span");
                note.style.color = "var(--error-color)";
                note.style.fontSize = "14px";
                note.textContent = " * สามารถเลือกได้มากกว่า 1 หัวข้อ";
                label.appendChild(note);
            }
        }

        function checkPDPA() {
            let pdpaRadios = document.getElementsByName('pdpaConsent');
            let nextBtn = document.getElementById("nextBtn");
            let isAccepted = false;

            for (let i = 0; i < pdpaRadios.length; i++) {
                if (pdpaRadios[i].checked && pdpaRadios[i].value === 'accept') {
                    isAccepted = true;
                }
            }

            if (currentTab === 0) {
                if (!isAccepted) {
                    nextBtn.disabled = true;
                    nextBtn.style.opacity = "0.5";
                    nextBtn.style.cursor = "not-allowed";
                } else {
                    nextBtn.disabled = false;
                    nextBtn.style.opacity = "1";
                    nextBtn.style.cursor = "pointer";
                }
            }
        }

        function validateForm() {
            let valid = true;
            let tabs = document.getElementsByClassName("tab");
            let inputs = tabs[currentTab].querySelectorAll("input[required], select[required], textarea[required]");

            // Special check for PDPA radio buttons in Step 1
            if (currentTab === 0) {
                let pdpaRadios = document.getElementsByName('pdpaConsent');
                let isChecked = false;
                let isAccepted = false;
                for (let i = 0; i < pdpaRadios.length; i++) {
                    if (pdpaRadios[i].checked) {
                        isChecked = true;
                        if (pdpaRadios[i].value === 'accept') {
                            isAccepted = true;
                        }
                    }
                }

                if (!isChecked || !isAccepted) {
                    alert("กรุณายอมรับนโยบายความเป็นส่วนตัวเพื่อดำเนินการต่อ");
                    valid = false;
                    return valid;
                }
            }

            // Normal validation
            for (let i = 0; i < inputs.length; i++) {
                if (inputs[i].type === "radio" || inputs[i].type === "checkbox") {
                    let group = document.getElementsByName(inputs[i].name);
                    let checked = false;
                    for (let j = 0; j < group.length; j++) {
                        if (group[j].checked) {
                            checked = true;
                            break;
                        }
                    }
                    if (!checked) {
                        let container = inputs[i].closest('.radio-group') || inputs[i].closest('.radio-item');
                        if (container) container.classList.add("invalid");
                        valid = false;
                    } else {
                        let container = inputs[i].closest('.radio-group') || inputs[i].closest('.radio-item');
                        if (container) container.classList.remove("invalid");
                    }
                } else if (inputs[i].value.trim() === "") {
                    inputs[i].classList.add("invalid");
                    valid = false;
                } else {
                    inputs[i].classList.remove("invalid");
                }
            }

            // Mark step as finished
            if (valid) {
                document.getElementsByClassName("step-dot")[currentTab].className += " finish";
            }
            return valid;
        }

        // === Auto-clear invalid (red border) when user fills data ===
        function attachClearInvalid() {
            var els = document.querySelectorAll('input, select, textarea');
            for (var i = 0; i < els.length; i++) {
                // Remove any previous listener to avoid duplicates
                els[i].removeEventListener('input', clearInvalidHandler);
                els[i].removeEventListener('change', clearInvalidHandler);
                // Attach fresh
                els[i].addEventListener('input', clearInvalidHandler);
                els[i].addEventListener('change', clearInvalidHandler);
            }
        }
        function clearInvalidHandler(e) {
            var el = e.target || e.srcElement || this;
            if (el && el.className && el.className.indexOf('invalid') !== -1) {
                el.className = el.className.replace(/\binvalid\b/g, '').trim();
            }
        }
        // Attach on page load
        attachClearInvalid();
        // Re-attach after any click (covers post-validation scenarios)
        document.addEventListener('click', function() {
            setTimeout(attachClearInvalid, 100);
        });

        // Polling fallback: auto-clear invalid from fields that have values
        window.setInterval(function () {
            var all = document.querySelectorAll('.invalid');
            for (var i = 0; i < all.length; i++) {
                var el = all[i];
                if (el && el.value !== undefined && el.value !== null && String(el.value).trim() !== '') {
                    el.className = el.className.replace(/\binvalid\b/g, '').trim();
                }
            }
        }, 300);

        function updateStepIndicator(n) {
            let dots = document.getElementsByClassName("step-dot");
            for (let i = 0; i < dots.length; i++) {
                dots[i].className = dots[i].className.replace(" active", "");
            }
            dots[n].className += " active";
        }

        function formatDateText(input) {
            let v = input.value.replace(/\D/g, '');
            if (v.length > 8) v = v.substring(0, 8);
            let out = '';
            if (v.length > 0) out += v.substring(0, 2);
            if (v.length > 2) out += '/' + v.substring(2, 4);
            if (v.length > 4) out += '/' + v.substring(4, 8);
            input.value = out;
        }

        function formatIdCard(input) {
            let value = input.value.replace(/\D/g, '');
            let formatted = '';

            if (value.length > 0) formatted += value.substring(0, 1);
            if (value.length > 1) formatted += '-' + value.substring(1, 5);
            if (value.length > 5) formatted += '-' + value.substring(5, 10);
            if (value.length > 10) formatted += '-' + value.substring(10, 12);
            if (value.length > 12) formatted += '-' + value.substring(12, 13);

            input.value = formatted;
        }

        function formatPhone(input) {
            let value = input.value.replace(/\D/g, '');
            let formatted = '';

            if (value.length > 0) formatted += value.substring(0, 3);
            if (value.length > 3) formatted += '-' + value.substring(3, 6);
            if (value.length > 6) formatted += '-' + value.substring(6, 10);

            input.value = formatted;
        }

        function toggleShippingAddress() {
            const radios = document.getElementsByName('shippingAddress');
            const section = document.getElementById('shippingAddressSection');
            const shipFields = ['shipHouseNo', 'shipSubDistrict', 'shipDistrict', 'shipProvince', 'shipZipcode'];
            let showShipping = false;

            for (let i = 0; i < radios.length; i++) {
                if (radios[i].checked && radios[i].value === 'different') {
                    showShipping = true;
                    break;
                }
            }

            if (showShipping) {
                section.style.display = 'block';
                section.classList.add('reveal');
                for (let id of shipFields) {
                    document.getElementById(id).setAttribute('required', 'required');
                }
            } else {
                section.style.display = 'none';
                section.classList.remove('reveal');
                for (let id of shipFields) {
                    let el = document.getElementById(id);
                    el.removeAttribute('required');
                    el.value = '';
                    el.classList.remove('invalid');
                }
                // Also clear non-required shipping fields
                ['shipMoo', 'shipVillage', 'shipSoi', 'shipRoad'].forEach(id => {
                    document.getElementById(id).value = '';
                });
            }
        }

        function submitForm() {
            // Validation on final step
            if (!validateForm()) return;

            // Actually submit the form via POST
            let form = document.getElementById('regForm');
            // Remove the onsubmit preventDefault so form can submit
            form.onsubmit = null;
            form.submit();
        }

        // ===== Relational Address Data System =====
        let rawProvinces = [];
        let rawDistricts = [];
        let rawSubDistricts = [];

        async function loadAddressData() {
            try {
                const [provRes, distRes, subRes] = await Promise.all([
                    fetch('provinces.json'),
                    fetch('districts.json'),
                    fetch('sub_districts.json')
                ]);
                const provData = await provRes.json();
                const distData = await distRes.json();
                const subData = await subRes.json();

                rawProvinces = provData.provinces || [];
                rawDistricts = distData.districts || [];
                rawSubDistricts = subData.sub_districts || [];
            } catch (err) {
                console.error("Error loading address data:", err);
            }
        }
        
        // Load data on page load
        document.addEventListener('DOMContentLoaded', loadAddressData);

        // ===== Address Autocomplete System =====
        function getFieldId(field, prefix) {
            if (!prefix) return field; // province, district, subDistrict
            // ship prefix: shipProvince, shipDistrict, shipSubDistrict
            return prefix + field.charAt(0).toUpperCase() + field.slice(1);
        }

        function acSearch(input, type, prefix) {
            var listId = getFieldId(type, prefix) + '_list';
            var listEl = document.getElementById(listId);
            var query = input.value.trim().toLowerCase();
            var items = [];

            if (type === 'province') {
                items = rawProvinces.map(p => ({
                    id: p.PROVINCE_ID,
                    text: p.PROVINCE_THAI
                }));
            } else if (type === 'district') {
                var provName = document.getElementById(getFieldId('province', prefix)).value;
                var provObj = rawProvinces.find(p => p.PROVINCE_THAI === provName);
                if (provObj) {
                    items = rawDistricts
                        .filter(d => d.PROVINCE_ID === provObj.PROVINCE_ID)
                        .map(d => ({
                            id: d.DISTRICT_ID,
                            text: d.DISTRICT_THAI
                        }));
                }
            } else if (type === 'subDistrict') {
                var distName = document.getElementById(getFieldId('district', prefix)).value;
                var distObj = rawDistricts.find(d => d.DISTRICT_THAI === distName);
                if (distObj) {
                    items = rawSubDistricts
                        .filter(s => s.DISTRICT_ID === distObj.DISTRICT_ID)
                        .map(s => ({
                            id: s.SUB_DISTRICT_ID,
                            text: s.SUB_DISTRICT_THAI,
                            postcode: s.POSTAL_CODE
                        }));
                }
            }

            if (query) {
                items = items.filter(i => i.text.toLowerCase().indexOf(query) !== -1);
            }

            listEl.innerHTML = '';
            if (items.length === 0) { listEl.classList.remove('show'); return; }
            items.slice(0, 30).forEach(function(itemObj) {
                var val = itemObj.text;
                var div = document.createElement('div');
                div.className = 'autocomplete-item';
                div.setAttribute('data-val', val);
                if (itemObj.postcode) div.setAttribute('data-postcode', itemObj.postcode);
                
                if (query) {
                    var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    div.innerHTML = val.replace(regex, '<mark style="background:rgba(228,160,37,0.25);padding:0;border-radius:2px;">$1</mark>');
                } else {
                    div.textContent = val;
                }
                div.onmousedown = function(e) { 
                    e.preventDefault(); 
                    acSelect(val, type, prefix, itemObj.postcode); 
                };
                listEl.appendChild(div);
            });
            listEl.classList.add('show');
        }

        function acSelect(value, type, prefix, postcode) {
            var input = document.getElementById(getFieldId(type, prefix));
            input.value = value;
            document.getElementById(getFieldId(type, prefix) + '_list').classList.remove('show');
            input.classList.remove('invalid');

            // Cascade: clear child fields and set zip code if applicable
            var zipField = prefix ? 'shipZipcode' : 'zipcode';
            
            if (type === 'province') {
                var distInput = document.getElementById(getFieldId('district', prefix));
                var subInput = document.getElementById(getFieldId('subDistrict', prefix));
                distInput.value = ''; subInput.value = '';
                document.getElementById(zipField).value = '';
            } else if (type === 'district') {
                document.getElementById(getFieldId('subDistrict', prefix)).value = '';
                document.getElementById(zipField).value = '';
            } else if (type === 'subDistrict') {
                var zipInput = document.getElementById(zipField);
                if (postcode) {
                    zipInput.value = postcode;
                }
                zipInput.classList.remove('invalid');
            }
        }

        // Keyboard navigation for Address autocomplete
        ['province', 'district', 'subDistrict', 'shipProvince', 'shipDistrict', 'shipSubDistrict'].forEach(function(fieldId) {
            var input = document.getElementById(fieldId);
            if (!input) return;
            var activeIdx = -1;

            input.addEventListener('keydown', function(e) {
                var listEl = document.getElementById(fieldId + '_list');
                var items = listEl.querySelectorAll('.autocomplete-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var selectedIdx = activeIdx >= 0 ? activeIdx : 0;
                    if (items[selectedIdx]) {
                        var isShip = fieldId.startsWith('ship');
                        var type = fieldId;
                        if (isShip) {
                            if (fieldId === 'shipProvince') type = 'province';
                            if (fieldId === 'shipDistrict') type = 'district';
                            if (fieldId === 'shipSubDistrict') type = 'subDistrict';
                        }
                        var prefix = isShip ? 'ship' : '';
                        var val = items[selectedIdx].getAttribute('data-val');
                        var postcode = items[selectedIdx].getAttribute('data-postcode');
                        acSelect(val, type, prefix, postcode);
                    }
                    activeIdx = -1;
                    return;
                } else if (e.key === 'Escape') {
                    listEl.classList.remove('show');
                    activeIdx = -1;
                    return;
                } else {
                    activeIdx = -1;
                    return;
                }

                items.forEach(function(i) { i.classList.remove('active'); });
                if (items[activeIdx]) {
                    items[activeIdx].classList.add('active');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(function() {
                    var listEl = document.getElementById(fieldId + '_list');
                    if (listEl) listEl.classList.remove('show');
                    activeIdx = -1;
                }, 150);
            });
        });

        // Close all autocomplete lists when clicking outside
        document.addEventListener('click', function(e) {
            if (e.target.closest('.autocomplete-wrapper')) return;
            var lists = document.querySelectorAll('.autocomplete-list');
            lists.forEach(function(l) { l.classList.remove('show'); });
        });

        // ===== Autocomplete: สังกัดภาค & สาขา =====
        var agentRegionMap = {
            'ภาค 1 (ภาคเหนือ)': ['เชียงราย','เชียงใหม่','นครสวรรค์','พิษณุโลก'],
            'ภาค 2 (ภาคตะวันออกเฉียงเหนือ)': ['ขอนแก่น','นครราชสีมา','อุดรธานี','อุบลราชธานี'],
            'ภาค 3 (ภาคตะวันออก)': ['จันทบุรี','ฉะเชิงเทรา','พัทยา','ระยอง'],
            'ภาค 4 (ภาคกลางและภาคตะวันตก)': ['นครปฐม','พระนครศรีอยุธยา','สมุทรสาคร','สระบุรี'],
            'ภาค 5 (ภาคใต้)': ['กระบี่','นครศรีธรรมราช','ภูเก็ต','สุราษฎร์ธานี','หาดใหญ่'],
            'ภาค 6 (ภาคกรุงเทพฯ)': ['กรุงเกษม','ดอนเมือง','บางนา','บางพลัด','ปู่เจ้าสมิงพราย','พระราม 2','ปากเกร็ด-345','รัชดาภิเษก','ลุมพินี','วงศ์สว่าง','วิภาวดี','สุขสวัสดิ์','สุขาภิบาล 3','กิจกรรพิเศษ1','กิจกรรพิเศษ2']
        };

        var allBranches = [];
        var branchToRegion = {};
        for (var region in agentRegionMap) {
            agentRegionMap[region].forEach(function(branch) {
                allBranches.push(branch);
                branchToRegion[branch] = region;
            });
        }

        var agentAcData = {
            agentBranch: allBranches
        };

        function agentAcSearch(fieldId) {
            if (fieldId !== 'agentBranch') return;
            var input = document.getElementById(fieldId);
            var listEl = document.getElementById(fieldId + '_list');
            var query = input.value.trim().toLowerCase();
            var options = agentAcData[fieldId] || [];
            var filtered = query
                ? options.filter(function(o) { return o.toLowerCase().indexOf(query) !== -1; })
                : options;

            listEl.innerHTML = '';
            if (filtered.length === 0) { listEl.classList.remove('show'); return; }

            filtered.forEach(function(val) {
                var div = document.createElement('div');
                div.className = 'autocomplete-item';
                if (query) {
                    var regex = new RegExp('(' + query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    div.innerHTML = val.replace(regex, '<mark style="background:rgba(228,160,37,0.25);padding:0;border-radius:2px;">$1</mark>');
                } else {
                    div.textContent = val;
                }
                div.onmousedown = function(e) {
                    e.preventDefault();
                    input.value = val;
                    listEl.classList.remove('show');
                    input.classList.remove('invalid');
                    
                    var regionInput = document.getElementById('agentRegion');
                    if (regionInput && branchToRegion[val]) {
                        regionInput.value = branchToRegion[val];
                        regionInput.classList.remove('invalid');
                    }
                };
                listEl.appendChild(div);
            });
            listEl.classList.add('show');
        }

        // Keyboard navigation for agent autocomplete
        ['agentBranch'].forEach(function(fieldId) {
            var input = document.getElementById(fieldId);
            if (!input) return;
            var activeIdx = -1;

            input.addEventListener('keydown', function(e) {
                var listEl = document.getElementById(fieldId + '_list');
                var items = listEl.querySelectorAll('.autocomplete-item');
                if (!items.length) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIdx = Math.min(activeIdx + 1, items.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIdx = Math.max(activeIdx - 1, 0);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    var selectedIdx = activeIdx >= 0 ? activeIdx : 0;
                    if (items[selectedIdx]) {
                        var val = items[selectedIdx].textContent;
                        input.value = val;
                        listEl.classList.remove('show');
                        input.classList.remove('invalid');
                        
                        var regionInput = document.getElementById('agentRegion');
                        if (regionInput && branchToRegion[val]) {
                            regionInput.value = branchToRegion[val];
                            regionInput.classList.remove('invalid');
                        }
                    }
                    activeIdx = -1;
                    return;
                } else if (e.key === 'Escape') {
                    listEl.classList.remove('show');
                    activeIdx = -1;
                    return;
                } else {
                    activeIdx = -1;
                    return;
                }

                items.forEach(function(i) { i.classList.remove('active'); });
                if (items[activeIdx]) {
                    items[activeIdx].classList.add('active');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                }
            });

            input.addEventListener('blur', function() {
                setTimeout(function() {
                    document.getElementById(fieldId + '_list').classList.remove('show');
                    activeIdx = -1;
                }, 150);
            });
            
            input.addEventListener('input', function(e) {
                var val = input.value.trim();
                var regionInput = document.getElementById('agentRegion');
                if (regionInput) {
                    if (branchToRegion[val]) {
                        regionInput.value = branchToRegion[val];
                        regionInput.classList.remove('invalid');
                    } else {
                        regionInput.value = '';
                    }
                }
            });
        });
    



        document.addEventListener('DOMContentLoaded', function() {
            flatpickr(".datepicker", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true
            });
        });
    
