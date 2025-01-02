<?php

declare(strict_types=1);

namespace BsUtils\Utility;

class Badge implements BadgeInterface
{
    private string $label;
    private ColorInterface|string $color;

    /**
     * @param string $label
     * @param ColorInterface|string $color
     */
    public function __construct(string $label, ColorInterface|string $color = 'primary')
    {
        $this->label = $label;
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function label(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function color(): string
    {
        if ($this->color instanceof ColorInterface) {
            return $this->color->value;
        }

        return $this->color;
    }
}
