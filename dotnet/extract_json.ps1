$code = Get-Content -Raw -Path 'D:\git_kraken\register_form\dotnet\backend\Services\OicApiService.cs'

$regex = '\"(\d{13})\"\s*=>\s*\"(\{.*?\})\"'
$matches = [regex]::Matches($code, $regex, [System.Text.RegularExpressions.RegexOptions]::Singleline)

$count = 0
foreach ($m in $matches) {
    $id = $m.Groups[1].Value
    $jsonStr = $m.Groups[2].Value
    # Skip if it is the mapped JSON (which has nationalId) instead of RAW JSON (which has cis_profile)
    if ($jsonStr -match "cis_profile") {
        $jsonStr = $jsonStr -replace '\\"', '"'
        $filePath = "D:\git_kraken\register_form\dotnet\backend\LocalData\Oic\$id.json"
        
        try {
            $formattedJson = $jsonStr | ConvertFrom-Json | ConvertTo-Json -Depth 10
            Set-Content -Path $filePath -Value $formattedJson -Encoding UTF8
            Write-Host "Created $filePath"
            $count++
        } catch {
            Write-Host "Failed to parse JSON for $id"
        }
    }
}
Write-Host "Total files created: $count"
