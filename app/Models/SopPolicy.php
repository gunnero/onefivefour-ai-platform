<?php

namespace App\Models;

use Database\Factories\SopPolicyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SopPolicy extends Pivot
{
    /** @use HasFactory<SopPolicyFactory> */
    use HasFactory;

    protected $table = 'sop_policies';

    protected $guarded = [];

    public $incrementing = true;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function standardOperatingProcedure(): BelongsTo
    {
        return $this->belongsTo(StandardOperatingProcedure::class);
    }

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
