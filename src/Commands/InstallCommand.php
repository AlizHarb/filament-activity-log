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

    public function __construct()
    {
        parent::__construct();

        $this->setDescription((string) __('filament-activity-log::activity.commands.install.description'));
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info(__('filament-activity-log::activity.commands.install.installing'));

        $this->info(__('filament-activity-log::activity.commands.install.publish_spatie_config'));
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Activitylog\ActivitylogServiceProvider",
            '--tag' => 'activitylog-config',
        ]);

        $this->info(__('filament-activity-log::activity.commands.install.publish_spatie_migration'));
        $this->call('vendor:publish', [
            '--provider' => "Spatie\Activitylog\ActivitylogServiceProvider",
            '--tag' => 'activitylog-migrations',
        ]);

        $this->info(__('filament-activity-log::activity.commands.install.publish_package_config'));
        $this->call('vendor:publish', [
            '--tag' => 'filament-activity-log-config',
        ]);

        $this->info(__('filament-activity-log::activity.commands.install.publish_package_migration'));
        $this->call('vendor:publish', [
            '--tag' => 'filament-activity-log-migrations',
        ]);

        if ($this->confirm(__('filament-activity-log::activity.commands.install.publish_translations'), false)) {
            $this->call('vendor:publish', [
                '--tag' => 'filament-activity-log-translations',
            ]);
        }

        if ($this->confirm(__('filament-activity-log::activity.commands.install.publish_views'), false)) {
            $this->call('vendor:publish', [
                '--tag' => 'filament-activity-log-views',
            ]);
        }

        $this->info(__('filament-activity-log::activity.commands.install.installed'));

        if ($this->confirm(__('filament-activity-log::activity.commands.install.run_migrations'))) {
            $this->call('migrate');
            $this->call('filament-activity-log:doctor');
        } else {
            $this->comment(__('filament-activity-log::activity.commands.install.migration_reminder'));
        }

        $this->newLine();
        $this->line(__('filament-activity-log::activity.commands.install.support_title'));
        $this->line(__('filament-activity-log::activity.commands.install.support_message'));
        $this->line('  <options=underscore>https://github.com/alizharb/filament-activity-log</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
