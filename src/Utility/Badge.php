<?php
declare(strict_types=1);

namespace BsUtils\Utility;

class Badge implements BadgeInterface
{
    /**
     * @param string $label
     * @param ColorInterface|string $color
     */
    public function __construct(
        private string $label,
        private ColorInterface|string $color,
    ) {
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return ColorInterface|string
     */
    public function color(): ColorInterface|string
    {
        return $this->color;
    }
}