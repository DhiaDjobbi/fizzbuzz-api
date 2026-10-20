<?php

declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
final class StrictQueryParameterListener
{
    public function __invoke(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $allowed = $request->attributes->get('_strict_query_parameters');

        if (!is_array($allowed)) {
            return;
        }

        $unknown = array_diff(
            array_keys($request->query->all()),
            $allowed,
        );

        if ([] === $unknown) {
            return;
        }

        $event->setResponse(new JsonResponse([
            'message' => sprintf(
                'Unexpected query parameter(s): %s.',
                implode(', ', $unknown),
            ),
        ], Response::HTTP_BAD_REQUEST));
    }
}
