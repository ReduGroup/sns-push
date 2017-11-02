<?php

use PHPUnit\Framework\TestCase;
use SNSPush\Messages\Message;
use SNSPush\Messages\MessageInterface;

class MessageTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function setUp()
    {
    }

    /**
     * @dataProvider messageProvider
     */
    public function testMessageFormat(MessageInterface $message, array $expectedIos, array $expectedAndroid)
    {
        $this->assertEquals($expectedIos, $message->getIosData());
        // write test for inbox mode
        $this->assertEquals($expectedAndroid, $message->getAndroidData());
    }


    public function messageProvider()
    {
        return [
            [
                (new Message())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setBadge(5)
                    ->setIosSound('Diamond.caf')
                    ->setAndroidSound('Diamond')
                    ->setContentAvailable(1)
                    ,
                [
                    'aps' => [
                        'alert' => [
                            'title' => 'Message Title',
                            'body' => 'Message body',
                        ],
                        'badge' => 5,
                        'sound' => 'Diamond.caf',
                        'content-available' => true,
                    ]
                ],
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'badge' => 5,
                        'sound' => 'Diamond',
                        'content-available' => true,
                    ]
                ],
            ],
            [
                (new Message())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setUseAndroidInboxMode()
                    ,
                [
                    'aps' => [
                        'alert' => [
                            'title' => 'Message Title',
                            'body' => 'Message body',
                        ],
                    ]
                ],
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'style' => 'inbox',
                        'summaryText' => '%n% messages',
                    ]
                ],
            ],
            [
                (new Message())
                    ->setTitle('Message Title')
                    ->setBody('Message body')
                    ->setUseAndroidInboxMode()
                    ->setAndroidInboxModeGroupMessage('You have %n% messages. Please pay attention.')
                    ,
                [
                    'aps' => [
                        'alert' => [
                            'title' => 'Message Title',
                            'body' => 'Message body',
                        ],
                    ]
                ],
                [
                    'data' => [
                        'title' => 'Message Title',
                        'message' => 'Message body',
                        'style' => 'inbox',
                        'summaryText' => 'You have %n% messages. Please pay attention.',
                    ]
                ],
            ],
            [
                (new Message())
                    ->setBadge(5)
                    ->setContentAvailable(1)
                    ,
                [
                    'aps' => [
                        'badge' => 5,
                        'content-available' => true,
                    ]
                ],
                [
                    'data' => [
                        'badge' => 5,
                        'content-available' => true,
                    ]
                ],
            ],
            [
                (new Message())
                    ->setBadge(0)
                    ->setContentAvailable(1)
                    ,
                [
                    'aps' => [
                        'badge' => 0,
                        'content-available' => true,
                    ]
                ],
                [
                    'data' => [
                        'badge' => 0,
                        'content-available' => true,
                    ]
                ],
            ],
            [
                (new Message())
                    ->setBadge(5)
                    ->setContentAvailable(1)
                    ->setPayload([
                        'additional-data' => 123
                    ])
                    ,
                [
                    'aps' => [
                        'badge' => 5,
                        'content-available' => true,
                    ],
                    'additional-data' => 123,
                ],
                [
                    'data' => [
                        'badge' => 5,
                        'content-available' => true,
                        'additional-data' => 123,
                    ]
                ],
            ],
        ];
    }
}
