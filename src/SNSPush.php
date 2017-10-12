<?php

namespace SNSPush;

use Aws\ApiGateway\Exception\ApiGatewayException;
use Aws\Credentials\Credentials;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use SNSPush\ARN\ARN;
use SNSPush\ARN\ARNBuilder;
use SNSPush\ARN\EndpointARN;
use SNSPush\ARN\SubscriptionARN;
use SNSPush\ARN\TopicARN;
use SNSPush\Exceptions\InvalidTypeException;
use SNSPush\Exceptions\SNSPushException;
use SNSPush\Exceptions\UnsupportedPlatformException;

class SNSPush
{
    /**
     * Supported target types.
     */
    const TYPE_ENDPOINT = 1;
    const TYPE_TOPIC = 2;
    const TYPE_APPLICATION = 3;
    const TYPE_SUBSCRIPTION = 4;

    /**
     * List of endpoint targets supported by this package.
     *
     * @var array
     */
    protected static $types = [
        self::TYPE_ENDPOINT, self::TYPE_TOPIC, self::TYPE_APPLICATION, self::TYPE_SUBSCRIPTION
    ];

    /**
     * The AWS SNS Client.
     *
     * @var \Aws\Sns\SnsClient
     */
    protected $client;

    /**
     * The AWS SNS configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Instance of the ARN builder.
     *
     * @var \SNSPush\ARN\ARNBuilder
     */
    protected $arnBuilder;

    /**
     * SNSPush constructor.
     *
     * @param array $config
     *
     * @throws \SNSPush\Exceptions\SNSPushException
     * @throws \InvalidArgumentException
     */
    public function __construct(array $config = [])
    {
        // Set configuration data.
        $this->config = array_merge([
            'region'        => 'eu-west-1',
            'api_version'   => '2010-03-31',
            'scheme'        => 'https'
        ], $config);

        // Validate config.
        $this->validateConfig();

        // Create the ARN builder instance.
        $this->arnBuilder = new ARNBuilder($this->config);

        // Initialize the SNS Client.
        $this->client = $this->createClient();
    }

    /**
     * Validate config to ensure required parameters have been supplied.
     *
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    private function validateConfig()
    {
        if (empty($this->config['account_id'])) {
            throw new SNSPushException('Please supply your Amazon "account_id" in the config.');
        }

        if (empty($this->config['access_key'])) {
            throw new SNSPushException('Please supply your Amazon API "access_key" in the config.');
        }

        if (empty($this->config['secret_key'])) {
            throw new SNSPushException('Please supply your Amazon API "secret_key" in the config.');
        }

        if (empty($this->config['platform_applications'])) {
            throw new SNSPushException('Please supply your Amazon SNS "platform_applications" in the config.');
        }
    }

    /**
     * Initialize the AWS SNS Client.
     *
     * @return \Aws\Sns\SnsClient
     * @throws \InvalidArgumentException
     */
    private function createClient(): SnsClient
    {
        return new SnsClient([
            'region'        => $this->config['region'],
            'version'       => $this->config['api_version'],
            'scheme'        => $this->config['scheme'],
            'credentials'   => $this->getCredentials()
        ]);
    }

    /**
     * Get an instance of the Credentials.
     *
     * @return \Aws\Credentials\Credentials
     */
    private function getCredentials(): Credentials
    {
        return new Credentials($this->config['access_key'], $this->config['secret_key']);
    }

    /**
     * Check if provided endpoint type is supported and valid.
     *
     * @param $type
     * @return bool
     */
    protected static function isValidType($type): bool
    {
        return in_array($type, self::$types, true);
    }

