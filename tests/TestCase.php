<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected static bool $viteHotFileWasPresent = false;
    protected static bool $viteHotFileCreated = false;
    protected static bool $viteHotFileShutdownRegistered = false;

    protected function setUp(): void
    {
        parent::setUp();

        if (self::$viteHotFileShutdownRegistered) {
            return;
        }

        $hotFile = public_path('hot');

        self::$viteHotFileWasPresent = file_exists($hotFile);

        if (! self::$viteHotFileWasPresent) {
            // Any URL is fine for tests; we just need to avoid loading the build manifest.
            file_put_contents($hotFile, 'http://localhost:5173');
            self::$viteHotFileCreated = true;
        }

        if (self::$viteHotFileCreated) {
            register_shutdown_function(function () use ($hotFile) {
                if (file_exists($hotFile)) {
                    @unlink($hotFile);
                }
            });
        }

        self::$viteHotFileShutdownRegistered = true;
    }
}
