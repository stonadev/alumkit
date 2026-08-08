<?php

declare(strict_types=1);

it('reads seeder admin values from ALUMKIT_* env vars', function () {
    putenv('ALUMKIT_ADMIN_NAME=Env Admin');
    putenv('ALUMKIT_ADMIN_EMAIL=admin@env.test');
    putenv('ALUMKIT_ADMIN_PASSWORD=env-secret');

    $config = require __DIR__.'/../../config/alumkit.php';

    expect($config['seeder']['admin_name'])->toBe('Env Admin');
    expect($config['seeder']['admin_email'])->toBe('admin@env.test');
    expect($config['seeder']['admin_password'])->toBe('env-secret');

    putenv('ALUMKIT_ADMIN_NAME');
    putenv('ALUMKIT_ADMIN_EMAIL');
    putenv('ALUMKIT_ADMIN_PASSWORD');
});
