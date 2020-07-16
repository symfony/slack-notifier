<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Notifier\Bridge\Slack\Notification;

use Symfony\Component\Notifier\Notification\Notification;

/**
 * @author Ihor Melnichenko <melnik.od92@gmail.com>
 *
 * @experimental in 5.1
 */
class ImageNotification extends Notification
{
    private $imageUrl = '';

    /**
     * @return $this
     */
    public function imageUrl(string $imageUrl): self
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }
}
