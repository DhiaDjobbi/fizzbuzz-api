<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(service: 'limiter.api')]
        private readonly RateLimiterFactory $rateLimiter,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 10],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!in_array($request->getPathInfo(), ['/fizzbuzz', '/stats'], true)) {
            return;
        }

        $clientIp = $request->getClientIp() ?? 'unknown';
        $limit = $this->rateLimiter->create($clientIp)->consume();

        if ($limit->isAccepted()) {
            return;
        }

        $retryAfter = max(
            0,
            $limit->getRetryAfter()->getTimestamp() - time(),
        );

        throw new TooManyRequestsHttpException(
            (string) $retryAfter,
            'Too many requests',
        );
    }
}
