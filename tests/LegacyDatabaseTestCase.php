<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

abstract class LegacyDatabaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        DB::connection('sqlite')->unprepared(file_get_contents(__DIR__.'/Fixtures/legacy-schema.sql'));
    }
}
