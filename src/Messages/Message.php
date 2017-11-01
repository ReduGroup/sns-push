<?php

namespace SNSPush\Messages;

/**
 * message class for constructing SNS message data
 */
class Message implements MessageInterface
{

    /**
     * the message title
     *
     * @var string|null
     */
    protected $title;

    /**
     * the message body
     *
     * @var string|null
     */
    protected $body;

    /**
     * the message badge count
     *
     * @var int|null
     */
    protected $count;

    /**
     * the iOS notification sound
     *
     * @var string|null
     */
    protected $iosSound;

    /**
     * the android notification sound
     *
     * @var string|null
     */
    protected $androidSound;

    /**
     * whether the notification should be silent or not
     *
     * @var bool|null
     */
    protected $contentAvailable;

    /**
     * use android inbox mode
     *
     * @var bool
     */
    protected $useAndroidInboxMode = false;

    /**
     * the android inbox mode group message
     *
     * substitute %n% for the number of notifications
     *
     * @var string|null
     */
    protected $androidInboxModeGroupMessage = "%n% messages";

    /**
     * other payload data to be added to the message
     *
     * @var array|null
     */
    protected $payload;

    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return static
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody(): ?string
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return static
     */
    public function setBody(string $body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return int
     */
    public function getCount(): ?int
    {
        return $this->count;
    }

    /**
     * @param int $count
     *
     * @return static
     */
    public function setBadge(int $count)
    {
        $this->count = $count;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAndroidSound(): ?string
    {
        return $this->androidSound;
    }

    /**
     * @param string $androidSound
     *
     * @return static
     */
    public function setAndroidSound(string $androidSound)
    {
        $this->androidSound = $androidSound;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIosSound(): ?string
    {
        return $this->iosSound;
    }

    /**
     * @param string $iosSsound
     *
     * @return static
     */
    public function setIosSound(string $iosSound)
    {
        $this->iosSound = $iosSound;
        return $this;
    }

    /**
     * @return bool
     */
    public function getContentAvailable(): ?bool
    {
        return $this->contentAvailable;
    }

    /**
     * @param bool $contentAvailable
     *
     * @return static
     */
    public function setContentAvailable(bool $contentAvailable)
    {
        $this->contentAvailable = $contentAvailable;
        return $this;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload ?? [];
    }

    /**
     * @param array $payload
     *
     * @return static
     */
    public function setPayload(array $payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return bool
     */
    public function getUseAndroidInboxMode(): bool
    {
        return $this->useAndroidInboxMode;
    }

    /**
     * @param bool $useAndroidInboxMode
     *
     * @return static
     */
    public function setUseAndroidInboxMode(bool $useAndroidInboxMode = true)
    {
        $this->useAndroidInboxMode = $useAndroidInboxMode;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAndroidInboxModeGroupMessage(): ?string
    {
        return $this->androidInboxModeGroupMessage;
    }

    /**
     * @param string|null $androidInboxModeGroupMessage
     *
     * @return static
     */
    public function setAndroidInboxModeGroupMessage(?string $androidInboxModeGroupMessage)
    {
        $this->androidInboxModeGroupMessage = $androidInboxModeGroupMessage;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getIosData(): array
    {
        return $this->filterNull(
            array_merge([
                'aps' => [
                    'alert' => [
                        'title' => $this->getTitle(),
                        'body' => $this->getBody(),
                    ],
                    'sound' => $this->getIosSound(),
                    'badge' => $this->getCount(),
                    'content-available' => $this->getContentAvailable(),
                ]
            ], $this->getPayload())
        );
    }

    /**
     * @inheritDoc
     */
    public function getAndroidData(): array
    {
        return $this->filterNull(
            [
                'data' => array_merge([
                    'title' => $this->getTitle(),
                    'message' => $this->getBody(),
                    'sound' => $this->getAndroidSound(),
                    'badge' => $this->getCount(),
                    'content-available' => $this->getContentAvailable(),
                ], $this->getPayload())
            ]
        );
    }

    /**
     * recursively removes null values from an array
     *
     * @param  array  $arr the array to have null values removed
     *
     * @return array       the array minus any null values
     */
    public function filterNull(array $arr): array
    {
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $arr[$key] = $this->filterNull($value);
            }
        }

        return array_filter($arr, function ($var) {
            return !(is_null($var) || (is_array($var) && empty($var)));
        });
    }
}
