<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use WP_Post;

/**
 * @phpstan-type MetaboxContext "side"|"normal"|"advanced"
 */
interface Metabox
{
    /**
     * @return MetaboxContext
     */
    public function getContext(): string;

    public function getKey(): string;

    public function getRenderHook(): string;

    /**
     * @return array<string>
     */
    public function getPostTypes(): array;

    public function getTitle(): string;

    public function render(WP_Post $post): void;
}
