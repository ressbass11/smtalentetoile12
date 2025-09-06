<?php
require 'db.php';

// Récupérer les résultats des votes
$stmt = $pdo->query("SELECT name, votes FROM participants ORDER BY votes DESC");
$participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <title>Résultats des votes</title>
  <link rel="stylesheet" href="/front-end/assets/CSS/style.css">
</head>

<body>
  <h1>Résultats du concours</h1>
  <ul>
    <?php foreach ($participants as $p): ?>
      <li><?= htmlspecialchars($p['name']) ?> - <?= $p['votes'] ?> votes</li>
    <?php endforeach; ?>
  </ul>
</body>

</html>