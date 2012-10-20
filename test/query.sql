SELECT course.course, deck.deckname, course.professor, deck.userid
FROM deck
INNER JOIN course ON deck.classid = course.classid
INNER JOIN school ON course.schoolid = school.schoolid
WHERE school.schoolid = 1 AND course.semester = 'SP12';
