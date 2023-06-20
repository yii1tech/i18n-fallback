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
 * @property \CMessageSource $owner
 * @property \CMessageSource|array $fallbackMessageSource fallback message source.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class MessageSourceFallbackBehavior extends CBehavior
{
    /**
     * @var \CMessageSource|array fallback message source or its array configuration.
     */
    private $_fallbackMessageSource = [];

    /**
     * @var string|null language which should be used to retrieve missing language source.
     * If `null` specified - the same previously failed language will be used.
     */
    public $fallbackLanguage = null;

    /**
     * Sets the message source for the translation fallback.
     *
     * @param \CMessageSource|array $fallbackMessageSource message source instance or its array configuration.
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
        $language = $this->fallbackLanguage;
        if ($language === null) {
            $language = $event->language;
        }

        $event->message = $this->getFallbackMessageSource()->translate($event->category, $event->message, $language);
    }
}