<?php
// 啟動會話
session_start();

// 檢查是否存在對話歷史，如果沒有則初始化
if (!isset($_SESSION['messages'])) {
    $_SESSION['messages'] = [];
}

// 如果有新問題提交
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['question'])) {
    $question = $_POST['question'];
    $selected_model = $_POST['model']; // 获取所选模型的值

    // 將新問題添加到對話歷史中
    $_SESSION['messages'][] = [
        'role' => 'user',
        'content' => $question
    ];

    // 要發送的數據
    $data = array(
        "model" => $selected_model, // 使用所选模型的值
        'messages' => $_SESSION['messages']
    );

    // 將數據轉換為JSON格式
    $jsonData = json_encode($data);

    // 初始化cURL會話
    $ch = curl_init('http://192.168.1.80:11434/api/chat');

    // 設置cURL選項
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

    // 執行cURL請求並獲取響應
    $response = curl_exec($ch);

    // 檢查是否有錯誤
    if ($response === false) {
        echo 'Curl error: ' . curl_error($ch);
    } else {
        // 分割每行 JSON
        $responses = explode("\n", trim($response));
        $responses_array = [];

        // 遍歷每個 JSON 字符串並解析
        foreach ($responses as $response) {
            $response_array = json_decode($response, true);
            if ($response_array !== null && isset($response_array['message']['content'])) {
                $responses_array[] = $response_array['message']['content'];
            }
        }

        // 將所有內容組合成一個字符串
        $combined_response = implode("", $responses_array);

        // 將模型的回答添加到對話歷史中
        $_SESSION['messages'][] = [
            'role' => 'assistant',
            'content' => $combined_response
        ];
    }

    // 關閉cURL會話
    curl_close($ch);
}

