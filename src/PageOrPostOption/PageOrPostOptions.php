<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use WP_Post;

class PageOrPostOptions
{
    public const HOOK_FILTER_METABOXES = 'ghwp-filter-boxes';
    public const HOOK_ACTION_RENDER_FIELDS = 'ghwp-action-render-fields-metabox-';
    public const HOOK_FILTER_POST_OPTIONS = 'ghwp-filter-post-options';

    public static function init(): void
    {
        add_action('add_meta_boxes', [self::class, 'onAddMetaBoxes']);
        add_action('save_post', [self::class, 'onSavePost'], 10, 2);
    }

    public static function onAddMetaBoxes(): void
    {
        /** @var array<Metabox> $boxes */
        $boxes = apply_filters(self::HOOK_FILTER_METABOXES, []);
        // Render metaboxes for all available options on their relevant post types
        foreach ($boxes as $metaBox) {
            foreach ($metaBox->getPostTypes() as $screen) {
                add_meta_box(
                    $metaBox->getKey(),
                    $metaBox->getTitle(),
                    [$metaBox, 'render'],
                    $screen,
                    $metaBox->getContext(),
                );
            }
        }
    }

    /**
     * @param WP_Post $post
     */
    public static function onSavePost(int $postId, $post): ?int
    {
        if (!(is_object($post) && is_a($post, WP_Post::class))) {
            return null;
        }

        if (!NonceField::getInstance()->validate($_POST)) {
            return $postId;
        }

        $postType = get_post_type_object($post->post_type);
        if (!$postType) {
            return $postId;
        }

        if (!current_user_can($postType->cap->edit_post, $postId)) {
            return $postId;
        }

        /** @var array<PageOrPostOption> $options */
        $options = apply_filters(self::HOOK_FILTER_POST_OPTIONS, []);

        foreach ($options as $option) {
            if (
                in_array($postType->name, $option->getMetabox()->getPostTypes())
            ) {
                $option->onChange($postId, $_POST);
            }
        }

        return null;
    }
}
