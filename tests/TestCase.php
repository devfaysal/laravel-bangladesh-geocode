<?php

namespace Devfaysal\BangladeshGeocode\Tests;

use Devfaysal\BangladeshGeocode\BangladeshGeocodeServiceProvider;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use InvalidArgumentException;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected string $databaseDirectory;

    protected function getPackageProviders($app)
    {
        return [BangladeshGeocodeServiceProvider::class];
    }

    protected function defineEnvironment($app)
    {
        $this->databaseDirectory = sys_get_temp_dir().'/bangladesh-geocode-'.bin2hex(random_bytes(8));
        mkdir($this->databaseDirectory.'/migrations', 0777, true);
        $app->useDatabasePath($this->databaseDirectory);
        $app['config']->set('database.default', 'testing');
        $environment = getenv();
        $driver = $environment['TEST_DB_CONNECTION'] ?? 'sqlite';
        $connection = match ($driver) {
            'sqlite' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'mysql' => [
                'driver' => 'mysql',
                'host' => $environment['TEST_DB_HOST'] ?? '127.0.0.1',
                'port' => $environment['TEST_DB_PORT'] ?? '3306',
                'database' => $environment['TEST_DB_DATABASE'] ?? 'bangladesh_geocode_test',
                'username' => $environment['TEST_DB_USERNAME'] ?? 'root',
                'password' => $environment['TEST_DB_PASSWORD'] ?? '',
                'unix_socket' => $environment['TEST_DB_SOCKET'] ?? '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ],
            default => throw new InvalidArgumentException('Unsupported test database connection: '.$driver),
        };
        $app['config']->set('database.connections.testing', $connection);

        if ($driver === 'mysql') {
            // Match SQLite's empty database for every test, including command tests.
            $app['db']->connection('testing')->getSchemaBuilder()->dropAllTables();
            RefreshDatabaseState::$migrated = false;
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        (new Filesystem)->deleteDirectory($this->databaseDirectory);
    }
}
