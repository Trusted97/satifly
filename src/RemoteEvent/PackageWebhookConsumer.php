<?php

namespace App\RemoteEvent;

use App\Event\BuildEvent;
use App\Service\RepositoryManager;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\RemoteEvent\Attribute\AsRemoteEventConsumer;
use Symfony\Component\RemoteEvent\Consumer\ConsumerInterface;
use Symfony\Component\RemoteEvent\RemoteEvent;

#[AsRemoteEventConsumer('package')]
final class PackageWebhookConsumer implements ConsumerInterface
{
    public function __construct(
        #[Target('package.lock.factory')] public LockFactory $lockFactory,
        public RepositoryManager $repositoryManager,
        public EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function consume(RemoteEvent $event): void
    {
        $lock = $this->lockFactory->createLock('package');
        $lock->acquire(true);

        try {
            $payload    = $event->getPayload();
            $repository = $this->repositoryManager->findByUrl($payload['repository']['url']);
            $buildEvent = new BuildEvent($repository);
            $this->eventDispatcher->dispatch($buildEvent, BuildEvent::class);
        } finally {
            $lock->release();
        }
    }
}
