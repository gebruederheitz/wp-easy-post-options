<?php

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use Gebruederheitz\Wordpress\MetaFields\Exception\InvalidFieldConfigurationException;
use Gebruederheitz\Wordpress\MetaFields\MetaForms;
use WP_Post;

abstract class AbstractPageOrPostOption implements PageOrPostOption
{
    /** @var bool */
    protected static $hasLinks = false;

    /** @var string */
    protected static $key = '';

    /** @var string */
    protected static $inputLabel = '';

    /** @var Metabox */
    protected $metabox;

    /** @var NonceField */
    protected $nonceField;

    public function __construct(Metabox $metabox, NonceField $nonceField)
    {
        $this->nonceField = $nonceField;
        $this->metabox = $metabox;

        add_action($this->metabox->getRenderHook(), [$this, 'render']);

        add_filter(PageOrPostOptions::HOOK_FILTER_POST_OPTIONS, function (
            array $options
        ) {
            $options[] = $this;

            return $options;
        });

        if (static::$hasLinks) {
            $this->initScripts();
        }
    }

    /**
     * @return mixed
     */
    public static function getValue(int $postId)
    {
        $originalValue = get_post_meta($postId, static::getMetaKey(), true);
        return static::parseValue($originalValue);
    }

    /**
     * @param mixed $rawValue
     *
     * @return mixed
     */
    public static function parseValue($rawValue)
    {
        return $rawValue;
    }

    public function getMetabox(): Metabox
    {
        return $this->metabox;
    }

    public function onEnqueueScripts(string $hook): void
    {
        global $post;

        if ($hook == 'post-new.php' || $hook == 'post.php') {
            if ($post->post_type === 'page' || $post->post_type === 'post') {
                wp_enqueue_script('wp-link');
            }
        }
    }

    /**
     * @param ?array<string, mixed> $data
     */
    public function onChange(int $postId, ?array $data): void
    {
        $rawValue = $data[static::$key] ?? null;
        $value =
            isset($rawValue) && is_string($rawValue)
                ? sanitize_text_field($rawValue)
                : '';

        update_post_meta($postId, static::getMetaKey(), $value);
    }

    /**
     * @throws InvalidFieldConfigurationException
     */
    public function render(WP_Post $post): void
    {
        $this->nonceField->render();
        $rawValue = static::getValue($post->ID);
        $value = is_string($rawValue) ? $rawValue : '';

        MetaForms::makeTextInputField()
            ->setName(static::$key)
            ->setValue($value)
            ->setLabel(static::$inputLabel)
            ->render();
    }

    public static function getMetaKey(): string
    {
        return '_' . static::$key;
    }

    private function initScripts(): void
    {
        add_action('admin_enqueue_scripts', [$this, 'onEnqueueScripts'], 10, 1);
    }
}
