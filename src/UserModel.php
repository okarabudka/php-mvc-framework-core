<?php

declare(strict_types=1);

namespace Okarabudka\PhpMvcCore;

use Okarabudka\PhpMvcCore\Db\DbModel;

abstract class UserModel extends DbModel
{
    abstract public function getDisplayName(): string;
}
