<?php

namespace Tests\Feature;

use Tests\TestCase;
use function Laravel\Ai\{agent};

class AiPromptTest extends TestCase
{

    public function test_gemini_prompt_explain_laravel(): void
    {
        $response = agent(
            instructions: 'You are an expert at software development. Answer concisely.',
            messages: [],
            tools: [],
        )->prompt(
            'Explain in one sentence what Laravel is. Respond in no more than 20 words. Start the text with: "Laravel is..."',
            [],
            provider: 'gemini',
            model: 'gemini-3.1-flash-lite'
        );

        if (is_callable([$response, 'text'])) {
            $text = $response->text();
        } elseif (isset($response->text)) {
            $text = $response->text;
        } else {
            $text = (string) $response;
        }

        $this->assertIsString($text);
        $this->assertNotEmpty(trim($text));
        $this->assertStringContainsString('Laravel', $text);
    }
}
