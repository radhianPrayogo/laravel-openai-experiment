<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use OpenAI;
use App\Http\Repositories\OpenAiRepository;
use App\Http\Requests\OpenAIModelRequest;

class OpenAIGeneratorController extends Controller
{
    function __construct(OpenAiRepository $repository) {
        $this->client = OpenAI::client(config('services.open_ai.secret'));
        $this->repository = $repository;
    }

    public function index()
    {
        return view('open-ai-models');
    }

    public function getResponse(OpenAIModelRequest $request)
    {
        $type = $request->type;
        
        $content = $this->repository->getOpenAiResponse($type, $request);

        $result = [
            'code' => 200,
            'status' => true,
            'message' => __('Success'),
            'data' => $content,
            'type' => $type
        ];
        return response()->json($result, $result['code']);
    }
}
