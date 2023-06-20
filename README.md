<p align="center">
    <a href="https://github.com/yii1tech" target="_blank">
        <img src="https://avatars.githubusercontent.com/u/134691944" height="100px">
    </a>
    <h1 align="center">Translation Fallback Extension for Yii1</h1>
    <br>
</p>

This extension provides support for Yii1 translation fallback to another message source.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://img.shields.io/packagist/v/yii1tech/i18n-fallback.svg)](https://packagist.org/packages/yii1tech/i18n-fallback)
[![Total Downloads](https://img.shields.io/packagist/dt/yii1tech/i18n-fallback.svg)](https://packagist.org/packages/yii1tech/i18n-fallback)
[![Build Status](https://github.com/yii1tech/i18n-fallback/workflows/build/badge.svg)](https://github.com/yii1tech/i18n-fallback/actions)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii1tech/i18n-fallback
```

or add

```json
"yii1tech/i18n-fallback": "*"
```

to the "require" section of your composer.json.


Usage
-----

This extension provides support for Yii1 translation fallback to another message source.
For example: you may setup an administration panel to edit the translation stored in the database.
At the same time you may want to setup default translations via local files stored under VCS.
Application configuration example:

```php
<?php

return [
    'components' => [
        'messages' => [
            'class' => CDbMessageSource::class, // use database source, controlled via admin panel
            'forceTranslation' => true,
            'behaviors' => [
                'fallbackBehavior' => [
                    'class' => yii1tech\i18n\fallback\MessageSourceFallbackBehavior::class,
                    'fallbackMessageSource' => [
                        'class' => CPhpMessageSource::class, // fallback to local translation files, if message is missing in the database
                        'forceTranslation' => true,
                    ],
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

In case `\yii1tech\i18n\fallback\MessageSourceFallbackBehavior::$fallbackMessageSource` is not set, behavior will use its owner for it.
This allows to set up a fallback to a some default language for the translations, which are missing at other language.
The fallback language is controlled via `\yii1tech\i18n\fallback\MessageSourceFallbackBehavior::$fallbackLanguage`.
For example:

```php
<?php

return [
    'components' => [
        'messages' => [
            'class' => CPhpMessageSource::class, // setup single message source
            'forceTranslation' => true,
            'behaviors' => [
                'fallbackBehavior' => [
                    'class' => yii1tech\i18n\fallback\MessageSourceFallbackBehavior::class,
                    'fallbackLanguage' => 'en_us', // fallback to 'en_us', if translation is missing in some language (like 'es', 'ru', etc.)
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```

> Note: you can setup `\yii1tech\i18n\fallback\MessageSourceFallbackBehavior::$fallbackLanguage` with a particular message source.
  For example: you may support local translation files for the default ('en_us') language only.
  If `\yii1tech\i18n\fallback\MessageSourceFallbackBehavior::$fallbackMessageSource` is omitted - behavior owner instance will be used
  as a fallback one.

You may attach `yii1tech\i18n\fallback\MessageSourceFallbackBehavior` to the fallback message source as well, creating a fallback chain.
For example:

```php
<?php

return [
    'components' => [
        'messages' => [
            'class' => CDbMessageSource::class, // use database source, controlled via admin panel
            'forceTranslation' => true,
            'behaviors' => [
                'fallbackBehavior' => [
                    'class' => yii1tech\i18n\fallback\MessageSourceFallbackBehavior::class,
                    'fallbackMessageSource' => [
                        'class' => CPhpMessageSource::class, // fallback to local translation files, if message is missing in the database
                        'forceTranslation' => true,
                        'behaviors' => [
                            'fallbackBehavior' => [
                                'class' => yii1tech\i18n\fallback\MessageSourceFallbackBehavior::class,
                                'fallbackLanguage' => 'en_us', // fallback to 'en_us', if translation is missing in some language (like 'es', 'ru', etc.)
                            ],
                        ],
                    ],
                ],
            ],
        ],
        // ...
    ],
    // ...
];
```
