<?php

namespace SNSPush\ARN;

class EndpointARN extends ARN
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
        return 'TargetArn';
    }

    /**
     * Get the endpoint key for removing device.
     *
     * @return string
     */
    public function getRemoveDeviceKey(): string
    {
        return 'EndpointArn';
    }
}