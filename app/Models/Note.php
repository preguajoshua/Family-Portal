<?php

namespace App\Models;

use App\Models\Concerns\UsesUuid;
use App\Models\Concerns\AppDbBase;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Note extends AppDbBase
{
    use HasFactory, UsesUuid;

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'Id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the user that owns the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'UserId');
    }

    /**
     * Get the owners name.
     *
     * @return  string
     */
    public function getAuthorAttribute()
    {
        if (! $this->user) {
            return 'Unknown';
        }

        return trim($this->user->name);
    }
}
