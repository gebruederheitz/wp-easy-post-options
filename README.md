# Wordpress Easy Post Options

_Extend posts with meta fields / options using a simple, modern interface._

---

## Installation

via composer:
```shell
> composer require gebruederheitz/wp-easy-post-options
```

Make sure you have Composer autoload or an alternative class loader present.


## Usage

This package offers a bunch of interfaces, abstract classes and handler classes
that simplifies adding "post meta" to posts or pages in Wordpress.

To enable the basic functionality, you'll have to load the initializer class,
either in your functions.php or somewhere in a controller class or container
module:

```php
\Gebruederheitz\Wordpress\PageOrPostOption\PageOrPostOptions::init();
```

### Creating meta boxes

Each custom option will need to be added to a meta box (which can contain 
multiple options). To easily create a meta box, use the `AbstractMetabox` class:

```php
use Gebruederheitz\Wordpress\PageOrPostOption\AbstractMetabox;

class SocialLinksMetabox extends AbstractMetabox 
{
    
    public function getKey(): string 
    {
        // give this metabox a unique identifier
        return 'ghwp-social-links';
    }
    
    public function getTitle(): string 
    {
        // This title will be visible to the user
        return 'Social Links';
    }
}
```

#### Restricting the post type(s) a metabox is displayed on

By default, meta boxes are shown on all posts of the types 'post' and 'page'. 
You can customize this behaviour by declaring a static class attribute `$postTypes`:

```php
use Gebruederheitz\Wordpress\PageOrPostOption\AbstractMetabox;

class SocialLinksMetabox extends AbstractMetabox 
{
    
    // Not available on pages, but on all regular posts and all posts of the 
    // custom "my-custom-post-type" type.
    protected static $postTypes = ['post', 'my-custom-post-type'];
    
    public function getKey(): string 
    {
        return 'ghwp-social-links';
    }
    
    public function getTitle(): string 
    {
        return 'Social Links';
    }
}
```

#### Changing where a meta box is shown

Similarly, you can customize the meta box's location by providing the static
class attribute `$context`, set to any of `side`, `normal` or `advanced` (blame
Wordpress, not me!):

```php
use Gebruederheitz\Wordpress\PageOrPostOption\AbstractMetabox;

class SocialLinksMetabox extends AbstractMetabox 
{
 
    protected static $context = 'advanced';
        
    public function getKey(): string 
    {
        return 'ghwp-social-links';
    }
    
    public function getTitle(): string 
    {
        return 'Social Links';
    }
}
```


### Adding post options

Once your meta box is created, you can add settings to it. The quickest way is
by extending `AbstractPageOrPostOption`:

```php

use Gebruederheitz\Wordpress\PageOrPostOption\AbstractPageOrPostOption;

class FacebookUrlPageOption extends AbstractPageOrPostOption 
{

    /** 
     * @var bool
     * @optional
     * You can set this to `true` to automatically have the "wp-link" script
     * enqueued – otherwise you can leave it out.  
     */
    protected static $hasLinks = false;

    /** 
     * @var string 
     * @required
     * A unique identifier for this setting, which will be used in the 
     * database amongst * others.
     */
    protected static $key = 'ghwp-social-facebook-url';

    /** 
     * @var string
     * A label to show the user next to the input field. When using MetaForms, 
     * this * will be automatically translated. It is strongly recommended you 
     * set this * attribute, especially if you dont' plan to override the 
     * default render method. 
     */
    protected static $inputLabel = 'Facebook URL';
}
```

Then, make sure you instantiate that class somewhere (usually this will be your
`functions.php`), along with the meta box the input should live in:

```php
use Gebruederheitz\Wordpress\PageOrPostOption\NonceField;

$socialBox = new SocialLinksMetaBox();
new FacebookUrlPageOption($socialBox, NonceField::getInstance());
```

For a basic text input field that's it, your're done. You can then retrieve the
value by calling the static `getValue()` method on your custom class:

```php
$facebookUrl = FacebookUrlPageOption::getValue($post->ID);
```

#### Displaying a boolean option using a checkbox field

The library offers another little helper for displaying simple checkbox fields:

```php
use Gebruederheitz\Wordpress\PageOrPostOption\AbstractBooleanPageOrPostOption;

class MyCheckboxField extends AbstractBooleanPageOrPostOption
{
    /** @var string */
    protected static $key = 'ghwp-my-checkbox';

    /** @var string */
    protected static $inputLabel = 'Checkbox';
}
```

#### Modifying the inputs and value parsing

In order to customize the form controls rendered into the metabox, you can 
override the `render()` method of the base class.

```php

use Gebruederheitz\Wordpress\PageOrPostOption\AbstractPageOrPostOption;
use Gebruederheitz\Wordpress\MetaFields\MetaForms;

class BackgroundImagePageOption extends AbstractPageOrPostOption 
{
    protected static $key = 'ghwp-background-image';
    
    // We define a second key for convenience
    protected static $urlKey = 'ghwp-background-image-url';

    protected static $inputLabel = 'Background image';
    
    /**
     * Called when the meta box is being rendered in WP admin
     */
    public function render(WP_Post $post): void
    {
        // make sure you call the nonce field's render method at least once for
        // every page containing page options
        $this->nonceField->render();
        
        // we retrieve the original value
        $rawValue = $this->getValue($post->ID);
        $value = is_string($rawValue) 
            ? json_decode($rawValue) 
            : (object) ['id' => '', 'url' => ''];

        // ...and then render our input field. You could use the MetaForms library
        // from gebruederheitz/wp-meta-fields, output some raw HTML or include
        // a PHP or Twig template.
        MetaForms::makeMediaPicker()
            ->setIdFieldName(self::$key)
            ->setUrlFieldName(self::$urlKey);
            ->setLabel(self::$inputLabel)
            ->setIdFieldValue($value->id)
            ->setUrlFieldValue($value->url)
            ->render();
    }
    
     /**
     * @param ?array<string, mixed> $data The POST data submitted from wp admin
     */
    public function onChange(int $postId, ?array $data): void
    {
        $rawId = $data[static::$key] ?? null;
        $rawUrl = $data[static::$urlKey]
        // this is where you should do some validation and sanitization
        $value = json_encode([
            'id' => (int) $rawId,
            'url' => $rawUrl,
        ]);   

        update_post_meta($postId, static::getMetaKey(), $value);
    }
}
```


## Note: wp-easy-post-options and adretto-extension-simple-post-options

This package is developed and maintained in parallel with 
[sillynet/adretto-extension-simple-post-options](https://packagist.org/packages/sillynet/adretto-extension-simple-post-options), 
which offers the same basic functionality, but is written to embed itself into 
the Adretto microframework (a work in progress). In the future it is likely that 
one of these two packages will reuse most of the code from the other one, in 
order to avoid large-scale code duplication. This will however most likely have
no effect on the public API of either project.

## Development

### Dependencies

 - PHP >= 7.3
 - [Composer 2.x](https://getcomposer.org)
 - [NVM](https://github.com/nvm-sh/nvm) and nodeJS LTS (v16.x)
 - Nice to have: GNU Make (or drop-in alternative)
