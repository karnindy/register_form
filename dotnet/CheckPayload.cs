using System;
using System.Data.SqlClient;

class Program
{
    static void Main()
    {
        string connStr = "Server=localhost;Database=thaiairp_iptc;User Id=sa;Password=Pass@456981@XKTT;TrustServerCertificate=True;";
        using (var conn = new SqlConnection(connStr))
        {
            conn.Open();
            using (var cmd = new SqlCommand("SELECT TOP 1 profile_payload FROM dbo.oic_agent_profile_store ORDER BY profile_id DESC", conn))
            {
                using (var reader = cmd.ExecuteReader())
                {
                    if (reader.Read())
                    {
                        Console.WriteLine(reader.GetString(0));
                    }
                }
            }
        }
    }
}
