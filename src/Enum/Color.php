<?php
declare(strict_types=1);

namespace BsUtils\Enum;

use BsUtils\Utility\ColorInterface;

enum Color: string implements ColorInterface
{
    case Primary = 'primary';
    case Secondary = 'secondary';
    case Success = 'success';
    case Danger = 'danger';
    case Warning = 'warning';
    case Info = 'info';
    case Light = 'light';
    case Dark = 'dark';

    public function value(): string
    {
        return $this->value;
    }
}