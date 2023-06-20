<?php

namespace yii1tech\i18n\fallback;

use CPhpMessageSource;
use Yii;
use CBehavior;

/**
 * MessageSourceBehaviorFallback is a behavior for {@see \CMessageSource}, which allows fallback
 * to other (default) message source in case the main one can not find a particular translation.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'messages' => [
 *             'class' => CDbMessageSource::class,
 *             'forceTranslation' => true,
 *             'behaviors' => [
 *                 'fallbackBehavior' => [
 *                     'class' => yii1tech\i18n\fallback\MessageSourceFallbackBehavior::class,
 *                     'fallbackMessageSource' => [
 *                         'class' => CPhpMessageSource::class,
 *                         'forceTranslation' => true,
 *                     ],
 *                 ],
 *             ],
 *         ],
 *         // ...
 *     ],
 *     // ...
 * ];
 * ```
 *
 * @property \CMessageSource $owner the owner message source that this behavior is attached to.
 * @property \CMessageSource|array|null $fallbackMessageSource fallback message source.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MessageSourceFallbackBehavior extends CBehavior
{
    /**
     * @var \CMessageSource|array|null fallback message source or its array configuration.
     * If `null` - owner instance will be used.
     */
    private $_fallbackMessageSource = null;

    /**
     * @var string|null language which should be used to retrieve missing language source.
     * If `null` specified - the same previously failed language will be used.
     */
    public $fallbackLanguage = null;

    /**
     * Sets the message source for the translation fallback.
     *
     * @param \CMessageSource|array|null $fallbackMessageSource message source instance or its array configuration, `null` means owner instance usage.
     * @return static self reference.
     */
    public function setFallbackMessageSource($fallbackMessageSource): self
    {
        $this->_fallbackMessageSource = $fallbackMessageSource;

        return $this;
    }

    /**
     * Returns message source for the translation fallback.
     *
     * @return \CMessageSource message source.
     */
    public function getFallbackMessageSource()
    {
        if ($this->_fallbackMessageSource === null) {
            return $this->owner;
        }

        if (!is_object($this->_fallbackMessageSource)) {
            $this->_fallbackMessageSource = $this->createFallbackMessageSource($this->_fallbackMessageSource);
        }

        return $this->_fallbackMessageSource;
    }

    /**
     * @param array $config message source configuration.
     * @return \CMessageSource message source instance.
     */
    protected function createFallbackMessageSource(array $config)
    {
        if (!array_key_exists('class', $config)) {
            $config['class'] = CPhpMessageSource::class;
        }
        $messageSource = Yii::createComponent($config);
        $messageSource->init();

        return $messageSource;
    }

    /**
     * {@inheritdoc}
     */
    public function events()
    {
        return [
            'onMissingTranslation' => 'missingTranslationHandler',
        ];
    }

    /**
     * Handles event when a message cannot be translated.
     *
     * @param \CMissingTranslationEvent $event the event instance.
     */
    public function missingTranslationHandler($event): void
    {
        $language = $this->fallbackLanguage ?? $event->language;

        $fallbackMessageSource = $this->getFallbackMessageSource();
        if ($fallbackMessageSource === $event->sender && $language === $event->language) {
            return; // avoid infinite recursion
        }

        $event->message = $fallbackMessageSource->translate($event->category, $event->message, $language);
    }
}