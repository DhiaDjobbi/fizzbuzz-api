<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\HttpErrorListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[TestDox('TEST HttpErrorListener')]
final class HttpErrorListenerTest extends TestCase
{
    #[TestDox('it returns the appropriate HTTP error response')]
    #[DataProvider('httpErrors')]
    public function testItReturnsHttpErrorResponse(
        \Throwable $exception,
        int $expectedStatusCode,
        string $expectedMessage,
    ): void {
        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/stats'),
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );

        (new HttpErrorListener())($event);

        self::assertSame($expectedStatusCode, $event->getResponse()->getStatusCode());
        self::assertSame(
            ['message' => $expectedMessage],
            json_decode($event->getResponse()->getContent(), true),
        );
    }

    public static function httpErrors(): array
    {
        return [
            'HTTP exception' => [
                new NotFoundHttpException('No FizzBuzz requests have been tracked yet.'),
                404,
                'No FizzBuzz requests have been tracked yet.',
            ],
            'unexpected exception' => [
                new \RuntimeException('Sensitive internal details.'),
                500,
                'An unexpected error occurred.',
            ],
        ];
    }
}