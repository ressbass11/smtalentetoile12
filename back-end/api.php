<?php
header('Content-Type: application/json');
require_once 'config.php';

function getPDO()
{
    global $pdo;
    return $pdo;
}

// ROUTES
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// GET /api/participants
if ($method === 'GET' && strpos($uri, '/api/participants') !== false) {
    $stmt = getPDO()->query('SELECT * FROM participants');
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

// GET /api/votes?participant_id=ID
if ($method === 'GET' && strpos($uri, '/api/votes') !== false) {
    $id = isset($_GET['participant_id']) ? intval($_GET['participant_id']) : 0;
    $stmt = getPDO()->prepare('SELECT SUM(vote_count) as total FROM votes WHERE participant_id = ?');
    $stmt->execute([$id]);
    echo json_encode(['votes' => $stmt->fetchColumn() ?: 0]);
    exit;
}

// POST /api/vote
if ($method === 'POST' && strpos($uri, '/api/vote') !== false) {
    $data = $_POST;
    $user_id = intval($data['user_id'] ?? 0);
    $participant_id = intval($data['participant_id'] ?? 0);
    $vote_count = intval($data['vote_count'] ?? 1);
    $stmt = getPDO()->prepare('INSERT INTO votes (user_id, participant_id, vote_count) VALUES (?, ?, ?)');
    $ok = $stmt->execute([$user_id, $participant_id, $vote_count]);
    echo json_encode(['success' => $ok]);
    exit;
}

// POST /api/payment
if ($method === 'POST' && strpos($uri, '/api/payment') !== false) {
    $data = $_POST;
    $user_id = intval($data['user_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $status = $data['status'] ?? 'pending';
    $stmt = getPDO()->prepare('INSERT INTO payments (user_id, amount, status) VALUES (?, ?, ?)');
    $ok = $stmt->execute([$user_id, $amount, $status]);
    echo json_encode(['success' => $ok]);
    exit;
}

// POST /api/register
if ($method === 'POST' && strpos($uri, '/api/register') !== false) {
    $data = $_POST;
    $name = $data['name'] ?? '';
    $email = $data['email'] ?? '';
    $password = password_hash($data['password'] ?? '', PASSWORD_DEFAULT);
    $stmt = getPDO()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $ok = $stmt->execute([$name, $email, $password]);
    echo json_encode(['success' => $ok]);
    exit;
}

// POST /api/login
if ($method === 'POST' && strpos($uri, '/api/login') !== false) {
    $data = $_POST;
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';
    $stmt = getPDO()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// Si aucune route ne correspond
http_response_code(404);
echo json_encode(['error' => 'Route not found']);
