<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Laravel\Ai\Models\ConversationMessage as ConversationMessageModel;

#[Hidden(['user_id'])]
class ConversationMessage extends ConversationMessageModel
{
    //
}