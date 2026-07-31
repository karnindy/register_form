import pymysql
import pyodbc

# Connect to MySQL
mysql_conn = pymysql.connect(
    host='localhost',
    user='thaiairp_iptc',
    password='Pass@456981@XKTT',
    database='thaiairp_iptc',
    cursorclass=pymysql.cursors.DictCursor
)

# Connect to SQL Server
sql_conn = pyodbc.connect(
    'DRIVER={ODBC Driver 18 for SQL Server};'
    'SERVER=.\\SQLEXPRESS;'
    'DATABASE=thaiairp_iptc;'
    'Trusted_Connection=yes;'
    'TrustServerCertificate=yes;'
)
sql_cursor = sql_conn.cursor()

# Get all tables from MySQL
with mysql_conn.cursor() as cursor:
    cursor.execute("SHOW TABLES")
    tables = [list(row.values())[0] for row in cursor.fetchall()]

if '__EFMigrationsHistory' in tables: tables.remove('__EFMigrationsHistory')
if 'pomelo_ef_history' in tables: tables.remove('pomelo_ef_history')

# Order of insertion to respect FKs (parents first)
# MstAgentRegion -> MstAgentBranch
# Provinces -> Districts -> SubDistricts
# Companies, Genders, Titles, BloodTypes, Religions, Territories, Expertises
# PersonRegistration -> RegistrationHistory, PersonExperiences
order = [
    'MstAgentRegion', 'MstAgentBranch',
    'Provinces', 'Districts', 'SubDistricts',
    'admin_users', 'Companies', 'Genders', 'Titles', 'BloodTypes', 'Religions', 'Territories', 'Expertises',
    'PersonRegistration', 'RegistrationHistory', 'PersonExperiences'
]
# Ensure all tables are ordered
ordered_tables = [t for t in order if t in tables]
for t in tables:
    if t not in ordered_tables: ordered_tables.append(t)

# Delete existing data in reverse order to avoid FK constraint violations
print("Clearing existing data...")
for table in reversed(ordered_tables):
    try:
        sql_cursor.execute(f"DELETE FROM [{table}]")
        sql_conn.commit()
    except Exception as e:
        print(f"Could not delete from {table}: {e}")

for table in ordered_tables:
    with mysql_conn.cursor() as cursor:
        cursor.execute(f"SELECT * FROM `{table}`")
        rows = cursor.fetchall()
        
    if not rows:
        print(f"Table {table} is empty. Skipping.")
        continue
        
    print(f"Migrating {len(rows)} rows for {table}...")
    # Get SQL Server columns for this table
    sql_cursor.execute(f"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{table}'")
    sql_cols = [row[0] for row in sql_cursor.fetchall()]
    
    # Filter columns to only those that exist in SQL Server
    columns = [c for c in list(rows[0].keys()) if c in sql_cols]
    
    if not columns:
        print(f"No matching columns for {table}. Skipping.")
        continue
    
    # Check if table has an IDENTITY column in SQL server
    # Quick way: just try SET IDENTITY_INSERT ON
    has_identity = False
    try:
        sql_cursor.execute(f"SET IDENTITY_INSERT [{table}] ON")
        has_identity = True
    except Exception as e:
        pass
        
    placeholders = ",".join(["?"] * len(columns))
    cols_str = ",".join([f"[{c}]" for c in columns])
    insert_query = f"INSERT INTO [{table}] ({cols_str}) VALUES ({placeholders})"
    
    for row in rows:
        values = tuple(row[c] for c in columns)
        sql_cursor.execute(insert_query, values)
        
    if has_identity:
        try:
            sql_cursor.execute(f"SET IDENTITY_INSERT [{table}] OFF")
        except: pass
        
    sql_conn.commit()

print("Migration completed successfully!")
