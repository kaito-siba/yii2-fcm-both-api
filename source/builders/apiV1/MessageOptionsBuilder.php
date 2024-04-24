<?php

namespace aksafan\fcm\source\builders\apiV1;

use aksafan\fcm\source\builders\OptionsBuilder;
use aksafan\fcm\source\helpers\OptionsHelper;
use InvalidArgumentException;

/**
 * Builder for Message to send by FCM API v1.
 *
 * Class MessageOptionsBuilder
 *
 * Official google documentation:
 *
 * @link https://firebase.google.com/docs/reference/fcm/rest/v1/projects.messages
 */
class MessageOptionsBuilder implements OptionsBuilder
{
    const TOPIC_CONDITION = 'condition';
    const TOKEN = 'token';
    const TOKENS = 'tokens';
    const TOPIC = 'topic';

    const TYPES = [
        self::TOPIC_CONDITION,
        self::TOKEN,
        self::TOKENS,
        self::TOPIC,
    ];

    /**
     * @var array|null
     */
    private $data;

    /**
     * @var array|null
     */
    private $notification;

    /**
     * @var array|null
     */
    private $androidConfig;

    /**
     * @var array|null
     */
    private $apnsConfig;

    /**
     * @var array|null
     */
    private $webPushConfig;

    /**
     * @var boolean Flag
     */
    private $validateOnly = false;

    /**
     * @var string
     */
    private static $target;

    /**
     * @var string|array
     */
    private static $targetValue;

    /**
     * @param string $target One of "condition", "token", "topic" (see constants of current class)
     * @param string|array $value
     *
     * @throws InvalidArgumentException
     */
    public function setTarget(string $target, $value)
    {
        OptionsHelper::validateApiV1Target($target, $value);
        self::$target = $target;
        self::$targetValue = $value;
    }

    /**
     * @param array $data
     *
     * @throws InvalidArgumentException
     */
    public function setData(array $data)
    {
        OptionsHelper::validateData($data);
        $this->data = $data;
    }

    /**
     * @param string $title
     * @param string $body
     */
    public function setNotification(string $title, string $body)
    {
        $this->notification = [
            'title' => $title,
            'body' => $body,
        ];
    }

    /**
     * @param array $config
     */
    public function setAndroidConfig(array $config)
    {
        $this->androidConfig = $config;
    }

    /**
     * @param array $config
     */
    public function setApnsConfig(array $config)
    {
        $this->apnsConfig = $config;
    }

    /**
     * @param array $config
     */
    public function setWebPushConfig(array $config)
    {
        $this->webPushConfig = $config;
    }

    /**
     * @param boolean Flag for testing the request without actually delivering the message.
     */
    public function setValidateOnly(bool $validateOnly)
    {
        $this->validateOnly = $validateOnly;
    }

    /**
     * @return bool
     */
    public function getValidateOnly(): bool
    {
        return $this->validateOnly;
    }

    /**
     * @return array
     */
    public static function getTokens(): array
    {
        if (self::TOKENS === self::getTarget()) {
            return \is_array(self::getTargetValue()) ? self::getTargetValue() : [];
        }
        if (self::TOKEN === self::getTarget()) {
            return [self::getTargetValue()];
        }

        return [];
    }

    /**
     * @return string
     */
    public static function getTarget(): string
    {
        return (string) self::$target;
    }

    /**
     * @return array|string
     */
    public static function getTargetValue()
    {
        return self::$targetValue;
    }

    /**
     * Builds request body data.
     *
     * @return array
     */
    public function build(): array
    {
        if (self::getTarget() === self::TOKENS) {
            $options = [];
            foreach (self::getTokens() as $token) {
                $options[] = array_filter([
                    'token' => $token,
                    'data' => $this->data,
                    'notification' => $this->notification,
                    'android' => $this->androidConfig,
                    'apns' => $this->apnsConfig,
                    'webpush' => $this->webPushConfig,
                ]);
            }
            return $options;
        } else {
            return array_filter([
                self::getTarget() => self::getTargetValue(),
                'data' => $this->data,
                'notification' => $this->notification,
                'android' => $this->androidConfig,
                'apns' => $this->apnsConfig,
                'webpush' => $this->webPushConfig,
            ]);
        }
    }
}
