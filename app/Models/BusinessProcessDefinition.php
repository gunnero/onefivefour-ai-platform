<?php

namespace App\Models;

use Database\Factories\BusinessProcessDefinitionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessProcessDefinition extends Model
{
    /** @use HasFactory<BusinessProcessDefinitionFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'input_schema' => 'array',
            'completion_criteria' => 'array',
            'default_site_required' => 'boolean',
            'metadata' => 'array',
            'activated_at' => 'datetime',
            'retired_at' => 'datetime',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function owningDepartment(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'owning_department_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_employee_id');
    }

    public function steps(): HasMany
    {
        return $this->hasMany(BusinessProcessStep::class);
    }

    public function assignmentTemplates(): HasMany
    {
        return $this->hasMany(AssignmentTemplate::class);
    }

    public function runs(): HasMany
    {
        return $this->hasMany(BusinessProcessRun::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function processEvents(): HasMany
    {
        return $this->hasMany(ProcessEvent::class);
    }
}
