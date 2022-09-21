<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use WP_Post;

interface PageOrPostOption
{
    public static function getMetaKey(): string;

    /**
     * @return mixed
     */
    public static function getValue(int $postId);

    public function getMetabox(): Metabox;

    /**
     * @param ?array<string, mixed> $data
     */
    public function onChange(int $postId, ?array $data): void;

    public function render(WP_Post $post): void;
}
