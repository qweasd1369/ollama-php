<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = $_POST['question'];
    $selected_model = $_POST['model'];

    if (!isset($_SESSION['messages'])) {
        $_SESSION['messages'] = [];
    }

    $_SESSION['messages'][] = [
        'role' => 'user',
        'content' => $question
    ];

    $data = array(
        "model" => $selected_model,
        'messages' => $_SESSION['messages']
    );

    $jsonData = json_encode($data);

    $ch = curl_init('自行創建');

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    $response = curl_exec($ch);

    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
    } else {
        $responses = explode("\n", trim($response));
        $responses_array = [];

        foreach ($responses as $response) {
            $response_array = json_decode($response, true);
            if ($response_array !== null && isset($response_array['message']['content'])) {
                $responses_array[] = $response_array['message']['content'];
            }
        }

        $combined_response = implode("", $responses_array);

        $_SESSION['messages'][] = [
            'role' => 'assistant',
            'content' => $combined_response
        ];
    }

    curl_close($ch);
}
?>
