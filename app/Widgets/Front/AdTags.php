<?php

namespace App\Widgets\Front;

use App\AdTag;
use Arrilot\Widgets\AbstractWidget;

class AdTags extends AbstractWidget
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [
        'heading' => null,
        'tags' => null,
        'output' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $this->config['heading'] = $this->config['heading']
            ?? (app()->getLocale() === 'ru' ? 'Метки объявлений' : 'Мітки оголошень');
        $tags = [];
        if ($this->config['tags']) {
            foreach ($this->config['tags'] as $tag) {
                if (!empty($tag->slug)) {
                    $tags[] = [
                        'name' => $tag->name,
                        'url' => route('tag', ['slug' => $tag->slug]),
                    ];
                }
            }
        }

        $this->config['output'] = $tags;

        return view('widgets.front.ad_tags', [
            'config' => $this->config,
        ]);
    }
}
