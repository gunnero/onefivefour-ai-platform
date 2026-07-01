<?php

namespace App\Models;

use Database\Factories\SopCapabilityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class SopCapability extends Pivot
{
    /** @use HasFactory<SopCapabilityFactory> */
    use HasFactory;

    protected $table = 'sop_capabilities';

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

    public function capability(): BelongsTo
    {
        return $this->belongsTo(Capability::class);
    }
}
