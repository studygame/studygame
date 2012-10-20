<?php

	// ensure only logged-in users can play a game
	session_start();
	if (!isset($_SESSION["userid"])) {
		echo json_encode(array("errno" => 1, "errmsg" => "You must be logged in to play a game."));
		exit;
	}
	
	// page globals
	$startTimer = 15;
	$cardTimer = 10;
	$roundTimer = 5;
	$endTimer = 15;
	
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
			$gameState = json_decode(file_get_contents("./games/".$gameid.".txt"), TRUE);
			
			// if it does not exist, create a new game
			if (empty($gameState)) {

				// constant number of rounds per game
				$totalRounds = 5;

				// gather deck and card count information
				$deck = json_decode("{\"deckid\": 123, \"name\": \"CS3380 Exam 1\", \"author\": \"tka55e\"}", TRUE);
				$cardCount = 1;

				// create a new game state object
				$gameState = array();
				$gameState["gameid"] = $gameid;
				$gameState["deck"] = $deck;
				$gameState["totalRounds"] = $totalRounds;
				$gameState["cardsPerRound"] = ($cardCount / $totalRounds < 1 ? $cardCount : $cardCount / $totalRounds);
				$gameState["status"] = 0;	// 0 = game has not started, 1 = game has started, 2 = between rounds, 3 = game over
				$gameState["startTimer"] = $startTimer;	// seconds before a game starts
				$gameState["timestamp"] = time();

				// save new game state object
				if (file_put_contents("./games/".$gameid.".txt", json_encode($gameState)) == FALSE) {
					echo json_encode(array("errno" => 3, "errmsg" => "unable to save new game state"));
					break;
				}

			}
			
			// update the client with the current game state
			// and redirect them to the game currently in progress
			$_SESSION["gameState"] = $gameState;
			header("Location: game.php");			
		
		break;
		
		case("getGameState"):

			// ensure a game state has been supplied to look up game id
			$gameState = $_SESSION["gameState"];
			if (empty($gameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not passed in"));
				break;
			}
			
			// ensure a game id has been supplied to look up game state object
			$gameid = $gameState["gameid"];
			if (empty($gameid)) {
				echo json_encode(array("errno" => 4, "errmsg" => "game id not found"));
				break;
			}
			
			// load game state object
			$gameState = json_decode(file_get_contents("./games/".$gameid.".txt"), TRUE);
			if (empty($gameState)) {
				echo json_encode(array("errno" => 5, "errmsg" => "game state not available"));
				break;
			}

			// if game has not started, wait a few seconds
			if ($gameState["status"] == 0) {
				
				// update game state if a second has gone by
				$now = time();
				$diff = $now - $gameState["timestamp"];
				
				if ($diff >= 1) {
				
					$gameState["timestamp"] = $now;
					$gameState["startTimer"]--;
				
					// if start counter has ended, start the game
					if ($gameState["startTimer"] == 0) {

						// get first card information for deck
						// replace with query
						$card = json_decode("{\"deckid\": 123, \"cardid\": 1, \"question\": \"What sound does a cow make?\", \"answers\": [{\"answerid\": 1, \"answer\": \"Meow\", \"correct\": 0}, {\"answerid\": 2, \"answer\": \"Moo\", \"correct\": 1}], \"timelimit\": 15}", TRUE);

						// initialize game data to the start of a game
						$gameState["card"] = $card;
						$gameState["cardTimer"] = ($card["timelimit"] ? $card["timelimit"] : $cardTimer);
						$gameState["cardNumber"] = 1;
						$gameState["roundNumber"] = 1;
						$gameState["status"] = 1;
					
					}
				
					// save the updated game state
					if (file_put_contents("./games/".$gameid.".txt", json_encode($gameState)) == FALSE) {
						echo json_encode(array("errno" => 6, "errmsg" => "unable to save updated game state"));
						break;
					}

				}
				
			}

			// if game is in progress, check for game state changes
			else if ($gameState["status"] == 1) {

				// update game state if a second has gone by
				$now = time();
				$diff = $now - $gameState["timestamp"];
			
				if ($diff >= 1) {
				
					$gameState["timestamp"] = $now;
					$gameState["cardTimer"]--;
			
					// if time has run out for the current card
					// get the next card in the deck
					if ($gameState["cardTimer"] == 0) {				
						$card = json_decode("{\"deckid\": 123, \"cardid\": 2, \"question\": \"What sound does a dog make?\", \"answers\": [{\"answerid\": 1, \"answer\": \"Woof\", \"correct\": 1}, {\"answerid\": 2, \"answer\": \"Baa\", \"correct\": 0}], \"timelimit\": 10}", TRUE);
						$gameState["card"] = $card;
						$gameState["cardTimer"] = $card["timelimit"];
						$gameState["cardNumber"]++;
						
						// if the card number is such that a new round
						// is required, update the round count
						// and set round status to indicate a new round is beginning
						if ($gameState["cardNumber"] % $gameState["cardsPerRound"] == 0) {
							$gameState["roundNumber"]++;
							$gameState["roundTimer"] = 5;
							$gameState["status"] = 2;
						}
						
						// if the round number is such that the game is over
						// set the round status to indicate end of game
						if ($gameState["roundNumber"] > $gameState["totalRounds"]) {
							$gameState["endTimer"] = 15;
							$gameState["status"] = 3;
						}
						
					}
						
					// save the updated game state
					if (file_put_contents("./games/".$gameid.".txt", json_encode($gameState)) == FALSE) {
						echo json_encode(array("errno" => 6, "errmsg" => "unable to save updated game state"));
						break;
					}

				}

			}
			
			// if game is between rounds, wait a few seconds
			else if ($gameState["status"] == 2) {
				
				// update game state if a second has gone by
				$now = time();
				$diff = $now - $gameState["timestamp"];
				
				if ($diff >= 1) {
				
					$gameState["timestamp"] = $now;
					$gameState["roundTimer"]--;
					
					// when timer runs out, continue to the next round
					if($gameState["roundTimer"] == 0) {
						$gameState["status"] = 1;
					}
					
					// save the updated game state
					if (file_put_contents("./games/".$gameid.".txt", json_encode($gameState)) == FALSE) {
						echo json_encode(array("errno" => 6, "errmsg" => "unable to save updated game state"));
						break;
					}
					
				}
				
			}
			
			// if game is over, wait a few seconds
			else if ($gameState["status"] == 3) {
				
				// update game state if a second has gone by
				$now = time();
				$diff = $now - $gameState["timestamp"];
				
				if ($diff >= 1) {
				
					$gameState["timestamp"] = $now;
					$gameState["endTimer"]--;
					
					// when timer runs out, continue to the next round
					if($gameState["endTimer"] == 0) {
						$gameState["startTimer"] = 1;
						$gameState["status"] = 0;
					}
					
					// save the updated game state
					if (file_put_contents("./games/".$gameid.".txt", json_encode($gameState)) == FALSE) {
						echo json_encode(array("errno" => 6, "errmsg" => "unable to save updated game state"));
						break;
					}
					
				}
				
			}

			// update client with latest game state object
			$_SESSION["gameState"] = $gameState;
			echo json_encode($gameState);

		break;
		
		default:
			echo json_encode(array("errno" => 0, "errmsg" => "invalid function call"));
		break;

	}

?>