    /**
     * Adds a device to an application endpoint in AWS SNS.
     *
     * @param $token
     * @param $platform
     *
     * @return mixed
     * @throws \SNSPush\Exceptions\InvalidArnException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function addDevice($token, $platform)
    {
        $arn = $this->arnBuilder->create(self::TYPE_APPLICATION, $platform);

        try {
            $result = $this->client->createPlatformEndpoint([
                $arn->getKey() => $arn->toString(),
                'Token' => $token
            ]);

            return isset($result['EndpointArn']) ? EndpointARN::parse($result['EndpointArn']) : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Subscribe a device endpoint to an ARN (topic subscription).
     *
     * @param       $endpointArn
     * @param       $arn
     * @param array $atts
     *
     * @return bool|mixed
     * @throws \SNSPush\Exceptions\InvalidArnException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function subscribeDeviceToTopic($endpointArn, $arn, array $atts = [])
    {
        if (!$arn instanceof TopicArn) {
            $arn = TopicArn::parse($arn);
        }

        try {
            $result = $this->client->subscribe([
                'Endpoint' => $endpointArn,
                'Protocol' => $atts['protocol'] ?? 'application',
                $arn->getKey() => $arn->toString()
            ]);

            return isset($result['SubscriptionArn']) ? SubscriptionARN::parse($result['SubscriptionArn']) : false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Remove a device endpoint from an ARN (unsubscribe topic).
     *
     * @param $arn
     *
     * @return \Aws\Result|bool
     * @throws \SNSPush\Exceptions\InvalidArnException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function removeDeviceFromTopic($arn)
    {
        if (!$arn instanceof SubscriptionARN) {
            $arn = SubscriptionARN::parse($arn);
        }

        try {
            $result = $this->client->unsubscribe([
                $arn->getKey() => $arn->toString()
            ]);

            return $result ?? false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Removes a device to an application endpoint in AWS SNS.
     *
     * @param $arn
     *
     * @return bool|array
     * @throws \SNSPush\Exceptions\InvalidArnException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function removeDevice($arn)
    {
        if (!$arn instanceof EndpointARN) {
            $arn = EndpointARN::parse($arn);
        }

        try {
            $result = $this->client->deleteEndpoint([
                $arn->getRemoveDeviceKey() => $arn->toString()
            ]);

            return $result ?? false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Gets list of all platform applications (ios, android, etc...).
     *
     * @return \Aws\Result|bool
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function getPlatformApplications()
    {
        try {
            $result = $this->client->listPlatformApplications();

            return $result ?? false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Send push notification to a topic endpoint.
     *
     * @param       $arn
     * @param       $message
     * @param array $options
     *
     * @return bool|mixed
     * @throws \SNSPush\Exceptions\InvalidTypeException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function sendTopicPushNotification($arn, $message, array $options = [])
    {
        return $this->sendPushNotification($arn, self::TYPE_TOPIC, $message, $options);
    }

    /**
     * Send push notification to a device endpoint.
     *
     * @param       $arn
     * @param       $message
     * @param array $options
     *
     * @return bool|mixed
     * @throws \SNSPush\Exceptions\InvalidTypeException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function sendEndpointPushNotification($arn, $message, array $options = [])
    {
        return $this->sendPushNotification($arn, self::TYPE_ENDPOINT, $message, $options);
    }

    /**
     * Send a Push Notification to an ARN.
     *
     * @param        $target
     * @param        $type
     * @param string $message
     * @param array  $atts
     *
     * @return bool|mixed
     * @throws \SNSPush\Exceptions\SNSPushException
     * @throws \InvalidArgumentException
     * @throws \SNSPush\Exceptions\InvalidTypeException
     */
    public function sendPushNotification($target, $type, $message = '', array $atts = [])
    {
        // Ensure the type is set to a topic or subscription.
        if (!self::isValidType($type)) {
            throw new InvalidTypeException('This type is invalid.');
        }

        // Set the ARN endpoint
        $arn = $this->arnBuilder->create($type, $target);

        return $this->sendPushNotificationWithArn($arn, $message, $atts);
    }

    /**
     * Send the push notification.
     *
     * @param \SNSPush\ARN\ARN $arn
     * @param                  $message
     * @param                  $atts
     *
     * @return \Aws\Result|bool
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function sendPushNotificationWithArn(ARN $arn, $message, $atts)
    {
        $data[$arn->getKey()] = $arn->toString();

        // Set the message
        if (!empty($message)) {
            // Message structure defaults to json but can also be set to string.
            $data['MessageStructure'] = $atts['message_structure'] ?? 'json';
            $data['Message'] = ($data['MessageStructure'] == 'json') ?
                $this->formatPushMessageAsJson($message, $atts['payload']) : $message;
        }

        try {
            $result = $this->client->publish($data);

            return $result ?? false;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Format push message as json in required format for various platforms.
     *
     * @param       $message
     * @param array $data
     *
     * @return string
     * @throws \SNSPush\Exceptions\UnsupportedPlatformException
     */
    public function formatPushMessageAsJson($message, array $data = []): string
    {
        $platformApplications = $this->config['platform_applications'];

        // Remove the application name from the platform endpoint.
        array_walk($platformApplications, function(&$value) {
            list($platform) = explode('/', $value);
            $value = $platform;
        });

        // Default message format.
        $messageArray = [
            'default' => $message
        ];

        // Loop through provided platforms to build the push message in the correct format.
        foreach ((array) $platformApplications as $key => $value) {
            $method = 'format' . ucfirst(mb_strtolower($key)) . 'MessageAsJson';

            if (!method_exists($this, $method)) {
                throw new UnsupportedPlatformException('This platform is not supported.');
            }

            $messageArray[$value] = $this->$method($message, $data);
        }

        return json_encode($messageArray);
    }

    /**
     * Format IOS message as JSON.
     *
     * @param $message
     * @param $data
     *
     * @return string
     */
    private function formatIosMessageAsJson($message, $data)
    {
        return json_encode(array_merge([
            'aps' => [
                'alert' => $message,
            ]],
                $data)
        );
    }

    /**
     * Format Android message as JSON.
     *
     * @param $message
     * @param $data
     *
     * @return string
     */
    private function formatAndroidMessageAsJson($message, $data)
    {
        return json_encode(array_merge([
            'data' => [
                'message' => $message,
            ]],
                $data)
        );
    }
}
