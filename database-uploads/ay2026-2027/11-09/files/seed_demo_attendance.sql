-- OPTIONAL: fake visits for the last 60 days so charts and the at-risk list have data for your demo.
-- Members whose MemberID is divisible by 3 only get visits older than 30 days (=> they show up as "at risk").
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
