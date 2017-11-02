<?php

use Aws\Result;
use Aws\Sns\SnsClient;
use PHPUnit\Framework\TestCase;
use SNSPush\SNSPush;
use SNSPush\Messages\Message;
use SNSPush\Messages\MessageInterface;

use Tests\Config;

class DeviceTest extends TestCase
{

    /**
     * @var SnsClient;
     */
    protected $client;

    /**
     * @var SNSPush;
     */
    protected $sns;

    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
        $config = Config::data();

        $this->client = Mockery::mock(SnsClient::class);
        $this->sns = new SNSPush($config, $this->client);
    }

    /**
     * @dataProvider messageProvider
     */
    public function testSendMessageToEndpoint(MessageInterface $message, string $endpoint, array $expectedPayload)
    {
        $messageId = "c03c7f56-c583-55f4-b521-2d24537a3337";
        $this->client->expects()->publish($expectedPayload)->andReturns(new Result(['MessageId' => $messageId]));

        $result = $this->sns->sendPushNotificationToEndpoint($endpoint, $message);

        $this->assertEquals($messageId, $result->get('MessageId'));
    }

    /**
     * @dataProvider topicMessageProvider
     */
    public function testSendMessageToTopic(MessageInterface $message, string $endpoint, array $expectedPayload)
    {
        $messageId = "c03c7f56-c583-55f4-b521-2d24537a4437";
        $this->client->expects()->publish($expectedPayload)->andReturns(new Result(['MessageId' => $messageId]));

        $result = $this->sns->sendPushNotificationToTopic($endpoint, $message);

        $this->assertEquals($messageId, $result->get('MessageId'));
    }


    public function messageProvider()
    {
        $iosEndpoint = 'arn:aws:sns:eu-west-1:01234567890:endpoint/APNS/application-ios/a5825a90-d4fc-3116-8c9f-821d81f745a0';

        return [
            [
                $this->getMessage(),
                $iosEndpoint,
                [
                      'TargetArn' => $iosEndpoint,
                      'Message' => '{"default":"Message body","APNS":"{\"aps\":{\"alert\":{\"title\":\"Message Title\",\"body\":\"Message body\"},\"sound\":\"Sound.caf\",\"badge\":5}}","GCM":"{\"data\":{\"title\":\"Message Title\",\"message\":\"Message body\",\"sound\":\"Sound\",\"badge\":5}}"}',
                      'MessageStructure' => 'json',
                ],
            ],
        ];
    }

    public function topicMessageProvider()
    {
        $topicEndpoint = 'arn:aws:sns:eu-west-1:01234567890:test-topic';

        return [
            [
                $this->getMessage(),
                $topicEndpoint,
                [
                      'TopicArn' => $topicEndpoint,
                      'Message' => '{"default":"Message body","APNS":"{\"aps\":{\"alert\":{\"title\":\"Message Title\",\"body\":\"Message body\"},\"sound\":\"Sound.caf\",\"badge\":5}}","GCM":"{\"data\":{\"title\":\"Message Title\",\"message\":\"Message body\",\"sound\":\"Sound\",\"badge\":5}}"}',
                      'MessageStructure' => 'json',
                ],
            ],
        ];
    }

    public function getMessage()
    {
        return (new Message())
            ->setTitle('Message Title')
            ->setBody('Message body')
            ->setBadge(5)
            ->setIosSound('Sound.caf')
            ->setAndroidSound('Sound');
    }
}
