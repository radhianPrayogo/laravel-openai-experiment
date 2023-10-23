<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Repositories\OpenAiRepository;
use App\Http\Requests\VoiceChatBotRequest;

class VoiceChatBotController extends Controller
{
    protected $repository;

    function __construct(OpenAiRepository $repository) {
        $this->repository = $repository;
    }

    public function index() {
        return view('voice-chat-bot');
    }

    public function getResponse(VoiceChatBotRequest $request)
    {
        $message = $request->message;
        $speech_text = $message[1]['content'];
        
        $content = $this->repository->getOpenAiResponseByCommand($speech_text, $message);
        
        $result = [
            'code' => 200,
            'status' => true,
            'message' => __('Success'),
            'data' => $content
        ];
        return response()->json($result, $result['code']);
    }
}
