<?php

namespace AlizHarb\ActivityLog\Tests;

use AlizHarb\ActivityLog\ActivityLogServiceProvider;
use AlizHarb\ActivityLog\Tests\Fixtures\TestPanelProvider;
use AlizHarb\ActivityLog\Tests\Fixtures\User;
use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Filament\Actions\ActionsServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Infolists\InfolistsServiceProvider;
use Filament\Notifications\NotificationsServiceProvider;
use Filament\Schemas\SchemasServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Filament\Widgets\WidgetsServiceProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use RyanChandler\BladeCaptureDirective\BladeCaptureDirectiveServiceProvider;

class TestCase extends Orchestra
{
    public User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        if (! class_exists(\CreateActivityLogTable::class)) {
            include __DIR__.'/../vendor/spatie/laravel-activitylog/database/migrations/create_activity_log_table.php.stub';
        }
        (new \CreateActivityLogTable)->up();

        // Ensure database schema satisfies the installed Spatie major version
        if (static::isSpatieV4()) {
            // v4 lane: ensure batch_uuid and event columns exist
            if (! Schema::hasColumn('activity_log', 'batch_uuid')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->uuid('batch_uuid')->nullable();
                });
            }
        } else {
            // v5 lane: ensure attribute_changes column exists
            if (! Schema::hasColumn('activity_log', 'attribute_changes')) {
                Schema::table('activity_log', function (Blueprint $table) {
                    $table->json('attribute_changes')->nullable();
                });
            }
        }

        if (! Schema::hasColumn('activity_log', 'event')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->string('event')->nullable();
            });
        }

        Model::unguard();
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
        $app['config']->set('auth.providers.users.model', User::class);
    }

    protected function getPackageProviders($app)
    {
        return [
            BladeIconsServiceProvider::class,
            BladeHeroiconsServiceProvider::class,
            ActionsServiceProvider::class,
            FilamentServiceProvider::class,
            FormsServiceProvider::class,
            InfolistsServiceProvider::class,
            NotificationsServiceProvider::class,
            SchemasServiceProvider::class,
            SupportServiceProvider::class,
            TablesServiceProvider::class,
            WidgetsServiceProvider::class,
            LivewireServiceProvider::class,
            BladeCaptureDirectiveServiceProvider::class,
            \Spatie\Activitylog\ActivitylogServiceProvider::class,
            ActivityLogServiceProvider::class,
            TestPanelProvider::class,
        ];
    }

    /**
     * Detect whether we are running against spatie/laravel-activitylog v4.
     */
    public static function isSpatieV4(): bool
    {
        // v4 has the LogBatch class; v5 removed it
        return class_exists(\Spatie\Activitylog\LogBatch::class);
    }

    /**
     * Detect whether we are running against spatie/laravel-activitylog v5.
     */
    public static function isSpatieV5(): bool
    {
        return ! static::isSpatieV4();
    }
}
