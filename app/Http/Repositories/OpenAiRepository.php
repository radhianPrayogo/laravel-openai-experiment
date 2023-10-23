<?php

namespace App\Http\Repositories;

use OpenAI;

class OpenAiRepository
{
	function __construct()
	{
		$this->client = OpenAI::client(config('services.open_ai.secret'));
	}

	public function getOpenAiResponse($type, $request)
	{
		switch ($type) {
            case 'chat':
                $command = $request->command;
            
                $response = $this->client->chat()->create([
                    "model" => "gpt-4",
                    'max_tokens' => 1000,
                    'messages' => [
                        ['role' => 'user', 'content' => $command]
                    ]
                ]);

                $content = $response['choices'][0]['message']['content'];
                break;
            case 'defined_bot':
                if (!$request->define_bot) {
                    $result = [
                        'code' => 400,
                        'status' => false,
                        'message' => __('Please define bot first')
                    ];

                    return response()->json($result, $result['code']);
                }

                $command = $request->command;
                $define_bot = $request->define_bot;

                $response = $this->client->chat()->create([
                    "model" => "gpt-4",
                    'max_tokens' => 1000,
                    'messages' => [
                        ['role' => 'system', 'content' => $define_bot],
                        ['role' => 'user', 'content' => $command]
                    ],
                ]);

                $content = $response['choices'][0]['message']['content'];
                break;
            case 'image':
                $command = $request->command;
                $sample = $request->total_sample ? $request->total_sample : 1;

                $response = $this->client->images()->create([
                    'prompt' => $command,
                    'n' => (int) $sample,
                    'size' => '512x512',
                    'response_format' => 'url',
                ]);

                foreach ($response->data as $data) {
                    $data->url;
                }
                $content = $response->toArray();   
                break;
            case 'combine_image':
                if (!$request->hasFile('image') || !$request->hasFile('image2')) {
                    $result = [
                        'code' => 400,
                        'status' => false,
                        'message' => __('Image cannot be empty')
                    ];

                    return response()->json($result, $result['code']);
                }

                $command = $request->command;
                $sample = $request->total_sample ? $request->total_sample : 1;

                $response = $this->client->images()->edit([
                    'image' => fopen($request->file('image'), 'r'),
                    'mask' => fopen($request->file('image2'), 'r'),
                    'prompt' => $command,
                    'n' => (int) $sample,
                    'size' => '512x512',
                    'response_format' => 'url',
                ]);

                $response->created;

                foreach ($response->data as $data) {
                    $data->url;
                }

                $content = $response->toArray();
                break;
            case 'image_variation':
                $sample = $request->total_sample ? $request->total_sample : 1;

                $response = $this->client->images()->variation([
                    'image' => fopen($request->file('image'), 'r'),
                    'n' => (int) $sample,
                    'size' => '256x256',
                    'response_format' => 'url',
                ]);

                foreach ($response->data as $data) {
                    $data->url;
                }

                $content = $response->toArray();
                break;
        }

        return $content;
	}

	public function getOpenAiResponseByCommand($speech_text, $messages)
	{
		$speech_text = strtolower($speech_text);
		$image_making_command1 = strtolower(__('command.tolong_buatkan_saya'));
		$image_making_command2 = strtolower(__('command.buatkan_saya'));
		$image_making_command3 = strtolower(__('command.tolong_buatkan'));
		$image_making_command4 = strtolower(__('command.can_you_give_me_a_picture'));

		$speech_text = $this->removeParticleWords($speech_text);

		if (str_contains($speech_text, $image_making_command1) || str_contains($speech_text, $image_making_command2) || str_contains($speech_text, $image_making_command3) || str_contains($speech_text, $image_making_command4)) {
			$command = $speech_text;
			$speech_text = str_replace($image_making_command1, '', $speech_text);
			$speech_text = str_replace($image_making_command2, '', $speech_text);
			$speech_text = str_replace($image_making_command3, '', $speech_text);
			$speech_text = str_replace($image_making_command4, '', $speech_text);

			switch (true) {
				case str_contains($speech_text, strtolower(__('command.gambar'))):
						$speech_text = str_replace(strtolower(__('command.gambar')), '', $speech_text);
						$last_word = $this->getLastWord($speech_text);
						$total_image = 1;
						if ($last_word == 'sample' || $last_word == 'sampel') {
							$speech_text = str_replace(' '.$last_word, '', $speech_text);
							$image_count = $this->getLastWord($speech_text);
							if (is_numeric($image_count)) {
								$total_image = (int) $image_count;
							}
						}

						$response = $this->client->images()->create([
						    'prompt' => $speech_text,
						    'n' => $total_image,
						    'size' => '256x256',
						    'response_format' => 'url',
						]);

						foreach ($response->data as $data) {
						    $data->url;
						}
						$result = $response->toArray();

						$contents = [];
						foreach ($result['data'] as $item) {
							array_push($contents, $item['url']);
						}

						return [
							'type' => 'image',
							'content' => $contents,
							'command' => $command,
						];
					break;
				case str_contains($speech_text, strtolower(__('command.script'))):
						$response = $this->client->completions()->create([
				            "model" => "gpt-4",
				            "temperature" => 0,
				            'max_tokens' => 2000,
				            'prompt' => $command,
				        ]);

						return [
							'type' => 'script',
							'content' => $response['choices'][0]['message']['content'],
							'command' => $command
						];
					break;
				default:
						$response = $this->client->completions()->create([
				            "model" => "gpt-4",
				            "temperature" => 0,
				            'max_tokens' => 2000,
				            'prompt' => $command,
				        ]);

						return [
							'type' => 'script',
							'content' => $response['choices'][0]['message']['content'],
							'command' => $command,
							'speech_text' => $speech_text
						];
					break;
			}
		} else {
			$response = $this->client->chat()->create([
	            "model" => "gpt-3.5-turbo",
	            "temperature" => 0,
	            'max_tokens' => 2000,
	            'messages' => $messages,
	        ]);

	        return [
	        	'type' => 'chat',
	        	'content' => $response['choices'][0]['message']
	        ];
		}
	}

	public function removeParticleWords($speech_text)
	{
		$particle_words = [
			strtolower(__('command.dong')), 
			strtolower(__('command.kan')), 
			strtolower(__('command.ya'))
		];
		$speech_text_array = explode(' ', $speech_text);
		$last_word = $speech_text_array[count($speech_text_array) - 1];
		if (in_array($last_word, $particle_words)) {
			$speech_text_array = array_pop($speech_text_array);
			$speech_text = implode(' ', $speech_text_array);
		}

		return $speech_text;
	}

	public function getLastWord($string)
	{
		$last_word_start = strrpos($string, ' ') + 1; // +1 so we don't include the space in our result
		$last_word = substr($string, $last_word_start);
		return $last_word;
	}
}