<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use WP_Post;

/**
 * @phpstan-import-type MetaboxContext from Metabox
 */
abstract class AbstractMetabox implements Metabox
{
    /** @var MetaboxContext */
    protected static $context = 'side';

    /** @var array<string> */
    protected static $postTypes = ['post', 'page'];

    public function __construct()
    {
        add_filter(PageOrPostOptions::HOOK_FILTER_METABOXES, function (
            array $boxes
        ) {
            $boxes[] = $this;
            return $boxes;
        });
    }

    abstract public function getKey(): string;

    abstract public function getTitle(): string;

    /**
     * @return MetaboxContext
     */
    public function getContext(): string
    {
        return static::$context;
    }

    /**
     * @return array<string>
     */
    public function getPostTypes(): array
    {
        return static::$postTypes;
    }

    public function getRenderHook(): string
    {
        return PageOrPostOptions::HOOK_ACTION_RENDER_FIELDS . $this->getKey();
    }

    public function render(WP_Post $post): void
    {
        do_action($this->getRenderHook(), $post);
    }
}
