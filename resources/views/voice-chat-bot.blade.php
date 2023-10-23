<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Virtual Assistant</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="{{ asset('css/styles.css') }}" rel="stylesheet" />

        <style type="text/css">
            path {
                stroke-linecap: square;
                stroke: white;
                stroke-width: 0.5px;
            }
        </style>
    </head>
    <body id="page-top">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg bg-secondary text-uppercase fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">Voice Chat Bot</a>
                <button class="navbar-toggler text-uppercase font-weight-bold bg-primary text-white rounded" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item mx-0 mx-lg-1">
                            <a class="nav-link py-3 px-0 px-lg-3 rounded" href="#">
                                <div class="spinner-grow text-success d-none" id="recording-loader" role="status">
                                  <span class="visually-hidden">Loading...</span>
                                </div>
                            </a>
                        </li>
                        <li class="nav-item mx-0 mx-lg-1">
                            <a class="nav-link py-3 px-0 px-lg-3 rounded" id="start-btn" data-status="idle" href="#">
                                Start To Speak
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Masthead-->
        <header class="masthead bg-primary text-white text-center">
            <div class="container d-flex align-items-center flex-column">
                <select class="form-control" style="width: 30%;" id="select-voice"></select>
                <input class="form-control mt-2" style="width: 35%;" id="bot-name" placeholder="Bot Name" />
                <!-- Masthead Avatar Image-->
                {{-- start visualizer --}}
                <svg preserveAspectRatio="none" style="width: 500px; height: 250px; display: none;" id="visualizer" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                    <defs>
                        <mask id="mask">
                            <g id="maskGroup">
                          </g>
                        </mask>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                            <stop offset="0%" style="stop-color:#ff0a0a;stop-opacity:1" />
                            <stop offset="20%" style="stop-color:#f1ff0a;stop-opacity:1" />
                            <stop offset="90%" style="stop-color:#d923b9;stop-opacity:1" />
                            <stop offset="100%" style="stop-color:#050d61;stop-opacity:1" />
                        </linearGradient>
                    </defs>
                    <rect x="0" y="0" width="100%" height="100%" fill="url(#gradient)" mask="url(#mask)"></rect>
                </svg>
                {{-- end visualizer --}}
                <!-- Masthead Heading-->
                <h1 class="masthead-heading text-uppercase mb-0" id="chatbot-name"></h1>
                <!-- Icon Divider-->
                <div class="divider-custom divider-light">
                    <div class="divider-custom-line"></div>
                    <div class="divider-custom-icon"><i class="fas fa-star"></i></div>
                    <div class="divider-custom-line"></div>
                </div>

                <p id="voice-text"></p>
            </div>
        </header>
        <!-- Copyright Section-->
        <div class="copyright py-4 text-center text-white">
            <div class="container"><small>Copyright &copy; Your Website 2023</small></div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="contentModal" tabindex="-1" role="dialog" aria-labelledby="contentModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <div class="modal-body" id="modal-body">
                {{-- modal content --}}
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
              </div>
            </div>
          </div>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <!-- Bootstrap core JS-->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- Core theme JS-->
        <script src="{{ asset('js/scripts.js') }}"></script>

        {{-- script speech to text --}}
        <script>
            $.ajaxSetup({
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              xhrFields: {
                withCredentials: true
              },
              dataType: 'json'
            });

            $(document).ready(function() {
                // $('#contentModal').modal('show')
                $(document).on('hidden.bs.modal', '#contentModal', function() {
                    document.getElementById('modal-body')
                    $('#modal-body').html('')
                })
            })

            var SpeechRecogntion = window.webkitSpeechRecognition;
            var recognition = new window.SpeechRecogntion();

            recognition.lang = 'id-ID';
            var recordingLoader = $('#recording-loader');
            var content = '';
            var chatList = [];
            recognition.continuous = true

            recognition.onstart = function() {
                // voice recognition is on
                recordingLoader.removeClass('d-none').removeClass('text-danger').addClass('text-success')
                $('#start-btn').data('status', 'recording').html('Stop Speaking')
            }

            recognition.onspeechend = function() {
                console.log('no activity')
                // no activity
                content = ''
                chatList  = []
                $('#voice-text').html('')
                recordingLoader.removeClass('text-danger').removeClass('text-warning').addClass('text-success').addClass('d-none')
                $('#start-btn').data('status', 'idle').html('Start to speak')
            }

            recognition.onerror = function(event) {
                console.log('error')
                console.log(event);
                recordingLoader.removeClass('d-none').removeClass('text-success').addClass('text-danger')
                recognition.stop()
            }

            recognition.onresult = function(event) {
                var current = event.resultIndex;
                if (current === 0) {
                    var transcript = event.results[current][0].transcript;
                    var confidence = event.results[current][0].confidence;
                    content = transcript;
                    recognition.stop()
                    console.log(content)
                    
                    if (content != '') {
                        chatBotRequest(content)
                        content = ''
                    }
                }
            };

            recognition.onend = function(event) {
                content = ''
                chatList  = []
                $('#voice-text').html('')
                recordingLoader.removeClass('text-danger').removeClass('text-warning').addClass('text-success').addClass('d-none')
                $('#start-btn').data('status', 'idle').html('Start to speak')
            }

            $('#start-btn').click(function(event) {
                let status = $(this).data('status')
                if (status == 'idle') {
                    if (content.length) {
                        content += ''
                    }

                    recognition.start()
                } else {
                    recognition.stop()
                }
            });
        </script>

        {{-- script visualizer --}}
        <script>
            function showVisualizer (status) {
                "use strict";
                var paths = document.getElementsByTagName('path');
                var visualizer = document.getElementById('visualizer');
                var mask = visualizer.getElementById('mask');
                var path;
                var report = 0;
                
                var soundAllowed = function (stream) {
                    //Audio stops listening in FF without // window.persistAudioStream = stream;
                    //https://bugzilla.mozilla.org/show_bug.cgi?id=965483
                    //https://support.mozilla.org/en-US/questions/984179
                    window.persistAudioStream = stream;
                    var audioContent = new AudioContext();
                    var audioStream = audioContent.createMediaStreamSource( stream );
                    var analyser = audioContent.createAnalyser();
                    audioStream.connect(analyser);
                    analyser.fftSize = 1024;

                    var frequencyArray = new Uint8Array(analyser.frequencyBinCount);
                    visualizer.setAttribute('viewBox', '0 0 255 255');
                  
                    //Through the frequencyArray has a length longer than 255, there seems to be no
                    //significant data after this point. Not worth visualizing.
                    for (var i = 0 ; i < 255; i++) {
                        path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                        path.setAttribute('stroke-dasharray', '4,1');
                        mask.appendChild(path);
                    }
                    var doDraw = function () {
                        requestAnimationFrame(doDraw);
                        analyser.getByteFrequencyData(frequencyArray);
                        var adjustedLength;
                        for (var i = 0 ; i < 255; i++) {
                            adjustedLength = Math.floor(frequencyArray[i]) - (Math.floor(frequencyArray[i]) % 5);
                            paths[i].setAttribute('d', 'M '+ (i) +',255 l 0,-' + adjustedLength);
                        }
                    }
                    doDraw();
                }

                var soundNotAllowed = function (error) {
                    h.innerHTML = "You must allow your microphone.";
                    console.log(error);
                }

                /*window.navigator = window.navigator || {};
                /*navigator.getUserMedia =  navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || null;*/
                navigator.getUserMedia({audio:true}, soundAllowed, soundNotAllowed);

                if (status == true){
                    $('#visualizer').css('display', 'block')
                } else {
                    $('#visualizer').css('display', 'none')
                }
            };
        </script>

        {{-- script text to speech --}}
        <script>
            $(function(){
                if ('speechSynthesis' in window) {
                    speechSynthesis.onvoiceschanged = function() {
                      var $voicelist = $('#select-voice');

                      if($voicelist.find('option').length == 0) {
                        speechSynthesis.getVoices().forEach(function(voice, index) {
                            if (voice.localService === true) {
                                let lang = voice.lang.split('-')
                                var $option = $('<option>').val(index).html(voice.name).attr('data-lang', lang[0]);

                                if (voice.lang == 'id-ID') {
                                    $option.prop('selected', true)
                                }

                                $voicelist.append($option);
                            }
                        });
                      }
                    }
                } else {
                    console.log('speechSynthesis Not Found')
                }

                $('#bot-name').on('change', function(){
                    $('#chatbot-name').html($(this).val())
                })
            });

            function chatBotRequest(text)
            {
                console.log('send request')
                let url = '{{ url('voice-chat-bot/interact') }}'
                let bot_name = $('#bot-name').val()
                if (bot_name == '') {
                    recognition.stop()
                    textToSpeech('{{ __('command.please_give_me_a_name_first') }}')
                    return false
                }

                chatList = []

                let system = {'role': 'system', 'content': bot_name}
                chatList.unshift(system) //add system param to first array

                let message = {'role': 'user', 'content': text}
                chatList.push(message)

                let params = JSON.stringify(chatList)

                $.post(url, { message: chatList}, function(response) {
                    console.log(JSON.stringify(response.data))
                    recognition.stop()

                    if (response.data.type == 'image') {
                        response.data.content.map(function(item) {
                            let img = $('<img />')
                            img.attr('src', item)
                            img.addClass('img img-fluid me-3')
                            img.appendTo('#modal-body')
                        })

                        $('#contentModal').modal('show')
                    } else if (response.data.type == 'script') {
                        $('#modal-body').append('<iframe id="content-iframe" style="width:100%; height:400px;"></iframe>')
                        let inside_script = response.data.content.replace('<script>','')
                        inside_script = inside_script.replace('<\/script>', '')

                        response.data.content = response.data.content.replace('<script', '<script id="content-script"')
                        let id = response.data.content.match(/getElementById\(([^)]+)\)/)
                        let canvas = ''
                        if (id != null) {
                            canvas = `<canvas id=${id[1]}></canvas>`
                        }
                        $('#content-iframe').contents().find('body').html(`
                            <!DOCTYPE html>
                            <html>
                                <head>
                                    <meta charset="utf-8">
                                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                                    <title></title>
                                    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"><\/script>
                                    ${response.data.content}
                                    <script>
                                        eval(document.getElementById("content-script").innerHTML)
                                    <\/script>
                                </head>
                                <body>
                                    ${canvas}
                                </body>
                            </html>
                        `)
                        $('#modal-body').append(`<pre class="border border-primary">${inside_script}</pre>`)
                        $('#contentModal').modal('show')
                    } else {
                        textToSpeech(response.data.content.content)
                    }
                }).fail((err) => {
                    console.log(err.responseJSON.message)
                })
            }

            function textToSpeech(text){
                var msg = new SpeechSynthesisUtterance();
                var voices = window.speechSynthesis.getVoices();
                msg.voice = voices[$('#select-voice').val()];
                msg.rate = 1;
                msg.pitch = 1;
                msg.text = text;

                msg.onend = function(e) {
                    showVisualizer(false)
                    console.log('Finished in ' + event.elapsedTime + ' seconds.');
                    recognition.start()
                };

                showVisualizer(true)
                speechSynthesis.speak(msg);
            }
        </script>
    </body>
</html>
