<!DOCTYPE html>
<html>
<head>
    <title>Speaker Authenticator</title>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/recorderjs/0.1.0/recorder.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="card">
        	<div class="card-body">
        		<center>
        			<h1>Validate Speaker</h1>
        		</center>

		        <div class="row">
		        	<div class="col-md-12">
		        		<div class="header">Speaker 1</div>
				        <div class="audio">
				            <audio id="audio1" controls></audio>
				            <div>
				                <button class="btn btn-sm btn-success" id="btnstartaudio1" onclick="startRecording('audio1')">Start</button>
				                <button class="btn btn-sm btn-danger" id="btnstopaudio1" onclick="stopRecording('audio1')" style="display: none;">Stop</button>
				            </div>
				        </div>
		        	</div>

		        	<div class="col-md-12">
				        <div class="header">Speaker 2</div>
				        <div class="audio">
				            <audio id="audio2" controls></audio>
				            <div>
				                <button class="btn btn-sm btn-success" id="btnstartaudio2" onclick="startRecording('audio2')">Start</button>
				                <button class="btn btn-sm btn-danger" id="btnstopaudio2" onclick="stopRecording('audio2')" style="display: none;">Stop</button>
				            </div>
				        </div>
				    </div>

				    <div class="col-md-12">
				        <center>
				        	<button class="btn btn-sm btn-primary" id="btncompare" onclick="compare()">Compare</button>
					        <div id="output" class="w-100 border border-secondary"></div>
				        </center>
				    </div>
		        </div>
        	</div>
        </div>
    </div>
    
    <script defer type="text/javascript">
        var audio_context;
        var recorder;

        function startAudioRecorder(stream) {
            var input = audio_context.createMediaStreamSource(stream);
            recorder = new Recorder(input);
        }

        window.onload = function init() {
            try {
                window.AudioContext = window.AudioContext || window.webkitAudioContext;
                navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia;
                window.URL = window.URL || window.webkitURL;

                audio_context = new AudioContext;
            } catch (e) {
                alert("Web Audio API is not supported in this browser");
            }

            navigator.getUserMedia({ audio: true }, startAudioRecorder, function (e) {
                alert("No live audio input in this browser");
            });
        }

        function compare() {
            fetch('https://hf.space/gradioiframe/microsoft/unispeech-speaker-verification/api/predict', {
                method: 'POST',
                body: JSON.stringify({
                    "data": [
                        { "data": document.getElementById('audio1').src, "name": "audio1.wav" },
                        { "data": document.getElementById('audio2').src, "name": "audio2.wav" }
                    ]
                }), headers: {
                    'Content-Type': 'application/json'
                }
            }).then(function (response) {
                return response.json();
            }).then(function (data) {
                if (data != null && data.data.length > 0) {
                    if (data.data[0] != null) {
                        document.getElementById("output").innerHTML = data.data[0];
                    }
                }
            });
        }
        
        function startRecording(ele) {
            recorder.clear();
            recorder.record();

            document.getElementById('btnstop' + ele).style = "display: inline-block;";
            document.getElementById('btnstart' + ele).style = "display: none;";
        }

        function stopRecording(ele) {
            recorder.stop();
            createDownloadLink(ele);

            document.getElementById('btnstop' + ele).style = "display: none;";
            document.getElementById('btnstart' + ele).style = "display: inline-block;";
        }

        function createDownloadLink(ele) {
            var player = document.getElementById(ele);
            recorder && recorder.exportWAV(function (blob) {
                var filereader = new FileReader();
                filereader.addEventListener("load", function () {
                    player.src = filereader.result;
                }, false);
                filereader.readAsDataURL(blob);
            });
        }
    </script>
</body>
</html>