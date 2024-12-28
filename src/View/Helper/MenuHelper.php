<?php

declare(strict_types=1);

namespace BsUtils\View\Helper;

use Cake\Core\InstanceConfigTrait;
use Cake\Utility\Hash;
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
        'menuClass' => 'nav nav-pills',
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
     * @param array $items
     * @param array $options
     * @return string
     */
    public function render(array $items, array $options = []): string
    {
        $options = Hash::merge($this->getConfig(), $options);

        if (isset($options['activeItem'])) {
            $this->activeItem($options['activeItem']);
        }

        if (isset($options['templates'])) {
            $this->setTemplates($options['templates']);
        }

        return $this->formatTemplate('menuContainer', [
            'menuClass' => $options['menuClass'] ?? null,
            'items' => $this->buildMenuItems($items, $options),
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
     * @param integer $level
     * @return string
     */
    protected function buildMenuItems(array $items, array $options, int $level = 0): string
    {
        $result = '';
        foreach ($items as $key => $item) {
            if (!$this->itemShow($item)) {
                continue;
            }
            $item['key'] = (string) $key;
            $result .= $this->buildMenuItem($item, $options, $level);
        }

        return $result;
    }

    /**
     * @param array $item
     * @param array $options
     * @param integer $level
     * @return string
     */
    protected function buildMenuItem(array $item, array $options, int $level): string
    {
        $hasChildren = !empty($item['children']);
        $isChild = $level > 0;
        $item['type'] = $item['type'] ?? self::ITEM_TYPE_LINK;

        if ($item['type'] === self::ITEM_TYPE_TITLE) {
            return $this->formatTemplate('menuTitle', [
                'text' => $item['label'],
            ]);
        }

        if ($item['type'] === self::ITEM_TYPE_DIVIDER) {
            return $this->formatTemplate('divider', []);
        }

        if ($this->itemDisabled($item)) {
            $disabledItem = $isChild ? 'dropdownItemDisabled' : 'menuItemDisabled';
            return $this->formatTemplate($disabledItem, [
                'text' => $item['label'],
            ]);
        }

        $isActiveItem = $this->isActiveItem($item, $level);
        $item['icon'] = $item['icon']
            ?? is_string($options['defaultIcon']) ? $options['defaultIcon'] : null
            ?? $options['defaultIcon'][$level]
            ?? $options['defaultIcon']['default']
            ?? null;
        $itemLink = $isChild ? 'dropdownItemLink' : 'menuItemLink';
        $itemLinkNest = $isChild ? 'dropdownItemLinkNest' : 'menuItemLinkNest';
        $template = $hasChildren ? $itemLinkNest : $itemLink;
        $link = $this->formatTemplate($template, [
            'url' => $this->Url->build($item['url'] ?? '#'),
            'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : null,
            'text' => $item['label'] ?? null,
            'activeClass' => $this->cssClass($isActiveItem ? $options['activeClass'] : null),
            'linkClass' => $this->cssClass($item['link'] ?? null),
        ]);

        $nest = null;
        if ($hasChildren) {
            $nest = $this->formatTemplate('dropdownContainer', [
                'items' => $this->buildMenuItems($item['children'], $options, $level + 1),
            ]);
        }

        $containerTemplate = $isChild ? 'dropdownItem' : 'menuItem';
        return $this->formatTemplate($containerTemplate, [
            'class' => $this->cssClass($item['class'] ?? null),
            'activeClass' => $this->cssClass($isActiveItem ? $options['activeClass'] : null),
            'dropdownClass' => $this->cssClass(!empty($item['children']) ? $options['dropdownClass'] : null),
            'dropdownOpenClass' => $this->cssClass($isActiveItem ? $options['dropdownOpenClass'] : null),
            'text' => $link,
            'nest' => $nest ?? null,
        ]);
    }

    /**
     * @param array $item
     * @return boolean
     */
    protected function itemShow(array $item): bool
    {
        if (isset($item['show']) && $item['show'] === false) {
            return false;
        }

        if (isset($item['show']) && is_callable($item['show']) && !$item['show']()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $item
     * @return boolean
     */
    protected function itemDisabled(array $item): bool
    {
        if (isset($item['type']) && $item['type'] === self::ITEM_TYPE_DISABLED) {
            return true;
        }

        if (isset($item['disabled']) && (
            $item['disabled'] === true ||
            (is_callable($item['disabled']) && $item['disabled']())
        )) {
            return true;
        }

        return false;
    }

    /**
     * @param array $item
     * @param integer $level
     * @return boolean
     */
    protected function isActiveItem(array $item, int $level): bool
    {
        if (isset($item['active']) && $item['active'] === true) {
            return true;
        }

        if (isset($item['active']) && is_callable($item['active']) && $item['active']()) {
            return true;
        }

        $currentActiveKey = (string) ($this->activeKeys[$level] ?? null);
        if ($currentActiveKey === ($item['key'] ?? null)) {
            return true;
        }

        return false;
    }

    /**
     * @param string|array $class
     * @return string
     */
    protected function cssClass(string|array|null $class): string
    {
        if (is_array($class)) {
            $class = implode(' ', array_filter($class));
        }

        if (empty($class)) {
            return '';
        }

        return ' ' . trim($class);
    }
}
