<?php

namespace Stickee\Sync\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stickee\Sync\Database\Factories\SyncTestFactory;

class SyncTest extends Model
{
    use HasFactory;

    /**
     * If the model includes timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return SyncTestFactory::new();
    }
}
