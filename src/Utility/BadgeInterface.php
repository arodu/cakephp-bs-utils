<?php
declare(strict_types=1);

namespace BsUtils\Utility;

interface BadgeInterface
{
    public function label(): string;
    public function color(): string;
}