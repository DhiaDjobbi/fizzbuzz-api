<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\SecurityHeadersSubscriber;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[TestDox('TEST SecurityHeadersSubscriber')]
final class SecurityHeadersSubscriberTest extends TestCase
{
    #[TestDox('it subscribes to the response event')]
    public function testItSubscribesToResponseEvent(): void
    {
        self::assertSame(
            [KernelEvents::RESPONSE => 'onKernelResponse'],
            SecurityHeadersSubscriber::getSubscribedEvents(),
        );
    }

    #[TestDox('it adds security headers to the response')]
    public function testItAddsSecurityHeaders(): void
    {
        $response = new Response();

        $event = new ResponseEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/fizzbuzz'),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        (new SecurityHeadersSubscriber())->onKernelResponse($event);

        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertSame('DENY', $response->headers->get('X-Frame-Options'));
        self::assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
        self::assertSame(
            'camera=(), microphone=(), geolocation=()',
            $response->headers->get('Permissions-Policy'),
        );
    }
}