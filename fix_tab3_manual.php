<?php
function fixRemaining($f) {
    if (!file_exists($f)) return;
    $c = file_get_contents($f);
    
    if ($f === 'save_tab3.php') {
        $c = preg_replace('/\$shipRadio = p\(\'shippingAddress\', \'same\'\);\s*if \(\$shipRadio === \'same\'\) \{.*?\} else \{/s', 
            "\$shipRadio = p('shippingAddress', 'same');\nif (\$shipRadio === 'same') {\n    \$contactAddress = 'ตรงกับที่อยู่ตามทะเบียนบ้าน';\n} elseif (\$shipRadio === 'different') {\n    \$contactAddress = 'ที่อยู่อื่น ๆ (โปรดระบุ)';\n} else {", $c);
    }
    
    if ($f === 'save_tab5.php') {
        // Fix all the course types
        $c = preg_replace('/\$agentType = p\(\'agentType\'\); \/\/[^\n]+/', "\$agentType = p('agentType'); // \"ตัวแทนประกันวินาศภัย\" or \"นายหน้าประกันวินาศภัย\"", $c);
        $c = preg_replace('/\$isAgent = \(\$agentType === \'เธ•เธฑเธงเน เธ—เธ™เธ›เธฃเธฐเธ เธฑเธ™เธงเธดเธ™เธฒเธจเธ\?ัย\'\);/s', "\$isAgent = (\$agentType === 'ตัวแทนประกันวินาศภัย');", $c);
        // Sometimes the ? above is a weird character so use regex
        $c = preg_replace('/\$isAgent = \(\$agentType === \'[^\']+\'\);/', "\$isAgent = (\$agentType === 'ตัวแทนประกันวินาศภัย');", $c);
        $c = preg_replace('/\$dbCourseType = \$isAgent \? \'[^\']+\' : \'[^\']+\';/', "\$dbCourseType = \$isAgent ? 'ตัวแทน' : 'นายหน้า';", $c);

        $c = preg_replace('/if \(\$courseType === \'เธ‚เธญเธฃเธฑเธšเนƒเธšเธญเธ™เธธเธ เธฒเธ•เน€เธ›เน‡เธ™เธ•เธฑเธงเน เธ—เธ™เธ›เธฃเธฐเธ เธฑเธ™เธงเธดเธ™เธฒเธจเธ\?ัย\' \|\| \$courseType === \'เธ‚เธญเธฃเธฑเธšเธญเธ™เธธเธ เธฒเธ•เน€เธ›เน‡เธ™เธ•เธฑเธงเน เธ—เธ™เธ›เธฃเธฐเธ เธฑเธ™เธงเธดเธ™เธฒเธจเธ\?ัย\'\)/s', "if (\$courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || \$courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย')", $c);
        // Using a loop for the rest
        
        $c = preg_replace('/if \(\$courseType === \'[^\']+\' \|\| \$courseType === \'[^\']+\'\) \{\s*\$dbCourseType = [^;]+;\s*\} elseif \(\$courseType === \'[^\']+\' \|\| \$courseType === \'[^\']+\'\) \{/s', "if (\$courseType === 'ขอรับใบอนุญาตเป็นตัวแทนประกันวินาศภัย' || \$courseType === 'ขอรับอนุญาตเป็นตัวแทนประกันวินาศภัย') {\n    \$dbCourseType = 'ตัวแทน';\n} elseif (\$courseType === 'ขอต่อใบอนุญาตเป็นตัวแทนประกันวินาศภัย 1' || \$courseType === 'ขอต่อใบอนุญาตตัวแทนประกันวินาศภัย 1') {", $c);
        
        $c = preg_replace_callback('/elseif \(\$courseType === \'[^\']+\' \|\| \$courseType === \'[^\']+\'\)/', function($m) {
            return $m[0]; // Wait, I will just rewrite the whole if block for courseType because it's completely mojibake!
        }, $c);
    }
    
    file_put_contents($f, $c);
}

// Rewriting save_tab3.php manually!
$t3 = file_get_contents('save_tab3.php');
$t3 = preg_replace('/\$addrSoi         = p\(\'soi\'\);\s*\$contactVillage     = p\(\'shipVillage\'\);/s', "\$addrSoi         = p('soi');\n\$addrRoad        = p('road');\n\$addrProvince    = p('province');\n\$addrDistrict    = p('district');\n\$addrSubDistrict = p('subDistrict');\n\$addrPostcode    = p('zipcode');\n\n\$shipRadio = p('shippingAddress', 'same');\nif (\$shipRadio === 'same') {\n    \$contactAddress = 'ตรงกับที่อยู่ตามทะเบียนบ้าน';\n} elseif (\$shipRadio === 'different') {\n    \$contactAddress = 'ที่อยู่อื่น ๆ (โปรดระบุ)';\n} else {\n    \$contactAddress = \$shipRadio;\n}\n\n\$contactHouseNo     = p('shipHouseNo');\n\$contactMoo         = p('shipMoo');\n\$contactVillage     = p('shipVillage');", $t3);
file_put_contents('save_tab3.php', $t3);

echo "Fixed save_tab3.php\n";
