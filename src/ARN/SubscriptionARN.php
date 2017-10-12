<?php

namespace SNSPush\ARN;

class SubscriptionARN extends ARN
{
    /**
     * Set the AWS target endpoint.
     *
     * @param mixed $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * Get the endpoint key.
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'SubscriptionArn';
    }

    /**
     * Parse provided ARN string.
     *
     * @param $string
     *
     * @return static
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\InvalidArnException
     */
    public static function parse($string)
    {
        $parts = explode(':', $string);

        if (count($parts) !== 7) {
            throw new InvalidArnException('This ARN is invalid. Expects 7 parts.');
        }

        foreach([0 => 'arn', 1 => 'aws', 2 => 'sns'] as $index => $expected) {
            if ($parts[$index] !== $expected) {
                throw new InvalidArnException("Required part of arn ({$expected}) is missing.");
            }
        }

        return new static(Region::parse($parts[3]), $parts[4], $parts[5] . ':' . $parts[6]);
    }
}