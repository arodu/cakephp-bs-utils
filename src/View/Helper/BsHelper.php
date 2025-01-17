<?php

declare(strict_types=1);

namespace BsUtils\View\Helper;

use BsUtils\Utility\BadgeInterface;
use BsUtils\Utility\ColorInterface;
use Cake\Utility\Hash;
use Cake\View\Helper;
use Cake\View\View;

/**
 * Bs helper
 */
class BsHelper extends Helper
{
    const CLASS_BG = 'bg';
    const CLASS_BTN = 'btn';
    const CLASS_TEXT = 'text';
    const CLASS_CARD = 'card';
    const CLASS_BORDER = 'border';

    const BADGE_DEFAULT = 'badge';
    const BADGE_PILL = 'badge-pill';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [];

    protected array $helpers = ['Html', 'BsUtils.Menu'];

    /**
     * ### Options
     * - `class`: Additional classes to add to the badge.
     * - `tag`: The HTML tag to use for the badge. Default `span`.
     *
     * @param \Cake\View\View $View The View this helper is being attached to.
     * @param array $config Configuration settings for the helper.
     */
    public function badge(BadgeInterface $badge, array $options = [])
    {
        $options = Hash::merge([
            'class' => 'text-bg-' . $badge->color(),
        ], $options);

        return $this->Html->badge($badge->text(), $options);
    }

    /**
     * ### Options
     * - `class`: Additional classes to add to the badge.
     *
     * @param \BsUtils\Utility\BadgeInterface $badge
     * @param array $options
     * @return string
     */
    public function badgePill(BadgeInterface $badge, array $options = [])
    {
        return $this->badge($badge, Hash::merge($options, ['class' => 'badge-pill']));
    }

    /**
     * ### Options
     * - `class`: Additional classes to add to the badge.
     *
     * @param \BsUtils\Utility\BadgeInterface $badge
     * @param array $options
     * @return string
     */
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

    /**
     * Undocumented function
     *
     * ### Options
     * `tag`: The HTML tag to use for the badge. Default `span`.
     * `class`: Additional classes to add to the badge.
     * 'striped': Add striped class to the progress bar.
     * 'animated': Add animated class to the progress bar.
     * 
     * @param integer $value
     * @param integer $max
     * @param ColorInterface|string|null $color
     * @param array $options
     * @return void
     */
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

        $progressbar = $this->Html->tag('div', null, [
            'class' => implode(' ', $classes),
            'role' => 'progressbar',
            'style' => 'width: ' . ($value / $max * 100) . '%',
            'aria-valuenow' => $value,
            'aria-valuemin' => 0,
            'aria-valuemax' => $max,
        ]);

        return $this->Html->tag('div', $progressbar, ['class' => 'progress']);
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
