<?php

namespace SNSPush;

use Aws\ApiGateway\Exception\ApiGatewayException;
use Aws\Credentials\Credentials;
use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;
use SNSPush\Exceptions\SNSPushException;

class SNSPush
{
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
     * List of platforms supported by AWS SNS.
     *
     * @var array
     */
    protected static $supportedPlatforms = [
        'ADM', 'APNS', 'APNS_SANDBOX', 'GCM'
    ];

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
        $this->validate();

        // Initialize the SNS Client.
        $this->client = $this->initializeClient();
    }

    /**
     * Validate config to ensure required parameters have been supplied.
     *
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    private function validate()
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
    private function initializeClient()
    {
        $client = new SnsClient([
            'region'        => $this->config['region'],
            'version'       => $this->config['api_version'],
            'scheme'        => $this->config['scheme'],
            'credentials'   => $this->getCredentials()
        ]);

        return $client;
    }

    /**
     * Get an instance of the Credentials.
     *
     * @return \Aws\Credentials\Credentials
     */
    private function getCredentials()
    {
        return new Credentials($this->config['access_key'], $this->config['secret_key']);
    }

    /**
     * Get the Application Platform ARN.
     *
     * @param $platform
     *
     * @return string
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function getPlatformApplicationArn($platform)
    {
        $platformApplications = $this->config['platform_applications'];

        if (array_key_exists($platform, $platformApplications)) {
            return 'arn:aws:sns:' . $this->config['region'] . ':' . $this->config['account_id'] .  ':app/' .
                $platformApplications[$platform];
        }

        throw new SNSPushException("Invalid platform specified({$platform})");
    }

    /**
     * Build the ARN for a given topic.
     *
     * @param $topic
     *
     * @return string
     */
    public function getTopicArn($topic)
    {
        return 'arn:aws:sns:' . $this->config['region'] . ':' . $this->config['account_id'] . ':' . $topic;
    }

    /**
     * Adds a device to an application endpoint in AWS SNS.
     *
     * @param $token
     * @param $platform
     *
     * @return mixed
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function addDevice($token, $platform)
    {
        try {
            $result = $this->client->createPlatformEndpoint([
                'PlatformApplicationArn' => $this->getPlatformApplicationArn($platform),
                'Token' => $token
            ]);

            return (isset($result['EndpointArn']) ? $result['EndpointArn'] : false);
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
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function removeDevice($arn)
    {
        try {
            $result = $this->client->deleteEndpoint([
                'EndpointArn' => $arn
            ]);

            return $result;
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Get all endpoints for devices in a supported push notification service such as APNS or GCM.
     *
     * @param $platform
     *
     * @return \Aws\Result|bool
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function getDevices($platform)
    {
        $platformApplicationArn = $this->getPlatformApplicationArn($platform);

        try {
            $result = $this->client->listEndpointsByPlatformApplication([
                'PlatformApplicationArn' => $platformApplicationArn
            ]);

            return ($result !== null ? $result : false);
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

            return ($result !== null ? $result : false);
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Send a Push Notification to an ARN/subscription.
     *
     * @param        $arn
     * @param string $type
     * @param string $message
     * @param array  $atts
     *
     * @return bool|mixed
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function sendPushNotification($arn, $type = 'topic', $message = '', array $atts = [])
    {
        // Ensure the target is valid.
        $this->validateArn($arn);

        // Ensure the type is set to a topic or subscription.
        if (!in_array($type, ['topic', 'subscription'], true)) {
            throw new SNSPushException('Type must be either a "topic" or "subscription"');
        }

        // Set the ARN endpoint
        $data[$type == 'topic' ? 'TopicArn' : 'TargetArn'] = $arn;

        // Set the message
        if (!empty($message)) {
            $data = [
                'Message' => $message,
                'MessageStructure' => isset($atts['message_structure']) ? $atts['message_structure'] : 'string',
            ];
        }

        try {
            $result = $this->client->publish($data);

            return (isset($result['MessageId']) ? $result['MessageId'] : false);
        } catch (SnsException $e) {
            throw new SNSPushException($e->getMessage());
        } catch (ApiGatewayException $e) {
            throw new SNSPushException('There was an unknown problem with the AWS SNS API. Code: ' . $e->getCode());
        }
    }

    /**
     * Validate a given ARN.
     *
     * @param        $arn
     **
     * @return bool
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function validateArn($arn)
    {
        // Check if the ARN string matches the format AWS expects.
        if (!preg_match('/^arn:aws:sns:([a-z]{2}-[a-z]+(?:-\d)?):(\d+):([a-z\d-_\/]+)$/i', $arn, $matches)) {
            throw new SNSPushException('The supplied ARN is not valid.');
        }

        // Validate ARN to ensure region matches the apps region.
        if ($matches[1] !== $this->config['region']) {
            throw new SNSPushException('The "region" in the ARN does not match the "region" in the config.');
        }

        // Validate ARN to ensure the account_id matches the apps account_id.
        if ($matches[2] !== $this->config['account_id']) {
            throw new SNSPushException('The "account_id" in the ARN does not match the "account_id" in the config.');
        }
    }

    /**
     * Validate a given application name.
     *
     * @param $applicationName
     * @param $platform
     *
     * @throws \SNSPush\Exceptions\SNSPushException
     */
    public function validateApplication($applicationName, $platform)
    {
        // Check if the application name is valid.
        if (strlen($applicationName) > 256 || !preg_match('/^([a-zA-Z-_\d]*)&/', $applicationName)) {
            throw new SNSPushException('The application name is invalid. Up to 256 alphanumeric characters, hyphens ' .
                '(-), and underscores (_) are allowed.');
        }

        // Make sure the provided platform is supported.
        if (!in_array($platform, self::$supportedPlatforms, true)) {
            throw new SNSPushException('The provided platform is invalid or not supported.');
        }
    }
}