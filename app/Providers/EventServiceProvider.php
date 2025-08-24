<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Models\{BarangUnitPrice, KasirShift, Transaksi, TransaksiItem};
use App\Observers\{BarangUnitPriceObserver, KasirShiftObserver, TransaksiObserver, TransaksiItemObserver};

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
         BarangUnitPrice::observe(BarangUnitPriceObserver::class);
        KasirShift::observe(KasirShiftObserver::class);
        Transaksi::observe(TransaksiObserver::class);
        TransaksiItem::observe(TransaksiItemObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
