<?php

namespace Tests\Feature;

use App\Models\AiChat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AiCoachTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGroq(): void
    {
        Http::fake(['api.groq.com/*' => Http::response(['choices' => [['message' => ['content' => 'Drink water.']]]])]);
    }

    public function test_earlier_messages_in_the_session_are_sent_to_the_model(): void
    {
        $this->fakeGroq();
        $user = User::factory()->create();
        $session = '7b0d6a52-5a1f-4f0e-9f53-3b5e3d1c2a10';
        AiChat::create(['user_id' => $user->id, 'session_id' => $session, 'user_message' => 'My name is Sam', 'ai_reply' => 'Hi Sam!']);

        $this->actingAs($user)->postJson('/ai/meal-assistant', ['prompt' => 'What is my name?', 'session_id' => $session])
            ->assertOk()->assertJson(['reply' => 'Drink water.', 'session_id' => $session]);

        Http::assertSent(function (Request $request) {
            $contents = array_column($request['messages'], 'content');

            return in_array('My name is Sam', $contents, true)
                && in_array('Hi Sam!', $contents, true)
                && end($contents) === 'What is my name?';
        });
    }

    public function test_the_endpoint_is_rate_limited(): void
    {
        $this->fakeGroq();
        $user = User::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)->postJson('/ai/meal-assistant', ['prompt' => "Hi {$i}"])->assertOk();
        }

        $this->actingAs($user)->postJson('/ai/meal-assistant', ['prompt' => 'One more'])->assertStatus(429);
    }
}
