<?php

// Removable code that's for achievements is marked with an ACHIEVEMENT-CODE comment

const CONST_ACHIEVEMENTS = [
	[
		"icon" => "webmaster.png",
		"title" => "Webmaster",
		"description" => "Solved all Web challenges"
	],

	[
		"icon" => "godofsecrets.png",
		"title" => "Crypto Senpai Orz Orz Orz",
		"description" => "Solved all Cryptography challenges"
	],

	[
		"icon" => "krtwconnoisseur.png",
		"title" => ".kr and .tw connoisseur",
		"description" => "Solved all Binary Exploitation challenges"
	],

	[
		"icon" => "practicalproblems.png",
		"title" => "REeeeeeeeeeeeee",
		"description" => "Solved all Reverse Engineering challenges"
	],

	[
		"icon" => "jackofalltrades.png",
		"title" => "Jack of all trades",
		"description" => "Solved all Misc challenges"
	],

	[
		"icon" => "finderskeepers.png",
		"title" => "Finders Keepers",
		"description" => "Solved all Forensics challenges"
	],

	[
		"icon" => "breakithammer.png",
		"title" => "Hardware Hackerman",
		"description" => "Solved all Hardware challenges"
	],

	[
		"icon" => "programming.png",
		"title" => "Oh, you know programming? Name every algorithm.",
		"description" => "Solved all Programming challenges"
	],

	[
		"icon" => "hoarder.png",
		"title" => "Flag Hoarder",
		"description" => "Solved 5 challenges in the span of 5 minutes"
	],

	[
		"icon" => "cheeser.png",
		"title" => "Brute Forcer",
		"description" => "Submitted 10 wrong flags for the same challenge"
	],

];

function add_achievement($achievementID)
{
	$userAchievements = db_select_one('users', array('achievements'), array('id' => $_SESSION['id']))['achievements'];
	db_update('users', array('achievements' => $userAchievements | (1 << $achievementID)), array('id' => $_SESSION['id']));
}