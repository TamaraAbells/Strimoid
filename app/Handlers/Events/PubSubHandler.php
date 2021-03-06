<?php

namespace Strimoid\Handlers\Events;

use Illuminate\Events\Dispatcher;
use Pusher\Laravel\Facades\Pusher;
use Strimoid\Models\Entry;
use Strimoid\Models\EntryReply;
use Strimoid\Models\Notification;

class PubSubHandler
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen('eloquent.created: ' . Entry::class, self::class . '@onNewEntry');
        $events->listen('eloquent.created: ' . EntryReply::class, self::class . '@onNewEntryReply');
        $events->listen('eloquent.created: ' . Notification::class, self::class . '@onNewNotification');
    }

    public function onNewEntry(Entry $entry): void
    {
        $arrayEntry = $entry->toArray();
        $additionalData = [
            'hashId' => $entry->hashId(),
            'avatarPath' => $entry->user->getAvatarPath(),
            'entryUrl' => $entry->getURL(),
        ];

        Pusher::trigger('entries', 'new-entry', array_merge($arrayEntry, $additionalData));
    }

    public function onNewEntryReply(EntryReply $reply): void
    {
        $arrayEntry = $reply->toArray();
        $additionalData = [
            'hashId' => $reply->hashId(),
            'avatarPath' => $reply->user->getAvatarPath(),
            'entryUrl' => $reply->getURL(),
        ];

        Pusher::trigger('entry.' . $reply->parent->hashId(), 'new-reply', array_merge($arrayEntry, $additionalData));
    }

    public function onNewNotification(Notification $notification): void
    {
        foreach ($notification->targets as $target) {
            $channelName = 'privateU' . $target->id;
            $notificationData = [
                'id' => $notification->hashId(),
                'type' => $notification->getTypeDescription(),
                'title' => $notification->title,
                'img' => $notification->getThumbnailPath(),
                'url' => $notification->getURL(true),
            ];

            Pusher::trigger($channelName, 'new-notification', $notificationData);
        }
    }
}
