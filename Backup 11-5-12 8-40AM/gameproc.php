<?php

	// Game state processor for StudyFlash

	// utility function for sending messages between function calls
	function message($status, $message) {
		return array("status" => $status, "data" => $message);
	}

	// ensure we have a valid session
	session_start();
	if (!isset($_SESSION["username"])) {
		echo json_encode(message(-1, "You must be logged in to play."));
		exit;
	}
	
	// ensure a database connection is available
	include 'dbconnect.php';
	if (!isset($dbconn) || $dbconn === "" || $dbconn === FALSE) {
		echo json_encode(message(-1, "Unable to connect to database!"));
		exit;
	}
	
	// creates a new game in the database for storing high scores
	function createNewGame($deckid) {
		
		// allow access to database connection variable
		global $dbconn;
		
		// ensure we have a valid deck id
		if ($deckid === "") {
			return message(-1, "Cannot create a high score record without a valid deck id.");
		}
		
		// insert the new game record
		$query = "INSERT INTO highscore (deckid, username, highscore) VALUES ($1, $2, 0)";
		$stmnt = pg_prepare($dbconn, "newGame", $query);
		$result = pg_execute($dbconn, "newGame", array($deckid, $_SESSION["username"]));
		
		// ensure there were no query errors
		if (!$result) {
			return message(-1, "Error executing query against the database.");
		}
		
		// ensure a record was inserted, if not, one already exists
		if (pg_affected_rows($result) == 0) {
			return message(1, "High score record for deck not created");
		}
		
		// return success
		return message(1, "High score record for deck created successfully");
		
	}
	
	// retrieves the current high scores from the database
	// ***** CHANGE THIS TO GRAB HIGH SCORES AS RECORDS IN HIGHSCORE TABLE ***/
	function getHighScores($deckid) {
		
		// allow access to database connection variable
		global $dbconn;
		
		// ensure we have a valid deck id
		if ($deckid === "") {
			return message(-1, "Cannot retrieve high scores without a valid deck id.");
		}
		
		// prepare to query the database for the current high scores
		$query = "SELECT username, highscore FROM highscore WHERE deckid = $1";
		$stmnt = pg_prepare($dbconn, "getHighScores", $query);
		$result = pg_execute($dbconn, "getHighScores", array($deckid));
			
		// ensure there were no query errors
		if (!$result) {
			return message(-1, "Error executing query against the database.");
		}
		
		// ensure a record was returned
		if (pg_num_rows($result) == 0) {
			return message(-1, "High score record for deck not found");
		}
			
		// fetch the rows that were returned
		$highscores = array();
		while ($row = pg_fetch_assoc($result)) {
			$highscores[] = $row;
		}
		
		// return high scores
		return message(1, $highscores);
		
	}
	
	// updates a player high score in the database if current score is greater
	function updateHighScore($deckid, $score) {
		
		// allow access to database connection variable
		global $dbconn;
		
		// ensure we have a valid deckid and score
		if ($deckid === "" || $score === "") {
			return message(-1, "Cannot update high score without a valid current state.");
		}
		
		// update the high score if appropriate
		$query = "UPDATE highscore SET highscore = $1 WHERE deckid = $2 AND username = $3 and highscore < $4";
		$stmnt = pg_prepare($dbconn, "updateHighScore", $query);
		$result = pg_execute($dbconn, "updateHighScore", array($score, $deckid, $_SESSION['username'], $score));

		// return new high scores
		return getHighScores($deckid);
		
	}
	
	function getDeckInfo($deckid) {

		// allow access to database connection variable
		global $dbconn;

		// ensure we have a valid deck id
		if ($deckid === "") {
			return message(-1, "Cannot get deck info without a valid deck id.");
		}

		// query for deck information
		$query = "SELECT deck.deckid, deck.deckname, count(card.cardid) AS numcards FROM deck INNER JOIN card ON deck.deckid = card.deckid WHERE deck.deckid = $1 GROUP BY deck.deckid, deck.deckname";
		$stmnt = pg_prepare($dbconn, "getDeck", $query);
		$result = pg_execute($dbconn, "getDeck", array($deckid));
		
		// ensure there were no query errors
		if (!$result) {
			return message(-1, "Error executing query against the database.");
		}

		// ensure a record was returned
		if (pg_num_rows($result) == 0) {
			return message(-1, "Deck record not found");
		}
		
		// save deck information to variable
		$row = pg_fetch_assoc($result);
		$deck = array("deckid" => (int)$row["deckid"], "deckname" => $row["deckname"], "numcards" => (int)$row["numcards"]);

		// return deck
		return message(1, $deck);
		
	}
	
	function getNextCard($deckid, $cardnum) {

		// allow access to database connection variable
		global $dbconn;

		// ensure we have a valid deck id
		if ($deckid === "") {
			return message(-1, "Cannot get card without a valid deck id.");
		}

		// ensure we have a valid card number
		if ($cardnum === "") {
			return message(-1, "Cannot get card without a valid card number.");
		}

		// query for next card from database
		$query = "SELECT cardid, question, timelimit FROM card WHERE deckid = $1 LIMIT 1 OFFSET $2";
		$stmnt = pg_prepare($dbconn, "getCard", $query);
		$result = pg_execute($dbconn, "getCard", array($deckid, $cardnum));
		
		// ensure there were no query errors
		if (!$result) {
			return message(-1, "Error executing query against the database.");
		}
		
		// ensure a record was returned
		if (pg_num_rows($result) == 0) {
			return message(-1, "Card record not found");
		}
		
		// save card information to variable
		$row = pg_fetch_assoc($result);
		$card = array("cardid" => (int)$row["cardid"], "question" => $row["question"], "timelimit" => (int)$row["timelimit"]);
		
		// query for card answers from the database
		$query = "SELECT answer FROM answer WHERE cardid = $1";
		$stmnt = pg_prepare($dbconn, "getAnswers", $query);
		$result = pg_execute($dbconn, "getAnswers", array($card["cardid"]));
		
		// ensure there were no query errors
		if (!$result) {
			return message(-1, "Error executing query against the database.");
		}
		
		// ensure a record was returned
		if (pg_num_rows($result) == 0) {
			return message(-1, "Answer record not found");
		}
		
		// save answer information to card
		while ($row = pg_fetch_assoc($result)) {
			$card["answers"][] = $row;
		}
		
		// return card with answers
		return message(1, $card);

	}
	
	function getCorrectAnswer($cardid) {
		
		// allow access to database connection variable
		global $dbconn;

		// ensure we have a valid card id
		if ($cardid === "") {
			return message(-1, "Cannot get card info without a valid cardid.");
		}
		
		// query for correct card answer from the database
		$query = "SELECT answer FROM answer WHERE cardid = $1 AND correct = 1";
		$stmnt = pg_prepare($dbconn, "getCorrectAnswer", $query);
		$result = pg_execute($dbconn, "getCorrectAnswer", array($cardid));
		
		// ensure there were no query errors
		if ($result === false) {
			return message(-1, "Error executing query against the database.");
		}
		
		// ensure a record was returned
		if (pg_num_rows($result) == 0) {
			return message(-1, "Answer record not found");
		}
		
		// save correct answer to variable
		$correct = pg_fetch_assoc($result);
		
		// return correct answer
		return message(1, $correct);
		
	}
	
	function getNextGameState($curGameState) {
		
		// ensure we have a valid game state
		if ($curGameState === "") {
			return message(-1, "Cannot retrieve next game state without a valid current state.");
		}

		// ensure we have a valid deck id
		if (!isset($curGameState["deckid"]) || $curGameState["deckid"] === "") {
			return message(-1, "Cannot retrieve next game state without a valid deck id.");
		}
		
		// ensure we have a valid state id
		if (!isset($curGameState["stateid"]) || $curGameState["stateid"] === "") {
			return message(-1, "Cannot retrieve next game state without a valid state id.");
		}
		
		// global game variables
		$correctAnswerPoints = 200;
		$numCardsPerRound = 5;
		$startMessageTimeLimit = 2;
		$roundMessageTimeLimit = 2;
		$correctMessageTimeLimit = 4;
		$endMessageTimeLimit = 10;
		
		// overrwite current game state with new game state information
		switch ($curGameState["stateid"]) {
			
			case 4:	// old state: game over, next state: game start

					// clear any lingering card information
					unset($curGameState["card"]);
			
			case -1: // old state: nil, next state: game start
			
				// get deck information
				$deck = getDeckInfo($curGameState["deckid"]);

				if ($deck["status"] == -1) {
					return message(-1, "Error getting deck information: ".$deck["data"]);
				}
				
				$deck = $deck["data"];
				
				// create game in game table if none exists already
				createNewGame($deck["deckid"]);
			
				// update game state
				$curGameState["deckid"] = $deck["deckid"];
				$curGameState["deckname"] = $deck["deckname"];
				$curGameState["numcards"] = $deck["numcards"];
				$curGameState["numrounds"] = ceil($deck["numcards"] / 5);
				$curGameState["timelimit"] = $startMessageTimeLimit;
				$curGameState["timer"] = $startMessageTimeLimit;
				$curGameState["cardnum"] = 0;
				$curGameState["roundnum"] = 0;
				$curGameState["score"] = 0;
				$curGameState["stateid"] = 0;
			
			break;

			case 0: // old state: game start, next state: round start

				$curGameState["timelimit"] = $roundMessageTimeLimit;
				$curGameState["timer"] = $roundMessageTimeLimit;
				$curGameState["roundnum"]++;
				$curGameState["stateid"] = 1;
			
			break;
			
			case 1: // old state: round start, next state: show card

				// get next card
				$card = getNextCard($curGameState["deckid"], $curGameState["cardnum"]);
				
				if ($card["status"] == -1) {
					return message(-1, "Error getting card information: ".$card["data"]);
				}
				
				$card = $card["data"];

				// update game state
				$curGameState["card"] = $card;
				$curGameState["timelimit"] = $card["timelimit"];
				$curGameState["timer"] = $card["timelimit"];
				$curGameState["cardnum"]++;
				$curGameState["stateid"] = 2;

			break;
			
			case 2: // old state: show card, next state: show correct answer
			
				// get the correct answer
				$correct = getCorrectAnswer($curGameState["card"]["cardid"]);
				
				if ($correct["status"] == -1) {
					return message(-1, "Error getting correct answer: ".$correct["data"]);
				}
				
				$correct = $correct["data"];
				
				// if player supplied an answer to this question, check the answer
				// and update the high scores if appropriate
				if (isset($curGameState["answer"]) && isset($curGameState["answertimeleft"])) {
					if ($curGameState["answer"] == $correct["answer"]) {

						// increase score
						$curGameState["score"] += ceil(($curGameState["answertimeleft"] / $curGameState["timelimit"]) * $correctAnswerPoints);

						// update high score if appropriate
						$highscores = updateHighScore($curGameState["deckid"], $curGameState["score"]);
						
						if ($highscores["status"] == -1) {
							return message(-1, "Unable to update high score: ".$highscores["data"]);
						}
												
					}
				}
				
				// update game state
				$curGameState["card"]["correct"] = $correct["answer"];
				$curGameState["timelimit"] = $correctMessageTimeLimit;
				$curGameState["timer"] = $correctMessageTimeLimit;
				$curGameState["stateid"] = 3;
			
			break;
			
			case 3: // old state: show correct answer, next state: next card, next round, or end game
			
				// if the number of cards is exhausted, end the game
				if ($curGameState["cardnum"] == $curGameState["numcards"]) {
					$curGameState["timelimit"] = $endMessageTimeLimit;
					$curGameState["timer"] = $endMessageTimeLimit;
					$curGameState["stateid"] = 4;
				}
					
				// if the card number is such that a new round should start
				else if (($curGameState["cardnum"] % $numCardsPerRound) == 0) {
					$curGameState["timelimit"] = $roundMessageTimeLimit;
					$curGameState["timer"] = $roundMessageTimeLimit;
					$curGameState["roundnum"]++;
					$curGameState["stateid"] = 1;
				}
					
				// otherwise, show the next card
				else {
					
					// get next card
					$card = getNextCard($curGameState["deckid"], $curGameState["cardnum"]);

					if ($card["status"] == -1) {
						return message(-1, "Error getting card information: ".$card["data"]);
					}
					
					$card = $card["data"];

					// update game state
					$curGameState["card"] = $card;
					$curGameState["timelimit"] = $card["timelimit"];
					$curGameState["timer"] = $card["timelimit"];
					$curGameState["cardnum"]++;
					$curGameState["stateid"] = 2;
					
				}
			
			break;
		}

		// get high scores
		$highscores = getHighScores($curGameState["deckid"]);
		
		if ($highscores["status"] == -1) {
			return message(-1, "Error getting high scores: ".$highscores["data"]);
		}

		$curGameState["highscores"] = $highscores["data"];
		
		// return next game state state
		return message(1, $curGameState);
				
	}

	// perform the specified function
	switch ($_POST["function"]) {
		
		case "joinGame":
		
			// ensure a deck id has been provided
			if (!isset($_POST["deckid"])) {
				echo json_encode(message(-1, "A deck id must be specified to play."));
				break;
			}

			$_SESSION["gamestate"] = array();
			$_SESSION["gamestate"]["stateid"] = -1;
			$_SESSION["gamestate"]["deckid"] = $_POST["deckid"];

			header("Location: game.php");

		break;
		
		case "getNextGameState":

			// ensure a game state has been specified
			if (!isset($_POST["curGameState"])) {
				echo json_encode(message(-1, "Game state must be specified when getting next game state."));
				break;
			}

			$clientGameState = json_decode(stripslashes($_POST["curGameState"]), true);
			
			// retrieve game state from session
			$curGameState = $_SESSION["gamestate"];

			// update answer and answer time left from client state if provided
			if (isset($clientGameState["answer"]) && isset($clientGameState["answertimeleft"])) {
				$curGameState["answer"] = $clientGameState["answer"];
				$curGameState["answertimeleft"] = $clientGameState["answertimeleft"];
			}
			
			// update current state id from client state
			// this is to ensure if page is reloaded that the game starts over
			$curGameState["stateid"] = $clientGameState["stateid"];

			// update game state to next game state
			$nextGameState = getNextGameState($curGameState);
			
			// save new game state to session
			if ($nextGameState["status"] == 1)
				$_SESSION["gamestate"] = $nextGameState["data"];

			// send new game state to client, or any errors that occured
			echo json_encode($nextGameState);
		
		break;
		
		default:
			echo json_encode(message(-1, "Invalid function call or no function specified"));
		break;
		
	}
	
	// close database connection
	pg_close($dbconn);
	
	// flush the buffer
	flush();
	
?>