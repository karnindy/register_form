import pymysql
conn = pymysql.connect(host='localhost', user='thaiairp_iptc', password='Pass@456981@XKTT', database='thaiairp_iptc', cursorclass=pymysql.cursors.DictCursor)
cur = conn.cursor()
cur.execute("SELECT broker_level, broker_company, broker_branch FROM register LIMIT 5")
for r in cur.fetchall(): print(r)
