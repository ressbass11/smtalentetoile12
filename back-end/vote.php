<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $participant_id = $_POST['participant_id'] ?? null;

  if ($participant_id) {
    $stmt = $pdo->prepare("UPDATE participants SET votes = votes + 1 WHERE id = ?");
    $stmt->execute([$participant_id]);
    echo "Vote enregistré avec succès.";
  } else {
    echo "Aucun participant sélectionné.";
  }
}
?>