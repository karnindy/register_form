
$filePath = (Get-ChildItem "d:\vib2\wordpressVerOld\new\*.xlsx" | Select-Object -First 1).FullName
Write-Host "Opening file: $filePath"
$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
try {
    $workbook = $excel.Workbooks.Open($filePath, 0, $true)
    $sheet = $workbook.Sheets.Item(1)
    $cell = $sheet.Cells.Item(8, 1)
    
    $fcCount = $cell.FormatConditions.Count
    Write-Host "FormatConditions count on A8: $fcCount"
    if ($fcCount -gt 0) {
        for ($i = 1; $i -le $fcCount; $i++) {
            $fc = $cell.FormatConditions.Item($i)
            Write-Host "FC $i type: $($fc.Type)"
            # Some FC types don't have formula
            try { Write-Host "FC $i formula: $($fc.Formula1)" } catch {}
        }
    }
} finally {
    if ($workbook) { $workbook.Close($false) }
    $excel.Quit()
    [System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
}
