# Filament Activity Log - Configuration Guide

This guide explains how to properly configure the Filament Activity Log package, specifically addressing common configuration scenarios.

## Table of Contents

1. [Navigation Group Configuration](#navigation-group-configuration)
2. [Permissions & Authorization](#permissions--authorization)
3. [Complete Examples](#complete-examples)

---

## Navigation Group Configuration

### Issue

When setting `navigationGroup()` on the plugin in your `AdminPanelProvider`, it doesn't show under the custom menu group.

### Solution

The plugin now properly supports navigation group configuration. You can set it in two ways:

#### Method 1: Via Plugin Registration (Recommended)

In your `AdminPanelProvider`:

```php
use AlizHarb\ActivityLog\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ActivityLogPlugin::make()
                ->label('Log')
                ->pluralLabel('Logs')
                ->navigationGroup('MyCustomMenuGroup')
                ->navigationIcon('heroicon-o-clipboard-document-list')
                ->navigationSort(10),
        ]);
}
```

#### Method 2: Via Configuration File

Publish the config file:

```bash
php artisan vendor:publish --tag="filament-activity-log-config"
```

Then update `config/filament-activity-log.php`:

```php
'resource' => [
    'group' => 'MyCustomMenuGroup',
    'sort' => 10,
    'navigation_icon' => 'heroicon-o-clipboard-document-list',
    // ...
],
```

**Note:** Plugin settings take precedence over config file settings.

---

## Permissions & Authorization

### Understanding Permission Modes

The package supports three authorization modes:

1. **Open Access (Default)** - Everyone can view activity logs
2. **Permission-Based** - Uses Laravel's permission system
3. **Custom Authorization** - Your own logic

### Mode 1: Open Access (Default)

By default, all authenticated users can view activity logs:

```php
'permissions' => [
    'enabled' => false,
],
```

### Mode 2: Permission-Based Authorization

Enable permission checking and assign permissions to users/roles:

```php
'permissions' => [
    'enabled' => true,
    'view_any' => 'view_any_activity',
    'view' => 'view_activity',
    'delete' => 'delete_activity',
    // ...
],
```

Then assign permissions using your permission package (e.g., Spatie Laravel Permission):

```php
// Give permission to a role
$role->givePermissionTo('view_any_activity');

// Give permission to a user
$user->givePermissionTo('view_any_activity');
```

### Mode 3: Custom Authorization (NEW)

**This is the solution for restricting access to specific users!**

Use the `custom_authorization` callback to define your own logic:

#### Example 1: Restrict to User ID 1 Only

```php
'permissions' => [
    'enabled' => false, // Not used when custom_authorization is set
    'custom_authorization' => fn($user) => $user->id === 1,
],
```

#### Example 2: Restrict to Super Admins Only

```php
'permissions' => [
    'custom_authorization' => fn($user) => $user->hasRole('super_admin'),
],
```

#### Example 3: Multiple Conditions

```php
'permissions' => [
    'custom_authorization' => function ($user) {
        return $user->id === 1 ||
               $user->hasRole('super_admin') ||
               $user->email === 'admin@example.com';
    },
],
```

#### Example 4: Using Filament's Panel Context

```php
'permissions' => [
    'custom_authorization' => function ($user) {
        // Check if user is a super admin in Filament
        return $user->hasRole(filament()->auth()->getDefaultRole());
    },
],
```

#### Example 5: Class-Based Authorization (Recommended for Config Caching)

If you cache your configuration (`php artisan config:cache`), you cannot use Closures. Instead, use an invokable class:

**config/filament-activity-log.php:**

```php
'permissions' => [
    'custom_authorization' => \App\Security\ActivityLogAuthorization::class,
],
```

**app/Security/ActivityLogAuthorization.php:**

```php
namespace App\Security;

use App\Models\User;

class ActivityLogAuthorization
{
    public function __invoke(User $user): bool
    {
        return $user->id === 1;
    }
}
```

**Important Notes:**

- `custom_authorization` takes **precedence** over the `enabled` setting
- If `custom_authorization` returns `false`, the user won't see the Activity Log resource in the navigation
- The callback receives the authenticated user instance
- You can use any logic: check user properties, roles, permissions, or even database queries

---

## Complete Examples

### Example 1: Basic Setup with Custom Group

**AdminPanelProvider.php:**

```php
use AlizHarb\ActivityLog\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ActivityLogPlugin::make()
                ->label('Activity Log')
                ->pluralLabel('Activity Logs')
                ->navigationGroup('System')
                ->navigationIcon('heroicon-o-clipboard-document-list')
                ->navigationSort(100),
        ]);
}
```

**Result:** Activity logs appear under "System" menu group.

---

### Example 2: Restrict to User ID 1 Only

**config/filament-activity-log.php:**

```php
return [
    'permissions' => [
        'enabled' => false,
        'custom_authorization' => fn($user) => $user->id === 1,
    ],
    // ... rest of config
];
```

**Result:** Only the user with ID 1 can see and access activity logs.

---

### Example 3: Restrict to Super Admins with Custom Group

**AdminPanelProvider.php:**

```php
use AlizHarb\ActivityLog\ActivityLogPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            ActivityLogPlugin::make()
                ->label('System Log')
                ->pluralLabel('System Logs')
                ->navigationGroup('Administration')
                ->navigationSort(999),
        ]);
}
```

**config/filament-activity-log.php:**

```php
return [
    'permissions' => [
        'custom_authorization' => fn($user) => $user->hasRole('super_admin'),
    ],
    // ... rest of config
];
```

**Result:** Only super admins see "System Logs" under the "Administration" menu group.

---

### Example 4: Using Filament Shield Integration

If you're using [Filament Shield](https://github.com/bezhanSalleh/filament-shield):

**config/filament-activity-log.php:**

```php
return [
    'permissions' => [
        'enabled' => true,
        'view_any' => 'view_any_activity',
        'view' => 'view_activity',
        'delete' => 'delete_activity',
    ],
];
```

Then generate permissions:

```bash
php artisan shield:generate --resource=ActivityLogResource
```

And assign them through Filament Shield's UI.

---

## Troubleshooting

### Issue: Navigation group still not working

**Check:**

1. Clear your config cache: `php artisan config:clear`
2. Clear your view cache: `php artisan view:clear`
3. Ensure you're calling the plugin methods correctly
4. Verify the plugin is registered in your panel

### Issue: Activity logs hidden even for super_admin

**Check:**

1. If `permissions.enabled` is `true`, ensure the user has the required permissions
2. Use `custom_authorization` instead for role-based checks
3. Clear config cache after changes

### Issue: Custom authorization not working

**Check:**

1. Ensure the callback returns a boolean (`true` or `false`)
2. Clear config cache: `php artisan config:clear`
3. Verify the user object has the properties/methods you're checking
4. Add logging to debug:
   ```php
   'custom_authorization' => function ($user) {
       \Log::info('Activity Log Auth Check', ['user_id' => $user->id]);
       return $user->id === 1;
   },
   ```

### Issue: Configuration serialization error

**Error:** `Your configuration files could not be serialized because the value at "filament-activity-log.permissions.custom_authorization" is non-serializable`

**Solution:**

You are likely running `php artisan config:cache` while using a Closure in your config file. Closures cannot be serialized.

To fix this, switch to **Class-Based Authorization** (see Example 5 above) or remove the config cache if not needed (`php artisan config:clear`).

---

## Best Practices

1. **Use Plugin Configuration for UI Settings**: Set labels, icons, and navigation through the plugin in your `AdminPanelProvider`
2. **Use Config File for Authorization**: Set permissions and authorization logic in the config file
3. **Clear Caches**: Always clear config cache after making changes to the config file
4. **Test Authorization**: Test with different user roles to ensure your authorization logic works correctly
5. **Document Your Setup**: Add comments explaining your authorization logic for future reference

---

## Additional Resources

- [Filament Documentation](https://filamentphp.com/docs)
- [Spatie Activity Log Documentation](https://spatie.be/docs/laravel-activitylog)
- [Package Repository](https://github.com/alizharb/filament-activity-log)
