<?php
// Exemple de traitement d'un paiement MTN Mobile Money en PHP (simulation)
// Pour une vraie intégration, il faut utiliser l'API MTN MoMo

require_once 'config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $participant_id = intval($_POST['participant_id'] ?? 0);
    $amount = floatval($_POST['amount'] ?? 0);
    $vote_count = intval($_POST['vote_count'] ?? 1);
    $status = $_POST['status'] ?? 'success';

    // Enregistrer le paiement
    $stmt = $pdo->prepare('INSERT INTO payments (user_id, amount, status) VALUES (?, ?, ?)');
    $ok1 = $stmt->execute([$user_id, $amount, $status]);

    // Enregistrer le vote
    $stmt2 = $pdo->prepare('INSERT INTO votes (user_id, participant_id, vote_count) VALUES (?, ?, ?)');
    $ok2 = $stmt2->execute([$user_id, $participant_id, $vote_count]);

    if ($ok1 && $ok2) {
        echo json_encode(['success' => true, 'message' => 'Paiement et vote enregistrés']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Méthode non autorisée']);
?>

<?php
// Remplace ces valeurs par tes propres clés
$api_user = 'YOUR_API_USER';
$api_key = 'YOUR_API_KEY';
$subscription_key = 'YOUR_SUBSCRIPTION_KEY';
$target_env = 'sandbox'; // ou 'production'
$base_url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/';

// 1. Récupère le token d'accès
function getAccessToken($api_user, $api_key, $subscription_key)
{
    $url = 'https://sandbox.momodeveloper.mtn.com/collection/token/';
    $headers = [
        "Ocp-Apim-Subscription-Key: $subscription_key",
        "Authorization: Basic " . base64_encode("$api_user:$api_key")
    ];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($result, true);
    return $data['access_token'] ?? null;
}

// 2. Initie le paiement
function requestToPay($token, $subscription_key, $number, $amount, $artiste)
{
    $url = 'https://sandbox.momodeveloper.mtn.com/collection/v1_0/requesttopay';
    $referenceId = uniqid();
    $headers = [
        "Authorization: Bearer $token",
        "X-Reference-Id: $referenceId",
        "X-Target-Environment: sandbox",
        "Ocp-Apim-Subscription-Key: $subscription_key",
        "Content-Type: application/json"
    ];
    $body = json_encode([
        "amount" => $amount,
        "currency" => "XAF",
        "externalId" => $referenceId,
        "payer" => [
            "partyIdType" => "MSISDN",
            "partyId" => $number
        ],
        "payerMessage" => "Vote pour $artiste",
        "payeeNote" => "Talent Etoile"
    ]);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);
    return $referenceId;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $number = $_POST['number'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $artiste = $_POST['artiste'] ?? '';

    if ($number && $amount) {
        $token = getAccessToken($api_user, $api_key, $subscription_key);
        if ($token) {
            $referenceId = requestToPay($token, $subscription_key, $number, $amount, $artiste);
            echo json_encode([
                'success' => true,
                'message' => 'Paiement initié. Veuillez valider sur votre téléphone.',
                'referenceId' => $referenceId
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erreur d\'authentification MTN MoMo.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Champs manquants.'
        ]);
    }
    exit;
}
?>