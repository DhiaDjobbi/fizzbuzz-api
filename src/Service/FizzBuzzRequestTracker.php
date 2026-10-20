<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\FizzBuzzRequest;
use App\Dto\FizzBuzzRequest as FizzBuzzRequestDto;
use App\Repository\FizzBuzzRequestRepository;
use Doctrine\ORM\EntityManagerInterface;

class FizzBuzzRequestTracker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FizzBuzzRequestRepository $repository,
    ) {
    }

    public function track(FizzBuzzRequestDto $request): void
    {
        $existing = $this->repository->findOneBy([
            'divisor1' => $request->int1,
            'divisor2' => $request->int2,
            'sequenceLimit' => $request->limit,
            'str1' => $request->str1,
            'str2' => $request->str2,
        ]);

        if ($existing !== null) {
            $existing->incrementHits();
        } else {
            $existing = (new FizzBuzzRequest())
                ->setDivisor1($request->int1)
                ->setDivisor2($request->int2)
                ->setSequenceLimit($request->limit)
                ->setStr1($request->str1)
                ->setStr2($request->str2);

            $this->entityManager->persist($existing);
        }

        $this->entityManager->flush();
    }
}
