<?php

namespace AlizHarb\ActivityLog\Tests;

use AlizHarb\ActivityLog\ActivityLogServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        if (! class_exists(\CreateActivityLogTable::class)) {
            include __DIR__.'/../vendor/spatie/laravel-activitylog/database/migrations/create_activity_log_table.php.stub';
        }
        (new \CreateActivityLogTable)->up();

        // Ensure database schema satisfies Spatie v4 model expectations for tests
        if (! \Illuminate\Support\Facades\Schema::hasColumn('activity_log', 'batch_uuid')) {
            \Illuminate\Support\Facades\Schema::table('activity_log', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->uuid('batch_uuid')->nullable();
            });
        }

        \Illuminate\Database\Eloquent\Model::unguard();
    }

    public function getEnvironmentSetUp($app)
    {
        //
    }

    protected function defineEnvironment($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('app.key', 'base64:Hupx3yAySikrM2/edkZQNQHslgDWYfiBfCuSThJ5SK8=');
    }

    protected function getPackageProviders($app)
    {
        return [
            \BladeUI\Icons\BladeIconsServiceProvider::class,
            \BladeUI\Heroicons\BladeHeroiconsServiceProvider::class,
            \Filament\Actions\ActionsServiceProvider::class,
            \Filament\FilamentServiceProvider::class,
            \Filament\Forms\FormsServiceProvider::class,
            \Filament\Infolists\InfolistsServiceProvider::class,
            \Filament\Notifications\NotificationsServiceProvider::class,
            \Filament\Schemas\SchemasServiceProvider::class,
            \Filament\Support\SupportServiceProvider::class,
            \Filament\Tables\TablesServiceProvider::class,
            \Filament\Widgets\WidgetsServiceProvider::class,
            \Livewire\LivewireServiceProvider::class,
            \RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            ActivityLogServiceProvider::class,
            \AlizHarb\ActivityLog\Tests\Fixtures\TestPanelProvider::class,
        ];
    }
}
