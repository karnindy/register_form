USE [master];
GO
EXEC xp_instance_regwrite N'HKEY_LOCAL_MACHINE', N'Software\Microsoft\MSSQLServer\MSSQLServer', N'LoginMode', REG_DWORD, 2;
GO
ALTER LOGIN sa WITH PASSWORD='Pass@456981@XKTT';
GO
ALTER LOGIN sa ENABLE;
GO
IF DB_ID('thaiairp_iptc') IS NULL CREATE DATABASE thaiairp_iptc;
GO
