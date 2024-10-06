<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Speech to Text</title>
    <style>
        #speechButton {
            position: fixed;
            top: 10px;
            right: 10px;
            padding: 10px 20px;
            font-size: 16px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #speechButton:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
        }
        #transcript {
            margin-top: 50px;
            padding: 20px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<button id="speechButton">Start Speech to Text</button>
<div id="transcript" contenteditable="true">Your speech will appear here...</div>

<script>
    var speechButton = document.getElementById('speechButton');
    var transcriptDiv = document.getElementById('transcript');
    var recognition;

    if ('webkitSpeechRecognition' in window) {
        recognition = new webkitSpeechRecognition();
        recognition.continuous = true;
        recognition.interimResults = true;
        recognition.lang = 'en-US';

        recognition.onstart = function() {
            speechButton.textContent = 'Stop Speech to Text';
            speechButton.style.backgroundColor = '#f44336';
        };

        recognition.onend = function() {
            speechButton.textContent = 'Start Speech to Text';
            speechButton.style.backgroundColor = '#4CAF50';
        };

        recognition.onresult = function(event) {
            var interimTranscript = '';
            var finalTranscript = '';

            for (var i = event.resultIndex; i < event.results.length; ++i) {
                if (event.results[i].isFinal) {
                    finalTranscript += event.results[i][0].transcript;
                } else {
                    interimTranscript += event.results[i][0].transcript;
                }
            }

            transcriptDiv.innerHTML = finalTranscript + '<span style="color: #999;">' + interimTranscript + '</span>';
        };

        speechButton.addEventListener('click', function() {
            if (recognition.recognizing) {
                recognition.stop();
                return;
            }
            recognition.start();
        });
    } else {
        speechButton.disabled = true;
        speechButton.textContent = 'Speech to Text not supported';
    }
</script>

</body>
</html>
