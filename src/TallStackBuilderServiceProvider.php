<?php

namespace GoodM4ven\TallStackBuilder;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TallStackBuilderServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         *
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         *
         */
        $package
            ->name('tall-stack-builder')
            ->hasCommand(BuildCommand::class);
    }
}
