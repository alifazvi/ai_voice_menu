<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenAIKnowledgeBase extends Model
{
    // Match the migration which creates the `knowledge_bases` table
    protected $table = 'knowledge_bases';
    protected $fillable = ['name', 'file_ids', 'embeddings', 'vector_store_id', 'restaurant_id'];
    protected $casts = [
        'file_ids' => 'array',
        'embeddings' => 'array',
    ];
}
