<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Slack\Tests;

use PHPUnit\Framework\TestCase;
use Symfony\Bridge\PhpUnit\ExpectDeprecationTrait;
use Symfony\Component\Notifier\Bridge\Slack\Block\SlackDividerBlock;
use Symfony\Component\Notifier\Bridge\Slack\SlackOptions;
use Symfony\Component\Notifier\Notification\Notification;

/**
 * @author Oskar Stark <oskarstark@googlemail.com>
 */
final class SlackOptionsTest extends TestCase
{
    use ExpectDeprecationTrait;

    /**
     * @dataProvider toArrayProvider
     * @dataProvider toArraySimpleOptionsProvider
     */
    public function testToArray(array $options, ?array $expected = null)
    {
        $this->assertSame($expected ?? $options, (new SlackOptions($options))->toArray());
    }

    public function toArrayProvider(): iterable
    {
        yield 'empty is allowed' => [
            [],
            [],
        ];

        yield 'always unset recipient_id' => [
            ['recipient_id' => '42'],
            [],
        ];

        yield 'blocks containing 1 divider block' => [
            [
                'blocks' => [
                    $block = new SlackDividerBlock(),
                ],
            ],
            [
                'blocks' => [
                    $block,
                ],
            ],
        ];
    }

    public function toArraySimpleOptionsProvider(): iterable
    {
        yield [['as_user' => true]];
        yield [['icon_emoji' => 'foo']];
        yield [['icon_url' => 'https://symfony.com']];
        yield [['link_names' => true]];
        yield [['mrkdwn' => true]];
        yield [['parse' => 'bar']];
        yield [['unfurl_links' => true]];
        yield [['unfurl_media' => true]];
        yield [['username' => 'baz']];
        yield [['thread_ts' => '1503435956.000247']];
    }

    /**
     * @dataProvider getRecipientIdProvider
     */
    public function testGetRecipientId(?string $expected, SlackOptions $options)
    {
        $this->assertSame($expected, $options->getRecipientId());
    }

    public function getRecipientIdProvider(): iterable
    {
        yield [null, new SlackOptions()];
        yield [null, (new SlackOptions(['recipient_id' => null]))];
        yield ['foo', (new SlackOptions())->recipient('foo')];
        yield ['foo', (new SlackOptions(['recipient_id' => 'foo']))];
    }

    /**
     * @dataProvider setProvider
     *
     * @param mixed $value
     */
    public function testSet(string $method, string $optionsKey, $value)
    {
        $options = (new SlackOptions())->$method($value);

        $this->assertSame($value, $options->toArray()[$optionsKey]);
    }

    public function setProvider(): iterable
    {
        yield ['asUser', 'as_user', true];
        yield ['iconEmoji', 'icon_emoji', 'foo'];
        yield ['iconUrl', 'icon_url', 'https://symfony.com'];
        yield ['linkNames', 'link_names', true];
        yield ['mrkdwn', 'mrkdwn', true];
        yield ['parse', 'parse', 'bar'];
        yield ['unfurlLinks', 'unfurl_links', true];
        yield ['unfurlMedia', 'unfurl_media', true];
        yield ['username', 'username', 'baz'];
        yield ['threadTs', 'thread_ts', '1503435956.000247'];
    }

    public function testSetBlock()
    {
        $options = (new SlackOptions())->block(new SlackDividerBlock());

        $this->assertSame([['type' => 'divider']], $options->toArray()['blocks']);
    }

    /**
     * @group legacy
     */
    public function testChannelMethodRaisesDeprecation()
    {
        $this->expectDeprecation('Since symfony/slack-notifier 5.1: The "Symfony\Component\Notifier\Bridge\Slack\SlackOptions::channel()" method is deprecated, use "recipient()" instead.');

        (new SlackOptions())->channel('channel');
    }

    /**
     * @dataProvider fromNotificationProvider
     */
    public function testFromNotification(array $expected, Notification $notification)
    {
        $options = SlackOptions::fromNotification($notification);

        $this->assertSame($expected, $options->toArray());
    }

    public function fromNotificationProvider(): iterable
    {
        $subject = 'Hi!';
        $emoji = '🌧️';
        $content = 'Content here ...';

        yield 'without content + without exception' => [
            [
                'icon_emoji' => $emoji,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $subject,
                        ],
                    ],
                ],
            ],
            (new Notification($subject))->emoji($emoji),
        ];

        yield 'with content + without exception' => [
            [
                'icon_emoji' => $emoji,
                'blocks' => [
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $subject,
                        ],
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $content,
                        ],
                    ],
                ],
            ],
            (new Notification($subject))->emoji($emoji)->content($content),
        ];
    }
}
