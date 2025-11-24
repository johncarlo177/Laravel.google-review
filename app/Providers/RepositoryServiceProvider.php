<?php

namespace App\Providers;

use App\Interfaces\BlogPostManager;
use App\Interfaces\CaptchaManager;
use App\Repositories\BlogPostManager as BlogPostManagerRepository;

use Illuminate\Support\ServiceProvider;

use App\Interfaces\QRCodeGenerator as QRCodeGeneratorInterface;
use App\Interfaces\DeviceInfo as DeviceInfoInterface;
use App\Interfaces\QRCodeStats as QRCodeStatsInterface;
use App\Interfaces\ModelIndex as ModelIndexInterface;

use App\Repositories\QRCodeGenerator;
use App\Repositories\DeviceInfo;
use App\Repositories\QRCodeStats;
use App\Repositories\ModelIndex;


use App\Repositories\EnvSaver as EnvSaverRepository;

use App\Interfaces\EnvSaver;

use App\Interfaces\SubscriptionManager;

use App\Repositories\SubscriptionManager as SubscriptionManagerRepository;

use App\Interfaces\FileManager;
use App\Interfaces\ModelSearchBuilder;
use App\Repositories\ModelSearchBuilder as ModelSearchBuilderRepository;
use App\Repositories\FileManager as FileManagerRepostiroy;

use App\Repositories\ContentBlockManager;

use App\Interfaces\ContentBlockManager as ContentBlockManagerInterface;
use App\Interfaces\MachineTranslation;
use App\Interfaces\TranslationManager as TranslationManagerInterface;
use App\Repositories\TranslationManager;

use App\Interfaces\UserManager;
use App\Repositories\UserManager as UserManagerRepository;
use App\Repositories\CaptchaManagerMobiCms;
use App\Repositories\GoogleTranslation;

use App\Repositories\TransactionManager;

use App\Interfaces\TransactionManager as TransactionManagerInterface;

use App\Repositories\CurrencyManager;

use App\Interfaces\CurrencyManager as CurrencyManagerInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            QRCodeGeneratorInterface::class,
            QRCodeGenerator::class
        );

        $this->app->bind(
            DeviceInfoInterface::class,
            DeviceInfo::class
        );

        $this->app->bind(
            QRCodeStatsInterface::class,
            QRCodeStats::class
        );

        $this->app->bind(
            ModelIndexInterface::class,
            ModelIndex::class
        );


        $this->app->bind(
            SubscriptionManager::class,
            SubscriptionManagerRepository::class
        );

        $this->app->bind(
            EnvSaver::class,
            EnvSaverRepository::class
        );

        $this->app->bind(
            FileManager::class,
            FileManagerRepostiroy::class
        );

        $this->app->bind(
            ModelSearchBuilder::class,
            ModelSearchBuilderRepository::class
        );

        $this->app->bind(
            BlogPostManager::class,
            BlogPostManagerRepository::class,
        );

        $this->app->bind(
            ContentBlockManagerInterface::class,
            ContentBlockManager::class,
        );

        $this->app->bind(
            CaptchaManager::class,
            CaptchaManagerMobiCms::class
        );

        $this->app->bind(
            UserManager::class,
            UserManagerRepository::class
        );

        $this->app->bind(
            TranslationManagerInterface::class,
            TranslationManager::class,
        );

        $this->app->bind(
            TransactionManagerInterface::class,
            TransactionManager::class
        );

        $this->app->bind(
            MachineTranslation::class,
            GoogleTranslation::class
        );

        $this->app->bind(
            CurrencyManagerInterface::class,
            CurrencyManager::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
