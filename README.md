# 使用ollama API + PHP創建多語言模組對話

### 資料夾結構  
/path/to/project  
│  
├── get_conversation.php &nbsp;(這個檔案用來取得會話歷史記錄。)<br>
├── send_message.php &nbsp;(這個文件用於處理用戶發送的新訊息。)<br>
├── clear_conversation.php &nbsp;(這個文件用於清除會話歷史記錄。)<br>
├── index.html &nbsp;(包括HTML結構、CSS樣式和JavaScript程式碼。)<br>
└── /img  
&emsp;&emsp;└── 自行設定.jpg  
<br>
![image](https://github.com/qweasd1369/LIN_ollama-API_php/assets/91960758/0b5268d2-cab0-4dbb-b166-dc1cab2ef72c)
<br>
![image](https://github.com/qweasd1369/LIN_ollama-API_php/assets/91960758/4db05bd1-5e5e-4286-9cb3-bbf41dd3c404)
<br>
### 使用到的語言模組<br>
codeqwen:latest = 程式問題專用語言模型<br>
gemma:7b = google語言模型(7b)<br>
wangrongsheng/taiwanllm-7b-v2.1-chat:latest = 台灣語言模型(7b)<br>
wangrongsheng/taiwanllm-13b-v2.0-chat:latest = 台灣語言模型(13b)<br>
jcai/llama3-taide-lx-8b-chat-alpha1:Q4_K_M selected = 台灣人開發語言模型(8b)<br>
llamafamily/llama3-chinese-8b-instruct:latest = llama3中文語言模型(8b)<br>
llama3-chatqa:latest = llama3-chatqa語言模型<br>
llama3:latest = llama3語言模型<br>
<br>
ollama API的建立和PHP撰寫說明待更新......
