<?php

namespace App\Providers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter as FlysystemLocalAdapter;
use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use League\Flysystem\Visibility;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;

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
        $this->registerLocalFilesystemWithoutFinfo();
    }

    /**
     * Local disk replacement that does not require the PHP fileinfo extension.
     *
     * Flysystem's default local adapter instantiates a finfo-based MIME detector
     * in its constructor, which makes every local disk operation fail when the
     * extension is unavailable. Detecting MIME from the file extension avoids
     * that dependency while keeping uploads working.
     */
    private function registerLocalFilesystemWithoutFinfo(): void
    {
        Storage::extend('local_no_finfo', function ($app, array $config): FilesystemAdapter {
            $visibility = PortableVisibilityConverter::fromArray(
                $config['permissions'] ?? [],
                $config['directory_visibility'] ?? $config['visibility'] ?? Visibility::PRIVATE,
            );

            $adapter = new FlysystemLocalAdapter(
                $config['root'],
                $visibility,
                $config['lock'] ?? LOCK_EX,
                ($config['links'] ?? null) === 'skip'
                    ? FlysystemLocalAdapter::SKIP_LINKS
                    : FlysystemLocalAdapter::DISALLOW_LINKS,
                new ExtensionMimeTypeDetector,
            );

            return new FilesystemAdapter(new Filesystem($adapter, $config), $adapter, $config);
        });
    }
}
