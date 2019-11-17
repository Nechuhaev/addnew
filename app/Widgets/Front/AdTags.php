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
        'heading' => 'Метки объявлений',
        'tags' => null,
        'output' => []
    ];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $tags = [];
        if ($this->config['tags']) {
            foreach ($this->config['tags'] as $tag) {
                $tags[] = [
                    'name' => $tag->name,
                    'url' => route('tag', ['slug' => $tag->slug]),
                ];
            }
        }

        $this->config['output'] = $tags;

        return view('widgets.front.ad_tags', [
            'config' => $this->config,
        ]);
    }
}
