<?php
$content = file_get_contents('admin/edit_trainee.php');
$search = "            const provData = await fetchWithFallback('../provinces.json');
            const distData = await fetchWithFallback('../districts.json');
            const subData = await fetchWithFallback('../sub_districts.json');

            rawProvinces = provData.provinces || [];
            rawDistricts = distData.districts || [];
            rawSubDistricts = subData.sub_districts || [];";

$replace = "            const provData = await fetchWithFallback('../api_locations.php?type=provinces');
            const distData = await fetchWithFallback('../api_locations.php?type=districts');
            const subData = await fetchWithFallback('../api_locations.php?type=sub_districts');

            rawProvinces = Array.isArray(provData) ? provData : (provData.provinces || []);
            rawDistricts = Array.isArray(distData) ? distData : (distData.districts || []);
            rawSubDistricts = Array.isArray(subData) ? subData : (subData.sub_districts || []);";

$content = str_replace($search, $replace, $content);
file_put_contents('admin/edit_trainee.php', $content);
echo "Done edit_trainee";
