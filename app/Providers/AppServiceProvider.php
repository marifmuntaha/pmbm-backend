<?php

namespace App\Providers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Master\Year;
use App\Models\Student\StudentVerification;
use App\Models\User;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use App\Observers\Master\YearObserver;
use App\Observers\Student\StudentVerificationObserver;
use App\Observers\UserObserver;
use App\Services\WhatsAppService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(WhatsAppService::class, function () {
            return new WhatsAppService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Invoice::observe(InvoiceObserver::class);
        User::observe(UserObserver::class);
        Year::observe(YearObserver::class);
        Payment::observe(PaymentObserver::class);
        StudentVerification::observe(StudentVerificationObserver::class);

        Builder::macro('whereLike', function ($attribute, $searchTerm) {
            return $this->where($attribute, 'LIKE', "%$searchTerm%");
        });
    }
}
