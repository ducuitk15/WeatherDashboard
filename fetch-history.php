<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$response = [];

try {
    // Connect to SQLite database
    $conn = new SQLite3('database.db');

    // Fetch search history
    $result = $conn->query('SELECT * FROM search_history');
    $history = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $history[] = $row;
    }

    $response['status'] = 'success';
    $response['history'] = $history;
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = 'Error: ' . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>
