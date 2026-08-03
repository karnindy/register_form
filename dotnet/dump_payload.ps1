$connString = "Server=localhost;Database=thaiairp_iptc;User Id=sa;Password=Pass@456981@XKTT;TrustServerCertificate=True;"
$conn = New-Object System.Data.SqlClient.SqlConnection
$conn.ConnectionString = $connString
$conn.Open()
$cmd = $conn.CreateCommand()
$cmd.CommandText = "SELECT TOP 1 profile_payload FROM dbo.oic_agent_profile_store ORDER BY profile_id DESC"
$reader = $cmd.ExecuteReader()
if ($reader.Read()) {
    $payload = $reader.GetString(0)
    Set-Content -Path "payload_out.json" -Value $payload -Encoding UTF8
}
$conn.Close()
