DROP TABLE IF EXISTS Answer;
DROP TABLE IF EXISTS Card;
DROP TABLE IF EXISTS Deck;
DROP TABLE IF EXISTS Member;
DROP TABLE IF EXISTS Course;
DROP TABLE IF EXISTS School;
DROP TABLE IF EXISTS Game;

CREATE TABLE School (
	schoolName  VARCHAR(255)    NOT NULL,
	schoolId    INTEGER         NOT NULL,
CONSTRAINT PK_School_schoolId PRIMARY KEY(schoolId)
);

CREATE TABLE Member (
	userId		INTEGER			NOT NULL,
	userName	VARCHAR(20)		NOT NULL,
	email		VARCHAR(64)		NOT NULL,
	passHash	VARCHAR(256)		NOT NULL,
	salt		VARCHAR(16)		NOT NULL,
    	schoolId	INTEGER,
CONSTRAINT PK_Member_userId PRIMARY KEY(userId),
CONSTRAINT FK_Member_schoolId FOREIGN KEY(schoolId) REFERENCES School(schoolId)
);

CREATE TABLE Course (
	schoolId	INTEGER		     	NOT NULL,
	course		VARCHAR(10)		NOT NULL,
	semester	VARCHAR(6)		NOT NULL,
	professor	VARCHAR(64)		NOT NULL,
	classId		INTEGER			NOT NULL,
CONSTRAINT PK_Course_classId PRIMARY KEY(classId),
CONSTRAINT FK_Course_schoolId FOREIGN KEY(schoolId) REFERENCES School(schoolId)
);

CREATE TABLE Deck (
	deckId		INTEGER			NOT NULL,
	deckName	VARCHAR(25)		NOT NULL,
	userId		INTEGER			NOT NULL,
	classId		INTEGER			NOT NULL,
CONSTRAINT PK_Deck_deckId PRIMARY KEY(deckId),
CONSTRAINT FK_Deck_classId FOREIGN KEY(classId) REFERENCES Course(classId),
CONSTRAINT FK_Deck_userId FOREIGN KEY(userId) REFERENCES Member(userId)
);

CREATE TABLE Card (
	cardId		INTEGER			NOT NULL,
	deckId		INTEGER			NOT NULL,
	question	TEXT			NOT NULL,
	timer		INTEGER,
CONSTRAINT PK_Card_cardId PRIMARY KEY(cardId),
CONSTRAINT FK_Card_deckId FOREIGN KEY(deckId) REFERENCES Deck(deckId),
CHECK(timer>=0)
);

-- correct = 1 or 0
CREATE TABLE Answer (
	cardId		INTEGER			NOT NULL,
	answer		TEXT			NOT NULL,
	correct		INTEGER			NOT NULL,
CONSTRAINT PK_Answer_answer PRIMARY KEY(cardId, answer),
CONSTRAINT FK_Answer_cardId FOREIGN KEY(cardId) REFERENCES Card(cardId)
);

CREATE TABLE Game (
	gameid		INTEGER			NOT NULL,
	state		TEXT			NOT NULL,
	lock		INTEGER			NOT NULL DEFAULT 0,
CONSTRAINT PK_Game_gameid PRIMARY KEY(gameid)
);

