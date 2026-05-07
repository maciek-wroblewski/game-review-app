<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        if ($users->count() < 2) return;

        // Create 5 random chat rooms
        for ($i = 0; $i < 5; $i++) {
            $conversation = Conversation::create();

            // Pick 2 to 4 random users for this group chat
            $chatParticipants = $users->random(rand(2, 4))->pluck('id');
            
            // This populates the 'conversation_user' PIVOT table!
            $conversation->users()->attach($chatParticipants);

            // Have those specific users send 10 random messages in the chat
            foreach (range(1, 10) as $index) {
                Message::factory()->create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $chatParticipants->random(),
                ]);
            }
        }
    }
}