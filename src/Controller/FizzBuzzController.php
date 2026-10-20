<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\FizzBuzzRequest;
use App\Service\FizzBuzzGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

class FizzBuzzController extends AbstractController
{
    private const QUERY_PARAMETERS = ['int1', 'int2', 'limit', 'str1', 'str2'];

    #[Route(
        '/fizzbuzz',
        methods: ['GET'],
        format: 'json',
        defaults: ['_strict_query_parameters' => self::QUERY_PARAMETERS],
    )]
    public function index(
        #[MapQueryString(validationFailedStatusCode: Response::HTTP_BAD_REQUEST)]
        FizzBuzzRequest $request,
        FizzBuzzGenerator $generator,
    ): JsonResponse {
        $result = $generator->generate(
            $request->int1,
            $request->int2,
            $request->limit,
            $request->str1,
            $request->str2,
        );

        return $this->json($result);
    }
}
