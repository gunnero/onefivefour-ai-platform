<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function sites(): HasMany
    {
        return $this->hasMany(Site::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function policies(): HasMany
    {
        return $this->hasMany(Policy::class);
    }

    public function standardOperatingProcedures(): HasMany
    {
        return $this->hasMany(StandardOperatingProcedure::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function businessProcessDefinitions(): HasMany
    {
        return $this->hasMany(BusinessProcessDefinition::class);
    }

    public function businessProcessSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessStep::class);
    }

    public function assignmentTemplates(): HasMany
    {
        return $this->hasMany(AssignmentTemplate::class);
    }

    public function businessProcessRuns(): HasMany
    {
        return $this->hasMany(BusinessProcessRun::class);
    }

    public function businessProcessRunSteps(): HasMany
    {
        return $this->hasMany(BusinessProcessRunStep::class);
    }

    public function processEvents(): HasMany
    {
        return $this->hasMany(ProcessEvent::class);
    }

    public function processLogs(): HasMany
    {
        return $this->hasMany(ProcessLog::class);
    }

    public function workRequests(): HasMany
    {
        return $this->hasMany(WorkRequest::class);
    }

    public function routingDecisions(): HasMany
    {
        return $this->hasMany(RoutingDecision::class);
    }

    public function departmentQueues(): HasMany
    {
        return $this->hasMany(DepartmentQueue::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
