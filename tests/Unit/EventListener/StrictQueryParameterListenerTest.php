<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\StrictQueryParameterListener;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;

#[TestDox('TEST StrictQueryParameterListener')]
final class StrictQueryParameterListenerTest extends TestCase
{
    public function testRejectsUnknownQueryParameters(): void
    {
        $request = Request::create('/fizzbuzz?int1=3&ping=pong');
        $request->attributes->set('_strict_query_parameters', ['int1']);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $listener = new StrictQueryParameterListener();
        $listener->__invoke($event);

        $response = $event->getResponse();

        self::assertNotNull($response);
        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            ['message' => 'Unexpected query parameter(s): ping.'],
            json_decode((string) $response->getContent(), true),
        );
    }

    public function testAllowsValidQueryParameters(): void
    {
        $request = Request::create('/fizzbuzz?int1=3');
        $request->attributes->set('_strict_query_parameters', ['int1']);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $listener = new StrictQueryParameterListener();
        $listener->__invoke($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresSubRequests(): void
    {
        $request = Request::create('/fizzbuzz?ping=pong');
        $request->attributes->set('_strict_query_parameters', ['int1']);

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            $request,
            HttpKernelInterface::SUB_REQUEST,
        );

        $listener = new StrictQueryParameterListener();
        $listener->__invoke($event);

        self::assertNull($event->getResponse());
    }

    public function testIgnoresRequestsWithoutAllowedParameters(): void
    {
        $request = Request::create('/fizzbuzz?ping=pong');

        $event = new RequestEvent(
            $this->createStub(KernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );

        $listener = new StrictQueryParameterListener();
        $listener->__invoke($event);

        self::assertNull($event->getResponse());
    }
}
