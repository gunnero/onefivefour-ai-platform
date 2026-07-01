<?php

namespace App\Providers;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\EmployeeCapability;
use App\Models\Policy;
use App\Models\StandardOperatingProcedure;
use App\Observers\AssignmentObserver;
use App\Observers\EmployeeCapabilityObserver;
use App\Observers\EmployeeObserver;
use App\Observers\PolicyObserver;
use App\Observers\StandardOperatingProcedureObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Employee::observe(EmployeeObserver::class);
        EmployeeCapability::observe(EmployeeCapabilityObserver::class);
        Policy::observe(PolicyObserver::class);
        StandardOperatingProcedure::observe(StandardOperatingProcedureObserver::class);
        Assignment::observe(AssignmentObserver::class);
    }
}
