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
        'menu.class' => 'nav',
        'dropdown.class' => 'dropdown',
        'active.class' => 'active',

        /**
         * Default icon for menu items.
         * 'defaultIcon' => [
         *      0 => 'bi bi-circle-fill',
         *      1 => 'bi bi-circle',
         *      2 => 'bi bi-record-circle-fill',
         *      'default' => 'bi bi-circle',
         * ],
         */
        'defaultIcon' => null,

        /**
         * Class for nested items.
         */
        'templates' => [
            /**
             * Default templates for menu items.
             */
            'menuContainer' => '<ul class="{{class}}">{{items}}</ul>',
            'menuItem' => '<li class="nav-item {{class}}">{{text}}{{nest}}</li>',
            'menuItemDisabled' => '<li class="nav-item"><a class="nav-link disabled" aria-disabled="true">{{icon}}{{text}}</a></li>',
            'menuItemLink' => '<a class="nav-link {{class}}{{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',
            'menuItemLinkNest' => '<a class="nav-link dropdown-toggle {{class}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{icon}}{{text}}</a>',

            /**
             * Default templates for dropdown items.
             */
            'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
            'dropdownItem' => '<li>{{text}}{{nest}}</li>',
            'dropdownItemDisabled' => '<li>{{text}}{{nest}}</li>',
            'dropdownItemLink' => '<a class="dropdown-item {{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',
            'dropdownItemLinkNest' => '<a class="dropdown-item {{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',

            /**
             * Default templates for other items.
             */
            'icon' => '<i class="{{icon}}"></i>',
            'divider' => '<li><hr class="dropdown-divider"></li>',
            'menuTitle' => '<li class="nav-header">{{icon}}{{text}}</li>',
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
        return $this->formatTemplate('menuContainer', [
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
                $result .= $this->formatTemplate('menuTitle', [
                    'text' => $item['label'],
                ]);
                continue;
            }

            if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_DIVIDER) {
                $result .= $this->formatTemplate('divider', []);
                continue;
            }

            if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_DISABLED) {
                $disabledItem = $isChild ? 'dropdownItemDisabled' : 'menuItemDisabled';
                $result .= $this->formatTemplate($disabledItem, [
                    'text' => $item['label'],
                ]);
                continue;
            }

            $item['icon'] = $item['icon']
                ?? $this->getConfig('defaultIcon')[$level]
                ?? $this->getConfig('defaultIcon')['default']
                ?? $this->getConfig('defaultIcon')
                ?? null;
            $itemLink = $isChild ? 'dropdownItemLink' : 'menuItemLink';
            $itemLinkNest = $isChild ? 'dropdownItemLinkNest' : 'menuItemLinkNest';
            $template = empty($item['children']) ? $itemLink : $itemLinkNest;
            $link = $this->formatTemplate($template, [
                'url' => $this->Url->build($item['url'] ?? '#'),
                'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : '',
                'text' => $item['label'],
            ]);

            $nest = '';
            if (!empty($item['children'])) {
                $nest = $this->formatTemplate('dropdownContainer', [
                    'items' => $this->buildMenuItems($item['children'], $options, $level + 1),
                ]);
            }

            $containerTemplate = $isChild ? 'dropdownItem' : 'menuItem';
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
