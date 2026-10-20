<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\FizzBuzzRequest;
use App\Service\FizzBuzzGenerator;
use App\Service\FizzBuzzRequestTracker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class FizzBuzzController extends AbstractController
{
    private const QUERY_PARAMETERS = ['int1', 'int2', 'limit', 'str1', 'str2'];

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
