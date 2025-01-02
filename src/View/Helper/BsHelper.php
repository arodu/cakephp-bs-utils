<?php

declare(strict_types=1);

namespace BsUtils\View\Helper;

use BsUtils\Utility\BadgeInterface;
use BsUtils\Utility\ColorInterface;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Bs helper
 */
class BsHelper extends Helper
{
    const TYPE_BG = 'bg';
    const TYPE_BTN = 'btn';
    const TYPE_TEXT = 'text';
    const TYPE_CARD = 'card';
    const TYPE_BORDER = 'border';

    const BADGE_DEFAULT = 'badge';
    const BADGE_PILL = 'badge-pill';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [];

    protected array $helpers = ['Html', 'BsUtils.Menu'];

    public function badge(BadgeInterface $badge, string $type = self::BADGE_DEFAULT)
    {
        return $this->Html->tag(
            'span',
            $badge->text(),
            [
                'class' => implode(' ', [
                    'badge',
                    'text-bg-' . $badge->color(),
                    $type === self::BADGE_PILL ? 'rounded-pill' : '',
                ]),
            ]
        );
    }

    public function badgePill(BadgeInterface $badge)
    {
        return $this->badge($badge, self::BADGE_PILL);
    }

    public function alert(string $message, ColorInterface $color, array $options = [])
    {
        if (isset($options['dismissible']) && $options['dismissible']) {
            $message .= $this->Html->tag(
                'button',
                null,
                [
                    'type' => 'button',
                    'class' => 'btn-close',
                    'data-bs-dismiss' => 'alert',
                    'aria-label' => __('Close'),
                ]
            );
        }

        return $this->Html->tag(
            'div',
            $message,
            [
                'class' => 'alert alert-' . $color,
                'role' => 'alert',
            ]
        );
    }

    public function progress(int $value, int $max = 100, ColorInterface|string $color = null, array $options = [])
    {
        $options += [
            'striped' => false,
            'animated' => false,
        ];

        $classes = ['progress-bar'];
        if ($options['striped']) {
            $classes[] = 'progress-bar-striped';
        }
        if ($options['animated']) {
            $classes[] = 'progress-bar-animated';
        }

        return $this->Html->tag(
            'div',
            $this->Html->tag(
                'div',
                null,
                [
                    'class' => implode(' ', $classes),
                    'role' => 'progressbar',
                    'style' => 'width: ' . ($value / $max * 100) . '%',
                    'aria-valuenow' => $value,
                    'aria-valuemin' => 0,
                    'aria-valuemax' => $max,
                ]
            ),
            [
                'class' => 'progress',
            ]
        );
    }

    public function spinner(ColorInterface|string $color = null, array $options = [])
    {
        $options += [
            'size' => null,
            'border' => null,
        ];

        $classes = ['spinner-border'];
        if ($options['size']) {
            $classes[] = 'spinner-border-' . $options['size'];
        }
        if ($options['border']) {
            $classes[] = 'border-' . $options['border'];
        }

        return $this->Html->tag(
            'div',
            null,
            [
                'class' => implode(' ', $classes),
                'role' => 'status',
            ]
        );
    }

    public function dropdown(array $items, array $options = [])
    {
        $options += [
            'templates' => [
                'menu' => '<ul class="dropdown-menu">{items}</ul>',
                'item' => '<li class="dropdown-item">{link}</li>',
                'itemWrapper' => '',
                'nest' => '<ul class="dropdown-menu">{items}</ul>',
            ],
        ];

        return $this->Menu->render($items, $options);
    }
}
