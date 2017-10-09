<?php

namespace SNSPush\ARN;

use InvalidArgumentException;
use SNSPush\Exceptions\InvalidArnException;
use SNSPush\Region;

abstract class ARN
{
    /**
     * AWS Region.
     *
     * @var string
     */
    protected $region;

    /**
     * AWS Account ID.
     *
     * @var integer
     */
    protected $accountId;

    /**
     * Target endpoint.
     *
     * @var string
     */
    protected $target;

    /**
     * ARN constructor.
     *
     * @param Region|string             $region
     * @param                           $accountId
     * @param                           $target
     *
     * @throws \InvalidArgumentException
     */
    public function __construct($region, $accountId, $target)
    {
        $this->setRegion($region);
        $this->setAccountId($accountId);
        $this->setTarget($target);
    }

    /**
     * Set the AWS target endpoint.
     *
     * @param mixed $target
     */
    abstract public function setTarget($target);

    /**
     * Get the endpoint key for the specified ARN.
     *
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Get the AWS region.
     *
     * @return Region
     */
    public function getRegion(): Region
    {
        return $this->region;
    }

    /**
     * Set the AWS region.
     *
     * @param mixed $region
     *
     * @throws \InvalidArgumentException
     */
    public function setRegion($region)
    {
        if (!$region instanceof Region) {
            $region = Region::parse($region);
        }

        $this->region = $region;
    }

    /**
     * Get the AWS account id.
     *
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Set the AWS account id.
     *
     * @param mixed $accountId
     *
     * @throws \InvalidArgumentException
     */
    public function setAccountId(string $accountId)
    {
        if (!ctype_digit($accountId)) {
            throw new InvalidArgumentException('The account id may only contain numbers.');
        }

        $this->accountId = $accountId;
    }

    /**
     * Get the AWS target endpoint.
     *
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Get the full AWS endpoint ARN.
     *
     * @return string
     */
    public function getString(): string
    {
        return 'arn:aws:sns:' . $this->getRegion()->getString() . ':' . $this->getAccountId() . ':' . $this->getTarget();
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

        if (count($parts) !== 6) {
            throw new InvalidArnException('This ARN is invalid. Expects 6 parts.');
        }

        foreach([0 => 'arn', 1 => 'aws', 2 => 'sns'] as $index => $expected) {
            if ($parts[$index] !== $expected) {
                throw new InvalidArnException("Required part of arn ({$expected}) is missing.");
            }
        }

        return new static(Region::parse($parts[3]), $parts[4], $parts[5]);
    }
}