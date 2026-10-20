<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ValidationErrorListener
{
    #[AsEventListener(priority: -10)]
    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $validationError = $exception->getPrevious();

        if (!$exception instanceof HttpExceptionInterface || !$validationError instanceof ValidationFailedException) {
            return;
        }

        $violation = $validationError->getViolations()->get(0);
        $parameter = $violation->getPropertyPath();

        if ($event->getRequest()->query->has($parameter)) {
            $message = sprintf('The %s parameter is invalid. %s', $parameter, $violation->getMessage());
        } else {
            $message = sprintf('The %s parameter is required.', $parameter);
        }

        $event->setResponse(new JsonResponse(['message' => $message], $exception->getStatusCode()));
    }
}
