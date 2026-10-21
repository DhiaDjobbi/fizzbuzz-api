<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\FizzBuzzRequest;
use App\Service\FizzBuzzGenerator;
use App\Service\FizzBuzzRequestTracker;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class FizzBuzzController extends AbstractController
{
    private const QUERY_PARAMETERS = ['int1', 'int2', 'limit', 'str1', 'str2'];

    #[OA\Response(
        response: 200,
        description: 'Generated FizzBuzz sequence',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(
                oneOf: [
                    new OA\Schema(type: 'integer'),
                    new OA\Schema(type: 'string'),
                ],
            ),
            example: [1, 2, 'fizz', 4, 'buzz'],
        ),
    )]
    #[OA\Response(ref: '#/components/responses/BadRequest', response: 400)]
    #[OA\Response(ref: '#/components/responses/TooManyRequests', response: 429)]
    #[Route(
        '/fizzbuzz',
        name: 'fizzbuzz',
        methods: ['GET'],
        defaults: [
            '_strict_query_parameters' => self::QUERY_PARAMETERS,
        ],
    )]
    public function __invoke(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        FizzBuzzRequest $request,
        FizzBuzzGenerator $generator,
        FizzBuzzRequestTracker $tracker,
    ): Response {
        $result = $generator->generate(
            $request->int1,
            $request->int2,
            $request->limit,
            $request->str1,
            $request->str2,
        );

        $tracker->track($request);

        return $this->json($result);
    }
}
