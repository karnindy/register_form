<?php
$prov = json_decode(file_get_contents('provinces.json'), true)['provinces'][0];
$dist = json_decode(file_get_contents('districts.json'), true)['districts'][0];
$sub = json_decode(file_get_contents('sub_districts.json'), true)['sub_districts'][0];
print_r($prov);
print_r($dist);
print_r($sub);
