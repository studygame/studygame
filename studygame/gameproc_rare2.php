<?php

	// ensure only logged-in players can play a game
	session_start();
	if (!isset($_SESSION["userid"])) {
		echo json_encode(array("errno" => 1, "errmsg" => "You must be logged in to play a game."));
		exit;
	}

	// ensure there is a valid database connection available
	if (empty($_SESSION["dbconn"]) || pg_connection_status($_SESSION["dbconn"]) == PGSQL_CONNECTION_BAD) {

		$server = "dbhost-pgsql.cs.missouri.edu";
		$username = "cs3380f12grp8";
		$password = "KUqRzeX7";
		$dbname = "cs3380f12grp8";
		
		$_SESSION["dbconn"] = pg_pconnect("host=$server user=$username password=$password dbname=$dbname");
		
		if (!$_SESSION["dbconn"]) {
			echo json_encode(array("errno" => 99, "errmsg" => "Could not connect to the database!"));
			exit;
		}
	}
	
	$dbconn = $_SESSION["dbconn"];
	
	// set game globals
	$startTimer = 15;
	$cardTimer = 10;
	$roundTimer = 5;
	$endTimer = 15;
	$playerID = $_SESSION["userid"];
	
	// returns the most inactive player from
	// a players array
	function findMostInactivePlayer($players) {
	
		$mostInactiveID = "";
		$minRequestTime = 0;
		foreach($players as $playerID => $vals) {
			if ($minRequestTime == 0 || $players[$playerID]["lastrequest"] < $minRequestTime) {
				$mostInactiveID = $id;
			}
		}
		
		return $mostInactiveID;
		
	}
	
	// switch to the appropriate function requested by the calling script
	switch($_POST["function"]) {
		
		case("joinGame"):
		
			// ensure deckid has been passed in, so we can use it
			// to identify our game
			$gameid = $_POST["deckid"];
			if (empty($gameid)) {
				echo json_encode(array("errno" => 2, "errmsg" => "deckid not specified"));
				break;
			}
			
			// try loading the game state object for this deck			
			$query = "SELECT gameid, state FROM game WHERE gameid = $1";
			$stmt = pg_prepare($dbconn, "getGameState", $query);
			$result = pg_execute($dbconn, "getGameState", array($gameid));
			
			// $curGameState = json_decode(file_get_contents("./games/".$gameid.".txt"), TRUE);
			if (pg_num_rows($result) > 0) {
				$row = pg_fetch_assoc($result);
				$curGameState = unserialize($row["state"]);
			}
			
			// if it does not exist, create a new game
			// if (empty($curGameState)) {
			else {

				// gather deck and card count information
				$query = "SELECT deck.deckid, deck.deckname, deck.userid, count(card.cardid) AS totalCards
							FROM deck INNER JOIN card ON deck.deckid = card.deckid
							WHERE deck.deckid = $1
							GROUP BY deck.deckid, deck.deckname, deck.userid";
				$stmt = pg_prepare($dbconn, "getDeckInfo", $query);
				$result = pg_execute($dbconn, "getDeckInfo", array($gameid));
				
				if (pg_num_rows($result) == 0) {
					echo json_encode(array("errno" => 8, "errmsg" => "There is no deck with the specified id."));
					break;
				}
				
				$deck = pg_fetch_assoc($result);

				// create a new game state object
				$curGameState = array();
				$curGameState["gameid"] = $gameid;
				$curGameState["deck"] = $deck;
				$curGameState["state"] = 0;
				$curGameState["startTimer"] = $startTimer;
				$curGameState["startDuration"] = $startTimer;
				$curGameState["roundNumber"] = 0;
				$curGameState["roundTimer"] = $roundTimer;
				$curGameState["roundDuration"] = $roundTimer;
				$curGameState["card"] = NULL;
				$curGameState["answers"] = NULL;
				$curGameState["cardNumber"] = 0;
				$curGameState["cardTimer"] = $cardTimer;
				$curGameState["cardDuration"] = $cardTimer;
				$curGameState["endTimer"] = $endTimer;
				$curGameState["endDuration"] = $endTimer;
				$curGameState["timestamp"] = time();
				$curGameState["players"] = array();

				// save new game state object
				// if (file_put_contents("./games/".$gameid.".txt", serialize($curGameState)) == FALSE) {
				//	echo json_encode(array("errno" => 3, "errmsg" => "unable to save new game state"));
				//	break;
				// }
				
				$query = "INSERT INTO game (gameid, state, lock) VALUES ($1, $2, 0)";
				$stmt = pg_prepare($dbconn, "setGameState", $query);
				$result = pg_execute($dbconn, "setGameState", array($gameid, serialize($curGameState)));
				
				if (!$result) {
					echo json_encode(array("errno" => 3, "errmsg" => "unable to save new game state"));
					break;
				}

			}
			
			// update the player with the current game state
			// and redirect them to the game currently in progress
			$_SESSION["gameState"] = $curGameState;
			header("Location: game_rare2.php");			
		
		break;
		
		case("getGameState"):
		
			// ensure a game state has been supplied by the player to look up game id
			$playerGameState = $_SESSION["gameState"];
			if (empty($playerGameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not passed in"));
				break;
			}
		
			// ensure a game id has been supplied to look up game state object
			$gameid = $playerGameState["gameid"];
			if (empty($gameid)) {
				echo json_encode(array("errno" => 4, "errmsg" => "game id not found"));
				break;
			}
		
			// load current game state object
			// $curGameState = unserialize(file_get_contents("./games/".$gameid.".txt"), TRUE);
			$query = "SELECT gameid, state, lock FROM game WHERE gameid = $1";
			$stmt = pg_prepare($dbconn, "getGameState", $query);
			$result = pg_execute($dbconn, "getGameState", array($gameid));
		
			if (pg_num_rows($result) > 0) {
				$row = pg_fetch_assoc($result);
				$curGameState = unserialize($row["state"]);
				$lock = $row["lock"];
			}
		
			if (empty($curGameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not available"));
				break;
			}

			// update player with latest game state object
			$_SESSION["gameState"] = $curGameState;
			echo json_encode($curGameState);

		break;
		
		// game state: 0 = game has not started, 1 = game has started, 2 = between rounds, 3 = game over
		case("setGameState"):

			// ensure a game state has been supplied by the player to look up game id
			$playerGameState = $_SESSION["gameState"];
			if (empty($playerGameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not passed in"));
				break;
			}
			
			// ensure a game id has been supplied to look up game state object
			$gameid = $playerGameState["gameid"];
			if (empty($gameid)) {
				echo json_encode(array("errno" => 4, "errmsg" => "game id not found"));
				break;
			}
			
			// load current game state object
			// $curGameState = unserialize(file_get_contents("./games/".$gameid.".txt"), TRUE);
			$query = "SELECT gameid, state, lock FROM game WHERE gameid = $1";
			$stmt = pg_prepare($dbconn, "getGameState", $query);
			$result = pg_execute($dbconn, "getGameState", array($gameid));
			
			if (pg_num_rows($result) > 0) {
				$row = pg_fetch_assoc($result);
				$curGameState = unserialize($row["state"]);
				$lock = $row["lock"];
			}
			
			if (empty($curGameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not available"));
				break;
			}
			
			// add player to players list for game if not currently there
			if (!isset($curGameState["players"][$playerID])) {
				$curGameState["players"][$playerID] = array();
				$curGameState["players"][$playerID]["gamescore"] = 0;
				$curGameState["players"][$playerID]["lastrequest"] = 0;
			}

			// allow the next game player who has not updated the game
			// state in a while be the next player to update it
			$nextUpdatePlayerID = findMostInactivePlayer($curGameState["players"]);
						
			// only update game state if at least one second has gone by
			// since the last player updated the game state
			$now = time();
			$timediff = $now - $curGameState["timestamp"];

			// only allow game state updates if there is no lock on the game;
			// a lock indicates that another player is already in the process
			// of updating the game state.
			if (($lock == 0 && $timediff >=1 && $playerID == $nextUpdatePlayerID && $curGameState["state"] == $_POST["state"]) || ($lock == 0 && $timediff >=1 && $curGameState["state"] == $_POST["state"])) {

				// mark game update flag so other players don't update game state
				// while this player is updating game state
				$query = "UPDATE game SET lock = 1 WHERE gameid = $1 AND lock = 0";
				$stmt = pg_prepare($dbconn, "flagGameState", $query);
				$result = pg_execute($dbconn, "flagGameState", array($gameid));

				if (!$result) {
					echo json_encode(array("errno" => 3, "errmsg" => "unable to update game state"));
					break;
				}
				
				// if the number of rows affected by the above query is 0,
				// this indicates that another player is already in the process
				// of updating the game state. Otherwise, the current player
				// is allowed to update the game state.
				if (pg_affected_rows($result) > 0) {
					
					// update player request time to reflect having updated the game state
					$curGameState["players"][$playerID]["lastrequest"] = time();
					
					// check for the player that was supposed to be the one to update the game state
					// if the current player is not that player, the other player might have left
					// the game and we should remove them from the player list
					if ($playerID != $nextUpdatePlayerID) {
						if (($now - $curGameState["players"][$nextUpdatePlayerID]["lastrequest"]) > 10) {
							unset($curGameState["players"][$nextUpdatePlayerID]);
						}
					}

					// update player gamescore if the new gamescore is greater;
					// this restriction helps with issues when players
					// are playing from two different windows with the
					// same user login at the same time
					if ($_POST["gamescore"] > $curGameState["players"][$playerID]["gamescore"]) {
						$curGameState["players"][$playerID]["gamescore"] = $_POST["gamescore"];
					}

					// if game has not started, prepare to start
					if ($curGameState["state"] == -1) {
						
						// set player gamescore to zero
						$curGameState["players"][$playerID]["gamescore"] = 0;

						$curGameState["timestamp"] = $now;
						$curGameState["startTimer"] = $startTimer;
						$curGameState["state"] = 0;
	
					}
					
					// if game is ready to play, prepare to start a new round
					else if ($curGameState["state"] == 0) {
						
						$curGame["timestamp"] = $now;
						$curGame["startTimer"] = 0;
						
						// set round state to indicate a new round is beginning
						$curGameState["roundNumber"]++;
						$curGameState["roundTimer"] = $roundTimer;
						$curGameState["roundDuration"] = $roundTimer;
						$curGameState["state"] = 2;
					}
					
					// if round is over, prepare to start a new round or prepare to end the game
					else if ($curGameState["state"] == 1) {

						$curGameState["timestamp"] = $now;
						$curGameState["cardTimer"] = 0;

						// if the card number is such that the game is over
						// set the state to indicate end of game
						if ($curGameState["cardNumber"] == $curGameState["deck"]["totalcards"]) {
							$curGameState["endTimer"] = $endTimer;
							$curGameState["endDuration"] = $endTimer;
							$curGameState["state"] = 3;
						}
						
						else {
							
							// set round state to indicate a new round is beginning
							$curGameState["roundNumber"]++;
							$curGameState["roundTimer"] = $roundTimer;
							$curGameState["roundDuration"] = $roundTimer;
							$curGameState["state"] = 2;
						}

					}
			
					// if new round is ready to start, start a new round
					else if ($curGameState["state"] == 2) {

						$curGameState["timestamp"] = $now;
						$curGameState["roundTimer"] = 0;

						// get the next card in the deck
						$query = "SELECT cardid, deckid, question, timer
									FROM card
									WHERE deckid = $1
									LIMIT 1 OFFSET $2";
						$stmt = pg_prepare($dbconn, "getCardInfo", $query);
						$result = pg_execute($dbconn, "getCardInfo", array($gameid, $curGameState["cardNumber"]));

						if (pg_num_rows($result) == 0) {
							echo json_encode(array("errno" => 8, "errmsg" => "There are no cards in the deck."));
							break;
						}

						$card = pg_fetch_assoc($result);

						// get the card answers
						$query = "SELECT cardid, answer, correct
									FROM answer
									WHERE cardid = $1";
						$stmt = pg_prepare($dbconn, "getAnswerInfo", $query);
						$result = pg_execute($dbconn, "getAnswerInfo", array($card["cardid"]));

						if (pg_num_rows($result) == 0) {
							echo json_encode(array("errno" => 8, "errmsg" => "There are no answers for this card."));
							break;
						}
						
						$answers = pg_fetch_all($result);

						$curGameState["card"] = $card;
						$curGameState["answers"] = $answers;
						$curGameState["cardTimer"] = ($card["timer"] ? $card["timer"] : $cardTimer);
						$curGameState["cardDuration"] = $curGameState["cardTimer"];
						$curGameState["cardNumber"]++;
						
						$curGameState["state"] = 1;
					
					}
			
					// if game has ended, start a new game
					else if ($curGameState["state"] == 3) {
						
						$curGameState["timestamp"] = $now;
						$curGameState["endTimer"] = 0;
				
						// when timer runs out, start a new game
						$curGameState["card"] = NULL;
						$curGameState["answers"] = NULL;
						$curGameState["cardNumber"] = 0;
						$curGameState["roundNumber"] = 0;
						$curGameState["startTimer"] = $startTimer;
						$curGameState["state"] = 0;
					}
				
					// save the updated game state
					// if (file_put_contents("./games/".$gameid.".txt", serialize($curGameState)) == FALSE) {
					//	echo json_encode(array("errno" => 6, "errmsg" => "unable to save updated game state"));
					//	break;
					// }

					$query = "UPDATE game SET state = $1, lock = 0 WHERE gameid = $2 AND lock = 1";
					$stmt = pg_prepare($dbconn, "updateGameState", $query);
					$result = pg_execute($dbconn, "updateGameState", array(serialize($curGameState), $gameid));

					if (!$result) {
						echo json_encode(array("errno" => 3, "errmsg" => "unable to update game state"));
						break;
					}

				}

			}

			// update player with latest game state object
			$_SESSION["gameState"] = $curGameState;
			echo json_encode($curGameState);

		break;
		
		default:
			echo json_encode(array("errno" => 0, "errmsg" => "invalid function call"));
		break;

	}

?>