<?php

declare(strict_types=1);

namespace Gebruederheitz\Wordpress\PageOrPostOption;

use Gebruederheitz\SimpleSingleton\Singleton;

class NonceField extends Singleton
{
    private const NONCE_ACTION = 'sillynet-update-pageoptions';
    private const NONCE_NAME = 'sillynet-pageoptions-nonce';

    /** @var bool */
    protected $hasRendered = false;

    /** @var bool */
    protected $isValidated = false;

    /**
     * Render the WP nonce field into the current request (if it hasn't already)
     */
    public function render(): void
    {
        if (!$this->hasRendered) {
            wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
            $this->hasRendered = true;
        }
    }

    /**
     * @param array<string, mixed> $requestData  Usually the $_POST data, which
     *                                           should contain the nonce
     *                                           field's value.
     *
     * @return bool
     */
    public function validate(array $requestData): bool
    {
        if (!$this->isValidated) {
            if (
                !isset($requestData[self::NONCE_NAME]) ||
                !wp_verify_nonce($_POST[self::NONCE_NAME], self::NONCE_ACTION)
            ) {
                $this->isValidated = true;
                return false;
            }
        }

        return true;
    }
}
