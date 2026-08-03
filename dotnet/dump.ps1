sqlcmd -S localhost -U sa -P Pass@456981@XKTT -d thaiairp_iptc -C -Q "SELECT TOP 1 profile_payload FROM dbo.oic_agent_profile_store ORDER BY profile_id DESC" -o out_payload.txt
