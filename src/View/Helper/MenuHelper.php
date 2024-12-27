<?php

declare(strict_types=1);

namespace BsUtils\View\Helper;

use Cake\Core\InstanceConfigTrait;
use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Menu helper
 */
class MenuHelper extends Helper
{
    use StringTemplateTrait;
    use InstanceConfigTrait;

    const ITEM_TYPE_LINK = 'link';
    const ITEM_TYPE_DIVIDER = 'divider';
    const ITEM_TYPE_DISABLED = 'disabled';
    const ITEM_TYPE_TITLE = 'title';

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'nestClass' => 'dropdown',
        'activeClass' => 'active',
        'templates' => [
            'menu' => '<ul class="navbar-nav {{class}}">{{items}}</ul>',
            'menuItem' => '<li class="nav-item {{class}}">{{text}}{{nest}}</li>',
            'menuLink' => '<a href="{{url}}" class="nav-link {{class}}{{activeClass}}">{{icon}}{{text}}</a>',
            'nestMenuLink' => '<a href="{{url}}" class="nav-link dropdown-toggle {{class}}{{activeClass}}" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{icon}}{{text}}</a>',
            'nest' => '<ul class="dropdown-menu">{{items}}</ul>',
            'nestItem' => '<li>{{text}}{{nest}}</li>',
            'nestLink' => '<a href="{{url}}" class="dropdown-item ">{{icon}}{{text}}</a>',
            'icon' => '<i class="{{icon}}"></i>',
            'divider' => '<li><hr class="dropdown-divider"></li>',
            'disabledItem' => '<li class="nav-item"><a class="nav-link disabled" aria-disabled="true">{{icon}}{{text}}</a></li>',
        ],
    ];

    protected array $helpers = ['Url'];

    protected array $activeKeys = [];

    /**
     * @param array $menu
     * @param array $options
     * @return string
     */
    public function render(array $menu, array $options = []): string
    {
        $this->activeItem($options['activeItem'] ?? '');
        return $this->formatTemplate('menu', [
            'class' => $options['menu.class'] ?? $this->getConfig('menu.class'),
            'items' => $this->buildMenuItems($menu, $options),
        ]);
    }

    /**
     * @param string $keys
     * @return void
     */
    public function activeItem(string $keys): void
    {
        $this->activeKeys = explode('.', $keys);
    }

    /**
     * @param array $items
     * @param array $options
     * @param int $level
     * @param bool $isChild
     * @return string
     */
    protected function buildMenuItems(array $items, array $options = [], int $level = 0): string
    {
        $result = '';
        $isChild = $level > 0;
        $currentActiveKey = $this->activeKeys[$level] ?? null;
        foreach ($items as $key => $item) {
            if (isset($item['hidden']) && $item['hidden'] === true) {
                continue;
            }

            if (isset($item['hidden']) && is_callable($item['hidden']) && $item['hidden']()) {
                continue;
            }

            if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_TITLE && !($isChild)) {
                $result .= $this->formatTemplate('menuItem', [
                    'text' => $item['title'],
                ]);
                continue;
            }

            if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_DIVIDER) {
                $result .= $this->formatTemplate('divider', []);
                continue;
            }

            if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_DISABLED) {
                $result .= $this->formatTemplate('disabledItem', [
                    'text' => $item['title'],
                ]);
                continue;
            }

            $template = $isChild ? 'nestLink' : 'menuLink';
            $template = $item['children'] ?? null ? 'nestMenuLink' : $template;
            $link = $this->formatTemplate($template, [
                'url' => $this->Url->build($item['url'] ?? '#'),
                'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : '',
                'text' => $item['title'],
            ]);

            $nest = '';
            if (!empty($item['children'])) {
                $nest = $this->formatTemplate('nest', [
                    'items' => $this->buildMenuItems($item['children'], $options, $level + 1),
                ]);
            }

            $containerTemplate = $isChild ? 'nestItem' : 'menuItem';
            $result .= $this->formatTemplate($containerTemplate, [
                'class' => $this->cssClass([
                    $item['class'] ?? null,
                    !empty($item['children']) ? $this->getConfig('nestClass') : null,
                    ($currentActiveKey == (string) $key) ? $this->getConfig('activeClass') : null,
                ]),
                'text' => $link,
                'nest' => $nest,
            ]);
        }

        return $result;
    }

    /**
     * @param string|array $class
     * @return string
     */
    protected function cssClass(string|array $class): string
    {
        if (is_array($class)) {
            $class = implode(' ', $class);
        }

        return trim($class);
    }
}
