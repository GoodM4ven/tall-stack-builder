<?php

namespace GoodM4ven\TallStackBuilder;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class BuildCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tall-stack:build';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build the TALL Stack dev-container!';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->newLine();
        $this->comment('Installing NPM packages...');
        $this->runSilently(['npm', 'install']);

        $this->comment('Generating an application key...');
        $this->callSilently('key:generate');

        $this->comment('Linking local storage...');
        $this->callSilently('storage:link');

        $this->comment('Migrating the database...');
        $this->callSilently('migrate', ['--seed' => true]);

        $this->comment('Converting the template\'s structure to a Laravel application one...');

        // ? Delete preset package files
        $fileSystem = new Filesystem;
        $fileSystem->deleteDirectory(base_path('.github'));
        $fileSystem->delete(base_path('LICENSE'));

        // ? ==============================
        // ? Resetting Package Information
        // ? ============================

        $composerFile = base_path('composer.json');

        $this->replaceInFile('"name": "goodm4ven/tall-stack"', '"name": "laravel/laravel"', $composerFile);

        $this->replaceInFile(
            '"description": "This is a TALL stack (Laravel) development environment (Docker) that I\'d use to instantly start a new web application.",',
            '"description": "A TALL Stack Laravel application.",',
            $composerFile
        );

        $this->replaceInFile('"tailwindcss",', "", $composerFile);
        $this->replaceInFile('"alpinejs",', "", $composerFile);
        $this->replaceInFile('"livewire",', '"framework",', $composerFile);
        $this->replaceInFile('"devcontainer",', "", $composerFile);
        $this->replaceInFile('"preset",', "", $composerFile);
        $this->replaceInFile('"mysql",', "", $composerFile);
        $this->replaceInFile('"redis",', "", $composerFile);
        $this->replaceInFile('"mailhog",', "", $composerFile);
        $this->replaceInFile('"tall-stack",', '"tall-stack"', $composerFile);
        $this->replaceInFile('"laravel-sail",', '', $composerFile);
        $this->replaceInFile('"minio",', '', $composerFile);
        $this->replaceInFile('"vitejs"', '', $composerFile);
        $this->removeFileEmptyLines($composerFile);

        copy(__DIR__ . '/../stubs/README.md', base_path('README.md'));

        // ? Set the tall-stack-builder package itself for deletion
        $this->replaceInFile('"goodm4ven/tall-stack-builder": "^1.0.0",', "", $composerFile);
        $this->removeFileEmptyLines($composerFile);

        $this->newLine();
        $this->info('Installation succeeded. But rebuilding the environment is needed! 😁👍🏻');
        $this->newLine();

        return static::SUCCESS;
    }

    protected function runSilently(array $commandKeywords)
    {
        (new Process($commandKeywords, base_path()))
            ->setTimeout(null)
            ->run();
    }

    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    protected function removeFileEmptyLines(string $filePath)
    {
        file_put_contents(
            $filePath,
            preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", file_get_contents($filePath))
        );
    }
}
