<?php
session_start();

if (isset($_SESSION['messages'])) {
    foreach ($_SESSION['messages'] as $message) {
        if ($message['role'] === 'assistant') {
            echo "<div class='remote'>
                <div class='avatar'>
                    <div class='pic'><img src='./img/自行設定.jpg' /></div>
                    <div class='name'>AI助手</div>
                </div>
                <div class='text'><pre><code>" . htmlspecialchars($message['content']) . "</code></pre></div>
            </div><br>";
        } else {
            echo "<div class='local'>
                <div class='text'><code>" . htmlspecialchars($message['content']) . "</code></div>
                <div class='avatar'>
                    <div class='pic'><img src='./img/自行設定.jpg' /></div>
                    <div class='name'>你</div>
                </div>
            </div><br>";
        }
    }
}
?>
