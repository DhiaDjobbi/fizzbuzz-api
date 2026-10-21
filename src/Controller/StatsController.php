<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\FizzBuzzRequestRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[OA\Response(
        response: 200,
        description: 'Most frequently used parameter combination',
        content: new OA\JsonContent(
            type: 'object',
            required: ['int1', 'int2', 'limit', 'str1', 'str2', 'hits'],
            properties: [
                new OA\Property(property: 'int1', type: 'integer', example: 3),
                new OA\Property(property: 'int2', type: 'integer', example: 5),
                new OA\Property(property: 'limit', type: 'integer', example: 15),
                new OA\Property(property: 'str1', type: 'string', example: 'fizz'),
                new OA\Property(property: 'str2', type: 'string', example: 'buzz'),
                new OA\Property(property: 'hits', type: 'integer', example: 12),
            ],
        ),
    )]
    #[OA\Response(ref: '#/components/responses/NotFound', response: 404)]
    #[OA\Response(ref: '#/components/responses/TooManyRequests', response: 429)]
    #[Route(
        '/stats',
        name: 'stats',
        methods: ['GET'],
        defaults: ['_strict_query_parameters' => []],
    )]
    public function __invoke(
        FizzBuzzRequestRepository $repository,
    ): Response {
        $request = $repository->findMostUsed();

        if ($request === null) {
            throw $this->createNotFoundException(
                'No FizzBuzz requests have been tracked yet.',
            );
        }

        return $this->json([
            'int1' => $request->getDivisor1(),
            'int2' => $request->getDivisor2(),
            'limit' => $request->getSequenceLimit(),
            'str1' => $request->getStr1(),
            'str2' => $request->getStr2(),
            'hits' => $request->getHits(),
        ]);
    }
}
