<?php

namespace SNSPush\Messages;

/**
 * the message interface
 */
interface MessageInterface
{
    /**
     * builds the iOS message data array
     *
     * @return array
     */
    public function getIosData(): array;

    /**
     * builds the android message data array
     *
     * @return array
     */
    public function getAndroidData(): array;
}
