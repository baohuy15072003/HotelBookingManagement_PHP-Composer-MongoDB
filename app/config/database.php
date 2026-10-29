<?php

$uri = $_ENV['MONGODB_URI'] ?? "mongodb://localhost:27017";

$databaseName = $_ENV['MONGODB_DATABASE'] ?? "HotelBooking";

$client = new MongoDB\Client($uri);

return $client->selectDatabase($databaseName);
?>
