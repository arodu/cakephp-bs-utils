<?php
declare(strict_types=1);

namespace BsUtils\Utility;

interface BadgeInterface
{
    public function text(): string;
    public function color(): string;
}
