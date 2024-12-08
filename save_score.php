<?php
// based on the example given on gitlab
require 'get_db_connection.php';

$conn = get_db_connection();

$username = $_GET['username'];
$score = $_GET['score'];


$query = $conn->
prepare("INSERT INTO leaderboard (username, score) VALUES (:username, :score) RETURNING username, score");
$query->bindParam(':username', $username);
$query->bindParam(':score', $score, PDO::PARAM_INT);
$query->execute();

$query = null;
$conn = null;
