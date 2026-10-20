<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\RateLimitSubscriber;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;

#[TestDox('TEST RateLimitSubscriber')]
final class RateLimitSubscriberTest extends TestCase
{
    #[TestDox('it subscribes to the request event')]
    public function testItSubscribesToRequestEvent(): void
    {
        self::assertSame(
            [KernelEvents::REQUEST => ['onKernelRequest', 10]],
            RateLimitSubscriber::getSubscribedEvents(),
        );
    }

    #[TestDox('it ignores subrequests')]
    public function testItIgnoresSubrequests(): void
    {
        $event = $this->createRequestEvent(
            Request::create('/fizzbuzz'),
            HttpKernelInterface::SUB_REQUEST,
        );

        (new RateLimitSubscriber($this->createRateLimiterFactory()))
            ->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    #[TestDox('it ignores routes outside the API endpoints')]
    public function testItIgnoresOtherRoutes(): void
    {
        $event = $this->createRequestEvent(Request::create('/'));

        (new RateLimitSubscriber($this->createRateLimiterFactory()))
            ->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    #[TestDox('it allows an accepted request')]
    public function testItAllowsAcceptedRequest(): void
    {
        $request = Request::create('/fizzbuzz');
        $request->server->set('REMOTE_ADDR', '192.0.2.10');

        $event = $this->createRequestEvent($request);

        (new RateLimitSubscriber($this->createRateLimiterFactory()))
            ->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    #[TestDox('it rejects a request when the limit is exceeded')]
    public function testItRejectsLimitedRequest(): void
    {
        $factory = $this->createRateLimiterFactory();
        $clientIp = '192.0.2.10';

        $factory->create($clientIp)->consume(60);

        $request = Request::create('/stats');
        $request->server->set('REMOTE_ADDR', $clientIp);

        $event = $this->createRequestEvent($request);

        try {
            (new RateLimitSubscriber($factory))->onKernelRequest($event);
            self::fail('Expected a TooManyRequestsHttpException.');
        } catch (TooManyRequestsHttpException $exception) {
            self::assertSame(429, $exception->getStatusCode());
            self::assertSame('Too many requests', $exception->getMessage());
            self::assertArrayHasKey(
                'Retry-After',
                $exception->getHeaders(),
            );
        }
    }

    private function createRequestEvent(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): RequestEvent {
        return new RequestEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            $requestType,
        );
    }

    private function createRateLimiterFactory(): RateLimiterFactory
    {
        return new RateLimiterFactory(
            [
                'id' => 'test_api',
                'policy' => 'fixed_window',
                'limit' => 60,
                'interval' => '1 minute',
            ],
            new CacheStorage(new ArrayAdapter()),
        );
    }
}