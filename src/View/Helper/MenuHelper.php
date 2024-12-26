<?php

declare(strict_types=1);

namespace BsUtils\View\Helper;

use Cake\View\Helper;
use Cake\View\StringTemplateTrait;

/**
 * Menu helper
 */
class MenuHelper extends Helper
{
    use StringTemplateTrait;

    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'menu' => [
            'class' => 'menu',
        ],
        'item' => [
            'class' => 'sidebar-item',
            'linkClass' => 'sidebar-link',
            'iconClass' => 'bi bi-caret-right-fill',
            'textClass' => 'sidebar-title',
        ],
        'submenu' => [
            'class' => 'submenu',
            'itemClass' => 'submenu-item',
        ],
        'templates' => [],
    ];

    protected array $helpers = ['Html', 'Url'];

    public function initialize(array $config): void
    {
        parent::initialize($config);
    }

    public function render(array $menu, array $options = []): string
    {
        return $this->formatTemplate('menu', [
            'class' => $options['menu.class'] ?? $this->getConfig('menu.class') ?? 'menu',
            'items' => $this->buildItems($menu, $options),
        ]);
    }

    protected function buildItems(array $items, array $options): string
    {
        $result = '';
        foreach ($items as $item) {
            if (isset($item['type']) && $item['type'] === 'title') {
                $result .= $this->formatTemplate('text', [
                    'class' => $this->getConfig('textClass') ?? 'sidebar-title',
                    'text' => $item['title'],
                ]);
                continue;
            }

            $link = $this->formatTemplate('link', [
                'url' => $this->Url->build($item['url'] ?? '#'),
                'linkClass' => $item['linkClass'] ?? $this->getConfig('item.linkClass') ?? 'sidebar-link',
                'icon' => !empty($item['icon']) ? $this->formatTemplate('icon', ['icon' => $item['icon']]) : '',
                'title' => $this->formatTemplate('caption', ['text' => $item['title']]),
            ]);

            $nest = '';
            if (!empty($item['children'])) {
                $nest = $this->formatTemplate('menu', [
                    'class' => $this->getConfig('submenuClass') ?? 'submenu',
                    'items' => $this->buildItems($item['children'], [
                        'class' => $this->getConfig('submenuItemClass') ?? 'submenu-item',
                    ]),
                ]);
            }

            $result .= $this->formatTemplate('item', [
                'itemClass' => ($item['class'] ?? $options['class'] ?? 'sidebar-item') . (!empty($item['children']) ? ' has-sub' : ''),
                'link' => $link,
                'nest' => $nest,
            ]);
        }

        return $result;
    }
}