// 如果有清除對話的請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear'])) {
    $_SESSION['messages'] = [];
    if (empty($_SESSION['messages'])) {
        echo "對話已清除。";
    } else {
        echo "繼續對話";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>AI對話</title>
    <meta charset="UTF-8">
    <style>
        /* 對話框外部容器 */
        .chat-container {
            border: 1px solid #ccc;
            border-radius: 5px;
            overflow: hidden;
        }

        /* 聊天框容器 */
        .conversation {
            width: calc(100% - 50px);
            /* 假設每個頭像寬度為 50px */
            height: 600px;
            overflow-y: auto;
            padding: 10px;
        }

        /* 訊息容器 */
        .message {
            margin-bottom: 10px;
            clear: both;
            overflow: hidden;
        }

        /* 頭像容器 */
        .avatar {
            display: inline-block;
            vertical-align: top;
            /* 將頭像垂直對齊到對話框的頂部 */
            margin-top: 5px;
            /* 調整頭像與對話框上邊緣的間距 */
        }

        /* 本地用戶訊息 */
        .local {
            text-align: right;
        }

        /* 本地用戶文字訊息背景顏色 */
        .local .text {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            background-color: #DCF8C6;
            max-width: 70%;
        }

        /* 本地用戶頭像 */
        .local .avatar {
            float: right;
            margin-left: 10px;
        }

        /* 遠端用戶訊息 */
        .remote {
            text-align: left;
        }

        /* 遠端用戶文字訊息背景顏色 */
        .remote .text {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 10px;
            background-color: #EAEAEA;
            max-width: 70%;
        }

        /* 遠端用戶頭像 */
        .remote .avatar {
            float: left;
            margin-right: 10px;
        }

        /* 頭像圖片 */
        .avatar .pic img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }

        /* 用戶名稱 */
        .name {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 2px;
        }

        /* 下拉式選單樣式 */
        .input-container select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
            margin-right: 10px;
        }

        /* 輸入容器 */
        .input-container {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
            background-color: #f9f9f9;
        }

        /* 輸入框樣式 */
        .input-container textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 20px;
            font-size: 16px;
            margin-right: 10px;
            width: 800px;
            height: 100px;
            /* 調整高度 */
            resize: vertical;
            /* 允許垂直調整大小 */
        }

        /* 送出按鈕樣式 */
        .input-container button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
        }

        /* 清除對話按鈕樣式 */
        .input-container button[name="clear"] {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 10px;
        }

        /* Loader styles */
        .loader {
            --w: 10ch;
            font-weight: bold;
            font-family: monospace;
            font-size: 30px;
            letter-spacing: var(--w);
            width: var(--w);
            overflow: hidden;
            white-space: nowrap;
            text-shadow:
                calc(-1*var(--w)) 0,
                calc(-2*var(--w)) 0,
                calc(-3*var(--w)) 0,
                calc(-4*var(--w)) 0,
                calc(-5*var(--w)) 0,
                calc(-6*var(--w)) 0,
                calc(-7*var(--w)) 0,
                calc(-8*var(--w)) 0,
                calc(-9*var(--w)) 0;
            animation: l16 2s infinite;
            display: none;
        }

        .loader:before {
            content: "Loading...";
        }

        @keyframes l16 {
            20% {
                text-shadow:
                    calc(-1*var(--w)) 0,
                    calc(-2*var(--w)) 0 red,
                    calc(-3*var(--w)) 0,
                    calc(-4*var(--w)) 0 #ffa516,
                    calc(-5*var(--w)) 0 #63fff4,
                    calc(-6*var(--w)) 0,
                    calc(-7*var(--w)) 0,
                    calc(-8*var(--w)) 0 green,
                    calc(-9*var(--w)) 0;
            }

            40% {
                text-shadow:
                    calc(-1*var(--w)) 0,
                    calc(-2*var(--w)) 0 red,
                    calc(-3*var(--w)) 0 #e945e9,
                    calc(-4*var(--w)) 0,
                    calc(-5*var(--w)) 0 green,
                    calc(-6*var(--w)) 0 orange,
                    calc(-7*var(--w)) 0,
                    calc(-8*var(--w)) 0 green,
                    calc(-9*var(--w)) 0;
            }

            60% {
                text-shadow:
                    calc(-1*var(--w)) 0 lightblue,
                    calc(-2*var(--w)) 0,
                    calc(-3*var(--w)) 0 #e945e9,
                    calc(-4*var(--w)) 0,
                    calc(-5*var(--w)) 0 green,
                    calc(-6*var(--w)) 0,
                    calc(-7*var(--w)) 0 yellow,
                    calc(-8*var(--w)) 0 #ffa516,
                    calc(-9*var(--w)) 0 red;
            }

            80% {
                text-shadow:
                    calc(-1*var(--w)) 0 lightblue,
                    calc(-2*var(--w)) 0 yellow,
                    calc(-3*var(--w)) 0 #63fff4,
                    calc(-4*var(--w)) 0 #ffa516,
                    calc(-5*var(--w)) 0 red,
                    calc(-6*var(--w)) 0,
                    calc(-7*var(--w)) 0 grey,
                    calc(-8*var(--w)) 0 #63fff4,
                    calc(-9*var(--w)) 0;
            }
        }

        .text {
            font-size: 16px;
            /* 設定文字大小為16像素 */
        }

        /* 將 <pre> 標籤中的程式碼框起來 */
        .text pre {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
            overflow-x: auto;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <div class="chat-container">
        <div class="conversation" id="conversation">
            <?php
            if (isset($_SESSION['messages'])) {
                foreach ($_SESSION['messages'] as $message) {
                    if ($message['role'] === 'assistant') {
                        // 將 AI 的回應用 <pre> 標籤包裹，並在 <pre> 標籤中使用 htmlspecialchars 函數轉換 HTML 特殊字符
                        echo "<div class='remote'>
                            <div class='avatar'>
                                <div class='pic'><img src='.\img\自行設定.jpg' /></div>
                                <div class='name'>AI助手</div>
                            </div>
                            <div class='text'><pre><code>" . htmlspecialchars($message['content']) . "</code></pre></div>
                        </div><br>";
                    } else {
                        // 將用戶的回應用 <code> 標籤包裹，並使用 htmlspecialchars 函數轉換 HTML 特殊字符
                        echo "<div class='local'>
                            <div class='text'><code>" . htmlspecialchars($message['content']) . "</code></div>
                            <div class='avatar'>
                                <div class='pic'><img src='.\img\自行設定.jpg' /></div>
                                <div class='name'>你</div>
                            </div>
                        </div><br>";
                    }
                }
            }
            ?>

        </div>
        <div class="input-container">
            <form id="chatForm">

                <label for="model">請選擇語言模組：</label>
                <select id="model" name="model">
                    <option value="codeqwen:latest">程式問題專用語言模型</option>
                    <option value="gemma:7b">google語言模型(7b)</option>
                    <option value="wangrongsheng/taiwanllm-7b-v2.1-chat:latest">台灣語言模型(7b)</option>
                    <option value="wangrongsheng/taiwanllm-13b-v2.0-chat:latest">台灣語言模型(13b)</option>
                    <option value="jcai/llama3-taide-lx-8b-chat-alpha1:Q4_K_M" selected>台灣人開發語言模型(8b)</option>
                    <option value="llamafamily/llama3-chinese-8b-instruct:latest">llama3中文語言模型(8b)</option>
                    <option value="llama3-chatqa:latest">llama3-chatqa語言模型</option>
                    <option value="llama3:latest">llama3語言模型</option>
                </select><label style="color: #f44336;">(7b、8b、13b...代表資料量大小)</label>
                <br><br>
                <label for="question">請輸入問題：</label>
                <textarea id="question" name="question" placeholder="請輸入問題" autocomplete="off" required></textarea>
                <button type="submit">送出</button>
                <button type="button" id="clearButton" name="clear">清除對話</button>
                <div class="loader"></div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#chatForm').on('submit', function(e) {
                e.preventDefault();
                $('.loader').show();
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: $(this).serialize(),
                    success: function(response) {
                        $('#conversation').load(location.href + " #conversation>*", function() {
                            var conversation = document.getElementById('conversation');
                            conversation.scrollTop = conversation.scrollHeight;
                        });
                        $('#question').val('');
                        $('.loader').hide();
                    }
                });
            });

            $('#question').keydown(function(e) {
                if (e.keyCode == 13 && !e.shiftKey) {
                    e.preventDefault();
                    $('#chatForm').submit();
                }
            });

            $('#clearButton').on('click', function() {
                $.ajax({
                    type: 'POST',
                    url: '',
                    data: {
                        clear: true
                    },
                    success: function(response) {
                        $('#conversation').html('');
                    }
                });
            });
        });
    </script>

</body>

</html>