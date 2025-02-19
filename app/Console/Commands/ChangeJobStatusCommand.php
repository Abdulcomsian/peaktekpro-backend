<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\CompanyJob;
use App\Models\ReadyToBuild;
use Illuminate\Console\Command;

class ChangeJobStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jobs:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Change status of jobs that are one day away from starting.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tomorrow = Carbon::now()->addDay();
        $jobs = ReadyToBuild::where(function ($query) use ($tomorrow) {
            $query->where('date', $tomorrow->format('d/m/Y'));

            // Include jobs starting on weekends (Saturday or Sunday)
            $query->orWhere(function ($query) use ($tomorrow) {
                $query->where(function ($query) use ($tomorrow) {
                    // Check if tomorrow is Saturday or Sunday
                    $query->where('date', Carbon::parse($tomorrow)->startOfWeek(Carbon::SUNDAY)->addDays(6)->format('d/m/Y'))
                        ->orWhere('date', Carbon::parse($tomorrow)->startOfWeek(Carbon::SUNDAY)->addDays(7)->format('d/m/Y'));
                });
            });
        })->get();
        foreach ($jobs as $job) {
            //Update Job Status
            $companyJob = CompanyJob::find($job->company_job_id);
            $companyJob->status_id = 11;
            $companyJob->save();
        }

        $this->info('Job statuses updated successfully.');
    }
}
