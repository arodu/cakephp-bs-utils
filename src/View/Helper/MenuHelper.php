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
        'menuClass' => 'nav',
        'dropdownClass' => 'dropdown',
        'activeClass' => 'active',
        'dropdownOpenClass' => 'dropdown-open',

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
            'menuContainer' => '<ul class="{{menuClass}}">{{items}}</ul>',
            'menuItem' => '<li class="nav-item{{class}}{{dropdownClass}}">{{text}}{{nest}}</li>',
            'menuItemDisabled' => '<li class="nav-item{{class}}"><a class="nav-link disabled" aria-disabled="true">{{icon}}{{text}}</a></li>',
            'menuItemLink' => '<a class="nav-link{{linkClass}}{{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',
            'menuItemLinkNest' => '<a class="nav-link dropdown-toggle{{linkClass}}{{activeClass}}" href="{{url}}" role="button" data-bs-toggle="dropdown" aria-expanded="false">{{icon}}{{text}}</a>',

            /**
             * Default templates for dropdown items.
             */
            'dropdownContainer' => '<ul class="dropdown-menu">{{items}}</ul>',
            'dropdownItem' => '<li>{{text}}{{nest}}</li>',
            'dropdownItemDisabled' => '<li>{{text}}{{nest}}</li>',
            'dropdownItemLink' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',
            'dropdownItemLinkNest' => '<a class="dropdown-item{{linkClass}}{{activeClass}}" href="{{url}}">{{icon}}{{text}}</a>',

            /**
             * Default templates for other items.
             */
            'icon' => '<i class="{{icon}}"></i>',
            'divider' => '<li><hr class="dropdown-divider"></li>',
            'menuTitle' => '<li class="nav-header">{{icon}}{{text}}</li>',
        ],
    ];

    /**
     * @var array
     */
    protected array $helpers = ['Url'];

    /**
     * @var array
     */
    protected array $activeKeys = [];

    /**
     * @param array $menu
     * @param array $options
     * @return string
     */
    public function render(array $menu, array $options = []): string
    {
        if (isset($options['activeItem'])) {
            $this->activeItem($options['activeItem']);
        }

        if (isset($options['templates'])) {
            $this->setTemplates($options['templates']);
        }

        return $this->formatTemplate('menuContainer', [
            'menuClass' => $options['menuClass'] ?? $this->getConfig('menuClass'),
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

            $hasChildren = !empty($item['children']);
            $isActiveItem = $currentActiveKey == (string) $key;

            $item['icon'] = $item['icon']
                ?? $this->getConfig('defaultIcon')[$level]
                ?? $this->getConfig('defaultIcon')['default']
                ?? $this->getConfig('defaultIcon')
                ?? null;
            $itemLink = $isChild ? 'dropdownItemLink' : 'menuItemLink';
            $itemLinkNest = $isChild ? 'dropdownItemLinkNest' : 'menuItemLinkNest';
            $template = $hasChildren ? $itemLinkNest : $itemLink;
            $link = $this->formatTemplate($template, [
                'url' => $this->Url->build($item['url'] ?? '#'),
                'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : '',
                'text' => $item['label'],
                'activeClass' => $this->cssClass($isActiveItem ? $this->getConfig('activeClass') : null),
                'linkClass' => $this->cssClass($item['link'] ?? null),
            ]);

            $nest = '';
            if ($hasChildren) {
                $nest = $this->formatTemplate('dropdownContainer', [
                    'items' => $this->buildMenuItems($item['children'], $options, $level + 1),
                ]);
            }

            $containerTemplate = $isChild ? 'dropdownItem' : 'menuItem';
            $result .= $this->formatTemplate($containerTemplate, [
                'class' => $this->cssClass($item['class'] ?? null),
                'activeClass' => $this->cssClass($isActiveItem ? $this->getConfig('activeClass') : null),
                'dropdownClass' => $this->cssClass($hasChildren ? $this->getConfig('dropdownClass') : null),
                'dropdownOpenClass' => $this->cssClass($isActiveItem ? $this->getConfig('dropdownOpenClass') : null),
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
    protected function cssClass(string|array|null $class): string
    {
        if (is_array($class)) {
            $class = implode(' ', $class);
        }

        if (!empty($class)) {
            return ' ' . trim($class);
        }
        
        return '';
    }
}
