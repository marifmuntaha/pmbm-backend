<?php

namespace App\Observers\Master;

use App\Models\Master\Year;

class YearObserver
{
    /**
     * Handle the Year "created" event.
     */
    public function created(Year $year): void
    {
        if ($year->active == '1') {
            $years = Year::get()->collect();
            $years->map(function (Year $item) use ($year) {
                if ($item->id !== $year->id) {
                    $item->active = '2';
                    $item->save();
                }
            });
        }
    }

    /**
     * Handle the Year "updated" event.
     */
    public function updated(Year $year): void
    {
        if ($year->active == '1') {
            $years = Year::get()->collect();
            $years->map(function (Year $item) use ($year) {
                if ($item->id !== $year->id) {
                    $item->active = '2';
                    $item->save();
                }
            });
        }
    }

    /**
     * Handle the Year "deleted" event.
     */
    public function deleted(Year $year): void
    {
        //
    }

    /**
     * Handle the Year "restored" event.
     */
    public function restored(Year $year): void
    {
        //
    }

    /**
     * Handle the Year "force deleted" event.
     */
    public function forceDeleted(Year $year): void
    {
        //
    }
}
