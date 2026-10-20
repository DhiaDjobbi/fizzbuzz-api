<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\FizzBuzzRequestRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StatsController extends AbstractController
{
    #[Route('/stats', name: 'stats', methods: ['GET'], defaults: ['_strict_query_parameters' => []])]
    public function __invoke(FizzBuzzRequestRepository $repository): Response
    {
        $request = $repository->findMostUsed();

        if ($request === null) {
            throw $this->createNotFoundException('No FizzBuzz requests have been tracked yet.');
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
