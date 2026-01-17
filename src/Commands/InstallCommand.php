<?php

declare(strict_types=1);

namespace AlizHarb\ActivityLog\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-activity-log:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Filament Activity Log package and its dependencies';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Installing Filament Activity Log...');

        $this->info('Publishing Spatie Activity Log configuration...');
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Activitylog\ActivitylogServiceProvider",
            '--tag' => 'activitylog-config',
        ]);

        $this->info('Publishing Spatie Activity Log migration...');
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Activitylog\ActivitylogServiceProvider",
            '--tag' => 'activitylog-migrations',
        ]);

        $this->info('Publishing Filament Activity Log configuration...');
        $this->call('vendor:publish', [
            '--tag' => 'filament-activity-log-config',
        ]);

        if ($this->confirm('Would you like to publish the translations?', false)) {
            $this->call('vendor:publish', [
                '--tag' => 'filament-activity-log-translations',
            ]);
        }

        if ($this->confirm('Would you like to publish the views?', false)) {
            $this->call('vendor:publish', [
                '--tag' => 'filament-activity-log-views',
            ]);
        }

        $this->info('Filament Activity Log installed successfully!');

        if ($this->confirm('Would you like to run the migrations now?')) {
            $this->call('migrate');
        } else {
            $this->comment('Please run "php artisan migrate" to create the activity logs table.');
        }

        return self::SUCCESS;
    }
}
