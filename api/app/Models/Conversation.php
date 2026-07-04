<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Laravel\Ai\Models\Conversation as ConversationModel;

#[Hidden(['user_id'])]
class Conversation extends ConversationModel
{
    //
}