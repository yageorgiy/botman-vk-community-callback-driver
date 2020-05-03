<?php

namespace BotMan\Drivers\VK\Providers;

use BotMan\Drivers\VK\VkCommunityCallbackDriver;
use Illuminate\Support\ServiceProvider;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\VK;
use BotMan\Studio\Providers\StudioServiceProvider;

class VKServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        if (! $this->isRunningInBotManStudio()) {
            $this->loadDrivers();

            $this->publishes([
                __DIR__.'/../../stubs/vk.php' => config_path('botman/vk.php'),
            ]);

            $this->mergeConfigFrom(__DIR__.'/../../stubs/vk.php', 'botman.vk');
        }
    }

    /**
     * Load BotMan drivers.
     */
    protected function loadDrivers()
    {
        DriverManager::loadDriver(VkCommunityCallbackDriver::class);
    }

    /**
     * @return bool
     */
    protected function isRunningInBotManStudio()
    {
        return class_exists(StudioServiceProvider::class);
    }
}