<?php
declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\EventListener\ValidationErrorListener;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[TestDox('TEST ValidationErrorListener')]
final class ValidationErrorListenerTest extends TestCase
{
    #[TestDox('it ignores exceptions without validation errors')]
    public function testItIgnoresUnrelatedExceptions(): void
    {
        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/fizzbuzz'),
            HttpKernelInterface::MAIN_REQUEST,
            new \RuntimeException('Unexpected error.'),
        );

        (new ValidationErrorListener())($event);

        self::assertNull($event->getResponse());
    }

    #[TestDox('it ignores HTTP exceptions without validation errors')]
    public function testItIgnoresHttpExceptionsWithoutValidationErrors(): void
    {
        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/fizzbuzz'),
            HttpKernelInterface::MAIN_REQUEST,
            new BadRequestHttpException('Bad request.'),
        );

        (new ValidationErrorListener())($event);

        self::assertNull($event->getResponse());
    }

    #[TestDox('it returns the appropriate validation error response')]
    #[DataProvider('validationErrors')]
    public function testItReturnsValidationErrorResponse(
        string $query,
        string $parameter,
        string $message,
        string $expectedMessage,
    ): void {
        $request = Request::create('/fizzbuzz' . $query);

        $violation = new ConstraintViolation(
            $message,
            $message,
            [],
            null,
            $parameter,
            null,
        );

        $validationException = new ValidationFailedException(
            null,
            new ConstraintViolationList([$violation]),
        );

        $exception = new BadRequestHttpException(
            'Bad request.',
            $validationException,
        );

        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception,
        );

        (new ValidationErrorListener())($event);

        self::assertSame(400, $event->getResponse()->getStatusCode());
        self::assertSame(
            ['message' => $expectedMessage],
            json_decode($event->getResponse()->getContent(), true),
        );
    }

    public static function validationErrors(): array
    {
        return [
            'invalid parameter' => [
                '?int1=0',
                'int1',
                'This value is invalid.',
                'The int1 parameter is invalid. This value is invalid.',
            ],
            'missing parameter' => [
                '?int1=3',
                'str2',
                'This value should not be blank.',
                'The str2 parameter is required.',
            ],
        ];
    }
}
