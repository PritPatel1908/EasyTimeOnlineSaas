<?php

namespace App\Providers;

use App\Exceptions\DeleteBlockedException;
use App\Helpers\DatabaseReferenceChecker;
use App\Support\TenantPermissions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
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
        TenantPermissions::registerGateHook();

        Event::listen('eloquent.deleting: *', static function (mixed ...$payload): void {
            $models = $payload[1] ?? [];
            $model = $models[0] ?? null;

            if (! $model instanceof Model || $model->getKey() === null) {
                return;
            }

            $reference = method_exists($model, 'findRelatedRecord')
                ? $model->findRelatedRecord()
                : DatabaseReferenceChecker::findReferences(
                    referencedTable: $model->getTable(),
                    referencedId: $model->getKey(),
                    connectionName: $model->getConnectionName(),
                );

            if ($reference !== null) {
                throw DeleteBlockedException::forModel($model);
            }
        });

        View::composer(['layouts.partials.header', 'partials.topbar'], function (\Illuminate\View\View $view): void {
            $user = Auth::guard('tenant')->user() ?? Auth::user();
            $notifications = $user && method_exists($user, 'unreadNotifications')
                ? $user->unreadNotifications()->limit(5)->get()
                : collect();

            $view->with('adminNotifications', $notifications);
        });
    }
}
