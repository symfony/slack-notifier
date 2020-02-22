<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Slack;

use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\TransportException;
use Symfony\Component\Notifier\Message\ChatMessage;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @internal
 *
 * @experimental in 5.0
 */
final class SlackWebhookTransport extends AbstractTransport
{
    protected const HOST = 'hooks.slack.com';

    protected $client;
    private $webhookPath;
    private $channel;
    private $username;

    public function __construct(
        string $webhookPath,
        string $channel,
        string $username,
        HttpClientInterface $client = null,
        EventDispatcherInterface $dispatcher = null
    ) {
        $this->client = $client;
        $this->webhookPath = $webhookPath;
        $this->channel = $channel;
        $this->username = $username;

        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf(
            '%s://%s/%s?channel=%s&username=%s&',
            SlackWebhookTransportFactory::SCHEME,
            $this->getEndpoint(),
            $this->webhookPath,
            $this->channel,
            $this->username
        );
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof ChatMessage && (null === $message->getOptions() || $message->getOptions() instanceof SlackOptions);
    }

    /**
     * Sending messages using Incoming Webhooks
     * @see https://api.slack.com/messaging/webhooks
     */
    protected function doSend(MessageInterface $message): void
    {
        if (!$message instanceof ChatMessage) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" (instance of "%s" given).', __CLASS__, ChatMessage::class, \get_class($message)));
        }
        if ($message->getOptions() && !$message->getOptions() instanceof SlackOptions) {
            throw new LogicException(sprintf('The "%s" transport only supports instances of "%s" for options.', __CLASS__, SlackOptions::class));
        }

        if (!($opts = $message->getOptions()) && $notification = $message->getNotification()) {
            $opts = SlackOptions::fromNotification($notification);
        }

        $options = $opts ? $opts->toArray() : [];
        if (!isset($options['channel'])) {
            $options['channel'] = $message->getRecipientId() ?: $this->channel;
        }
        $options['username'] = $options['username'] ?? $this->username;
        $options['text'] = $message->getSubject();

        $response = $this->client->request(
            'POST',
            sprintf(
                '%s://%s/%s',
                'https',
                $this->getEndpoint(),
                $this->webhookPath
            ),
            ['json' => array_filter($options)]
        );

        if (200 !== $response->getStatusCode()) {
            throw new TransportException(sprintf('Unable to post the Slack message: %s.', $response->getContent(false)), $response);
        }
    }
}
