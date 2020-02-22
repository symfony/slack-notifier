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

use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;
use Symfony\Component\Notifier\Transport\Dsn;
use Symfony\Component\Notifier\Transport\TransportInterface;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @experimental in 5.0
 */
final class SlackTransportFactory extends AbstractTransportFactory
{
    /**
     * @return SlackTransport
     */
    public function create(Dsn $dsn): TransportInterface
    {
        $scheme = $dsn->getScheme();
        $channel = $dsn->getOption('channel');
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();

        if ('slack' === $scheme) {
            $accessToken = $this->getUser($dsn);
            return (new SlackTransport($accessToken, $channel, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        if ('slack+webhook' === $scheme) {
            $webhookPath = ltrim($dsn->getPath(), '/');
            $username = $dsn->getOption('username');

            return (new SlackWebhookTransport($webhookPath, $channel, $username, $this->client, $this->dispatcher))->setHost($host)->setPort($port);
        }

        throw new UnsupportedSchemeException($dsn, 'slack', $this->getSupportedSchemes());
    }

    protected function getSupportedSchemes(): array
    {
        return ['slack', 'slack+webhook'];
    }
}
