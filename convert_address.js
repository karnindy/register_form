// Convert flat geography JSON to nested province→district→subdistrict structure
const fs = require('fs');

// Read raw data (skip first 4 lines which are markdown header)
let raw = fs.readFileSync(process.argv[2], 'utf8');
// Find the start of JSON array
let jsonStart = raw.indexOf('[');
let data = JSON.parse(raw.substring(jsonStart));

// Build nested structure: { province: { district: [subdistrict, ...] } }
let result = {};
data.forEach(r => {
    let prov = r.provinceNameTh;
    let dist = r.districtNameTh;
    let sub = r.subdistrictNameTh;
    let zip = r.postalCode;
    
    if (!result[prov]) result[prov] = {};
    if (!result[prov][dist]) result[prov][dist] = [];
    result[prov][dist].push(sub);
});

// Output as JS variable
let output = 'var thaiAddressDB = ' + JSON.stringify(result, null, 0) + ';';
fs.writeFileSync(process.argv[3], output, 'utf8');

console.log('Provinces:', Object.keys(result).length);
let distCount = 0, subCount = 0;
Object.values(result).forEach(d => {
    distCount += Object.keys(d).length;
    Object.values(d).forEach(s => subCount += s.length);
});
console.log('Districts:', distCount);
console.log('Sub-districts:', subCount);
