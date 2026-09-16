<?php

namespace Workbench\App\Models;

use Alumkit\Alumkit\Models\User as BaseUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Workbench\Database\Factories\UserFactory;

/** @use HasFactory<UserFactory> */
class User extends BaseUser
{
    use HasFactory;
}
