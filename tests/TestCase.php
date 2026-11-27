<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Los tests nunca escriben copias ni logs de borrado en storage/ real.
        $dir = sys_get_temp_dir() . '/webnu-tests-' . getmypid() . '-' . uniqid();
        config([
            'platform.deleted_users_path' => $dir . '/deleted-users',
            'logging.channels.account_deletions.path' => $dir . '/account-deletions.log',
        ]);
    }
}
