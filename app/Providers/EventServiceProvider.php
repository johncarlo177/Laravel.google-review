<?php

namespace App\Providers;

use App\Listeners\DynamicListener;
use App\Support\System\Traits\ClassListLoader;
use App\Support\System\Traits\WriteLogs;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    use WriteLogs;

    use ClassListLoader;

    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerDynamicEventListeners();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return true;
    }

    private function registerDynamicEventListeners()
    {
        collect(
            $this->makeInstances(
                base_path('app/Listeners')
            )
        )->filter(function ($listener) {

            return $listener instanceof DynamicListener &&
                $this->guessDynamicEventClass($listener);
            //
        })->each(function (DynamicListener $listener) {

            $eventClass = $this->guessDynamicEventClass($listener);

            $class = $listener::class;

            $class = "$class";

            $this->registerDynamicListener($eventClass, $class);
            //
        });
    }

    private function guessDynamicEventClass($listener)
    {
        $name = class_basename($listener::class);

        if (!preg_match('/On/', $name)) {
            return null;
        }

        $eventName = preg_replace('/On/', '', $name);

        $class = "App\\Events\\$eventName";

        if (!class_exists($class)) return null;

        return $class;
    }

    private function registerDynamicListener($eventClass, $listenerClass)
    {
        if (app()->environment('local') && app()->environment('demo'))
            $this->logDebugf(
                'Registering dynamic class listener %s ==> %s',
                $eventClass,
                $listenerClass
            );

        if (!class_exists($eventClass)) {
            $this->logDebugf('Class %s not found', $eventClass);
        }

        if (!class_exists($listenerClass)) {
            $this->logDebugf('Class %s not found', $listenerClass);
        }

        Event::listen($eventClass, [$listenerClass, 'handle']);
    }

    protected function configureEmailVerification() {}
}
