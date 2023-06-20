<?php

namespace yii1tech\i18n\fallback\test;

use CPhpMessageSource;
use Yii;
use yii1tech\i18n\fallback\MessageSourceFallbackBehavior;

class MessageSourceFallbackBehaviorTest extends TestCase
{
    /**
     * @param \yii1tech\i18n\fallback\MessageSourceFallbackBehavior|array $behavior
     * @return void
     */
    protected function attachMessageSourceBehavior($behavior): void
    {
        Yii::app()->getComponent('messages')->attachBehavior('fallbackBehavior', $behavior);
    }

    public function testFallbackToDifferentSource(): void
    {
        $this->attachMessageSourceBehavior([
            'class' => MessageSourceFallbackBehavior::class,
            'fallbackMessageSource' => [
                'class' => CPhpMessageSource::class,
                'basePath' => __DIR__ . '/messages/fallback',
                'forceTranslation' => true,
            ],
        ]);

        $this->assertSame('title-main-en_us', Yii::t('content', 'title'));
        $this->assertSame('header-fallback-en_us', Yii::t('content', 'header'));
    }

    public function testFallbackToDifferentLanguage(): void
    {
        $messageSource = Yii::app()->getComponent('messages');

        $this->attachMessageSourceBehavior([
            'class' => MessageSourceFallbackBehavior::class,
            'fallbackLanguage' => 'en_us',
            'fallbackMessageSource' => [
                'class' => get_class($messageSource),
                'basePath' => $messageSource->basePath,
                'forceTranslation' => true,
            ],
        ]);

        Yii::app()->setLanguage('es');

        $this->assertSame('title-main-es', Yii::t('content', 'title'));
        $this->assertSame('description-main-en_us', Yii::t('content', 'description'));
    }

    public function testFallbackToDifferentLanguageWithinSameMessageSource(): void
    {
        $this->attachMessageSourceBehavior([
            'class' => MessageSourceFallbackBehavior::class,
            'fallbackLanguage' => 'en_us',
        ]);

        /** @var \CMessageSource|MessageSourceFallbackBehavior $messageSource */
        $messageSource = Yii::app()->getComponent('messages');

        $this->assertSame($messageSource, $messageSource->getFallbackMessageSource());

        Yii::app()->setLanguage('es');

        $this->assertSame('title-main-es', Yii::t('content', 'title'));
        $this->assertSame('unexisting', Yii::t('content', 'unexisting'));
    }
}