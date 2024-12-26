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

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'nestClass' => 'dropdown',
        'activeClass' => 'active',
        'templates' => [
            'menu' => '<ul class="menu">{{items}}</ul>',
            'menuItem' => '<li class="{{class}}">{{text}}{{nest}}</li>',
            'menuLink' => '<a href="{{url}}" class="">{{icon}}<span>{{text}}</span></a>',
            'nest' => '<ul class="dropdown-menu">{{items}}</ul>',
            'nestItem' => '<li class="dropdown-item {{class}}">{{text}}{{nest}}</li>',
            'nestLink' => '<a href="{{url}}" class="">{{icon}}{{text}}</a>',
            'icon' => '<i class="{{icon}}"></i>',
        ],
    ];

    protected array $helpers = ['Url'];

    protected array $activeKeys = [];

    public function render(array $menu, array $options = []): string
    {
        $this->activeKeys = explode('.', $options['activeItem'] ?? '');
        return $this->formatTemplate('menu', [
            'class' => $options['menu.class'] ?? $this->getConfig('menu.class') ?? 'menu',
            'items' => $this->buildMenuItems($menu, $options),
        ]);
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
            if (isset($item['type']) && $item['type'] === 'title' && !($isChild)) {
                $result .= $this->formatTemplate('menuItem', [
                    'text' => $item['title'],
                ]);
                continue;
            }

            $template = $isChild ? 'nestLink' : 'menuLink';
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

        return ' ' . trim($class);
    }
}
