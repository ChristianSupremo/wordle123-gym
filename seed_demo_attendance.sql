-- OPTIONAL demo data only: creates synthetic visits for the previous 60 days.
-- Run once on a demo database after attendance_migration.sql. Do not use with production data.
-- Members divisible by 3 receive visits only from the older half of the period to help
-- demonstrate the at-risk member list.
INSERT INTO attendance (MemberID, CheckIn, CheckOut)
SELECT t.MemberID, t.ts, DATE_ADD(t.ts, INTERVAL 40 + FLOOR(RAND()*60) MINUTE)
FROM (
  SELECT m.MemberID, n.d,
         DATE_ADD(DATE_SUB(CURDATE(), INTERVAL n.d DAY), INTERVAL 6 + FLOOR(RAND()*15) HOUR) AS ts
  FROM member m
  JOIN (SELECT a.i + 10*b.i AS d
        FROM (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9) a
        JOIN (SELECT 0 i UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) b) n
) t
WHERE (t.MemberID % 3 <> 0 OR t.d >= 30) AND RAND() < 0.4 AND t.ts <= NOW();
