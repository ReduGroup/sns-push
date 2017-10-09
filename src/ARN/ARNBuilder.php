<?php

namespace SNSPush\ARN;

use InvalidArgumentException;
use SNSPush\SNSPush;

class ARNBuilder
{
    /**
     * AWS Account ID.
     *
     * @var string
     */
    protected $accountId;

    /**
     * AWS region.
     *
     * @var \SNSPush\Region
     */
    protected $region;

    /**
     * ARNBuilder constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->accountId = $config['account_id'];

        $this->region = Region::parse($config['region']);
    }

    /**
     * Create relevant ARN from provided type.
     *
     * @param $type
     * @param $target
     *
     * @return \SNSPush\ARN\EndpointARN|\SNSPush\ARN\TopicARN
     * @throws \InvalidArgumentException
     */
    public function create($type, $target)
    {
        if ($type === SNSPush::TYPE_ENDPOINT) {
            return $this->createEndpointARN($target);
        } elseif ($type === SNSPush::TYPE_TOPIC) {
            return $this->createTopicARN($target);
        }

        throw new InvalidArgumentException('Invalid type.');
    }

    /**
     * Create a topic ARN.
     *
     * @param $target
     *
     * @return \SNSPush\ARN\TopicARN
     * @throws \InvalidArgumentException
     */
    public function createTopicARN($target): TopicARN
    {
        return new TopicARN($this->region, $this->accountId, $target);
    }

    /**
     * Create an Application ARN.
     *
     * @param $target
     *
     * @return \SNSPush\ARN\ApplicationARN
     * @throws \InvalidArgumentException
     */
    public function createApplicationARN($target): ApplicationARN
    {
        return new ApplicationARN($this->region, $this->accountId, $target);
    }

    /**
     * Create an Endpoint ARN.
     *
     * @param $target
     *
     * @return \SNSPush\ARN\EndpointARN
     * @throws \InvalidArgumentException
     */
    public function createEndpointARN($target): EndpointARN
    {
        return new EndpointARN($this->region, $this->accountId, $target);
    }
}