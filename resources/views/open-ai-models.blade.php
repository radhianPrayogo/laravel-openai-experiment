<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel with OpenAI</title>

        <!-- Fonts -->
        <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
        <script src="https://code.jquery.com/jquery-3.7.0.min.js" integrity="sha256-2Pmvv0kuTBOenSvLm6bvfBSSHrUJ+3A7x6P5Ebd07/g=" crossorigin="anonymous"></script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

        <style>
            body {
                font-family: 'Space Grotesk', sans-serif;
            }
            .title:empty:before {
                content:attr(data-placeholder);
                color:gray
            }
            .form-control-description {
                min-height: calc(1.5em + .75rem + 2px) !important; 
                height: auto !important;
            }

            .bg-dark {
                background-color: #25293c !important;
            }

            .shadow {
                box-shadow: 0 1rem 1rem rgba(115,103,240,.15) !important;
            }

            .card {
                border: 1px solid #7367f0;
            }
        </style>

        <script type="text/javascript">
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
                $('#type').val(null).trigger('change')
                $('#input-command').val('')
                $('#total-sample').val(1)

                $("#form-total-sample").keypress(function (e){
                  var charCode = (e.which) ? e.which : e.keyCode;
                  if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                    return false;
                  }
                });

                $('#image1').change(function(){
                    const file = this.files[0];
                    if (file){
                      let reader = new FileReader();
                      reader.onload = function(event){
                        $('#imgPreview').removeClass('d-none').attr('src', event.target.result);
                      }
                      reader.readAsDataURL(file);
                    }
                });

                $('#image2').change(function(){
                    const file = this.files[0];
                    if (file){
                      let reader = new FileReader();
                      reader.onload = function(event){
                        $('#imgPreview2').removeClass('d-none').attr('src', event.target.result);
                      }
                      reader.readAsDataURL(file);
                    }
                });

                $('#type').on('change', function() {
                    let val = $(this).val()

                    switch (val) {
                        case 'combine_image':
                            $('#input-command').removeClass('d-none').prop('required', false)
                            $('#form-image').removeClass('d-none')
                            $('#form-image-2').removeClass('d-none')
                            $('#form-total-sample').addClass('d-none')
                            $('#form-define-bot').addClass('d-none')
                            $('#image1').val(null).trigger('change')
                            $('#image2').val(null).trigger('change')
                            $('#imgPreview').attr('src', '#').addClass('d-none')
                            $('#imgPreview2').attr('src', '#').addClass('d-none')
                            break;
                        case 'image_variation':
                            $('#input-command').addClass('d-none').prop('required', false)
                            $('#form-image').removeClass('d-none')
                            $('#form-image-2').addClass('d-none')
                            $('#form-total-sample').removeClass('d-none')
                            $('#form-define-bot').addClass('d-none')
                            $('#image1').val(null).trigger('change')
                            $('#image2').val(null).trigger('change')
                            $('#imgPreview').attr('src', '#').addClass('d-none')
                            $('#imgPreview2').attr('src', '#').addClass('d-none')
                            break;
                        case 'image':
                            $('#form-total-sample').removeClass('d-none');
                            $('#input-command').removeClass('d-none').prop('required', true)
                            $('#form-image').addClass('d-none')
                            $('#form-image-2').addClass('d-none')
                            $('#imgPreview').attr('src', '').addClass('d-none')
                            $('#imgPreview2').attr('src', '').addClass('d-none')
                            $('#form-define-bot').addClass('d-none')
                            $('#image1').val(null).trigger('change')
                            $('#image2').val(null).trigger('change')
                            $('#imgPreview').attr('src', '#').addClass('d-none')
                            $('#imgPreview2').attr('src', '#').addClass('d-none')
                            break;
                        case 'defined_bot':
                            $('#input-command').removeClass('d-none').prop('required', true)
                            $('#form-image').addClass('d-none')
                            $('#form-image-2').addClass('d-none')
                            $('#form-total-sample').addClass('d-none')
                            $('#imgPreview').attr('src', '').addClass('d-none')
                            $('#imgPreview2').attr('src', '').addClass('d-none')
                            $('#image1').val(null).trigger('change')
                            $('#image2').val(null).trigger('change')
                            $('#form-define-bot').removeClass('d-none')
                            break;
                        default:
                            $('#input-command').removeClass('d-none').prop('required', true)
                            $('#form-image').addClass('d-none')
                            $('#form-image-2').addClass('d-none')
                            $('#form-total-sample').addClass('d-none')
                            $('#imgPreview').attr('src', '').addClass('d-none')
                            $('#imgPreview2').attr('src', '').addClass('d-none')
                            $('#image1').val(null).trigger('change')
                            $('#image2').val(null).trigger('change')
                            $('#form-define-bot').addClass('d-none')
                    }
                })

        		$('#form-input').submit(function(e) {
        			e.preventDefault()

        			let url = '{{ url('/write/generate') }}'
        			let params = new FormData($('#form-input')[0])
                    $('#content').empty().append(`<span class="spinner-grow spinner-grow-sm text-primary me-2" role="status" aria-hidden="true"></span><span class="text-primary">Loading...</span>`)

        			$.ajax({
					    url: url,
					    data: params,
					    cache: false,
					    contentType: false,
					    processData: false,
					    method: 'POST',
					    success: function(response) {
                            let images = ``;
                            switch (response.type) {
                                case 'image_variation':
                                    images += `<div class="row">`
                                    response.data.data.map(function(item) {
                                        images += `<div class="col-md-4"><img class="img img-fluid" src="${item.url}"></div>`
                                    })
                                    images += `</div>`
                                    $('#content').empty().append(images)
                                    $('#content-code').addClass('d-none')
                                    $('#content').removeClass('d-none')
                                    break;
                                case 'combine_image':
                                    images += `<div class="row">`
                                    response.data.data.map(function(item) {
                                        images += `<div class="col-md-4"><img class="img img-fluid" src="${item.url}"></div>`
                                    })
                                    images += `</div>`
                                    $('#content').empty().append(images)
                                    $('#content-code').addClass('d-none')
                                    $('#content').removeClass('d-none')
                                    break;
                                case 'image':
                                    images += `<div class="row">`
                                    response.data.data.map(function(item) {
                                        images += `<div class="col-md-4"><img class="img img-fluid" src="${item.url}"></div>`
                                    })
                                    images += `</div>`
                                    $('#content').empty().append(images)
                                    $('#content-code').addClass('d-none')
                                    $('#content').removeClass('d-none')
                                    break;
                                case 'chat':
                                    $('#content').empty().append(response.data)
                                    $('#content-code').addClass('d-none')
                                    $('#content').removeClass('d-none')
                                    break;
                                case 'defined_bot':
                                    $('#content-code').removeClass('d-none').empty().html(response.data)
                                    $('#content').addClass('d-none')
                                    break;
                                default:
                                    $('#content').empty().append(response.data)
                                    $('#content-code').addClass('d-none')
                                    $('#content').removeClass('d-none')
                                    break;
                            }
					    },
					    error: function(err) {
					    	console.log(err)
					    }
					});
        		})
        	})
        </script>

    </head>
    <body class="antialiased bg-dark">
        <div class="row p-5">
            <div class="col-1"></div>
            <div class="col-10 text-center">
                
                <div class="card shadow bg-dark">
                    <div class="card-body">
                        <div class="text-center text-gray py-4">
                            <h1 class="font-bold text-primary">OpenAI Models</h1>
                        </div>

                        <div class="row">
                            <div class="col-md-12 rounded-md border-2 border-gray p-4 h-full text-gray">
                                <form method="post" id="form-input" class="inline-flex gap-2 w-full">
                                    @csrf
                                    <center>
                                        <select class="form-control mb-3 w-50" name="type" id="type" placeholder="Select Feature" required>
                                            <option value="">- Select Type -</option>
                                            <option value="chat">Chat</option>
                                            <option value="defined_bot">Defined Bot</option>
                                            <option value="image">Create Image</option>
                                            <option value="combine_image">Combine Image</option>
                                            <option value="image_variation">Image Variation</option>
                                        </select>
                                    </center>
                                    <div class="form-group text-start d-none" id="form-image">
                                        <label class="label text-light">Image</label>
                                        <input type="file" name="image" id="image1" class="form-control">
                                        <img id="imgPreview" src="#" alt="preview" class="img-thumbnail d-none" style="max-width: 300px;" />
                                    </div>
                                    <div class="form-group text-start d-none" id="form-image-2">
                                        <label class="label text-light">Image 2</label>
                                        <input type="file" name="image2" id="image2" class="form-control">
                                        <img id="imgPreview2" src="#" alt="preview" class="img-thumbnail d-none" style="max-width: 300px;" />
                                    </div>
                                    <div class="form-group text-start d-none mb-3" id="form-total-sample">
                                        <label class="label text-light">Total Variation</label>
                                        <input type="text" name="total_sample" class="form-control" style="width: 100px;" id="total-sample">
                                    </div>
                                    <div class="form-group text-start d-none mb-3" id="form-define-bot">
                                        <label class="label text-light">Define Bot</label>
                                        <input type="text" name="define_bot" class="form-control" id="define-bot">
                                    </div>
                                    <textarea required name="command" id="input-command" class="form-control" placeholder="Ketikkan perintah disini" rows="3"></textarea>
                                    <button type="submit" class="btn btn-primary w-100 mt-2">Generate</button>
                                </form>
                            </div>
                            <div class="col-md-12 rounded-md border-2 border-gray-600 p-4">
                                <center>
                                    <h5 class="text-primary">Result:</h5>
                                </center>
                                <p class="form-control form-control-description" id="content"></p>
                                <code id="content-code" class="d-none form-control form-control-description text-start"></code>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </body>
</html>