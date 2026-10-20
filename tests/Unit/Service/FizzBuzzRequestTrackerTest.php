<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Dto\FizzBuzzRequest as FizzBuzzRequestDto;
use App\Entity\FizzBuzzRequest;
use App\Repository\FizzBuzzRequestRepository;
use App\Service\FizzBuzzRequestTracker;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('TEST FizzBuzzRequestTracker service')]
final class FizzBuzzRequestTrackerTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private FizzBuzzRequestRepository $repository;
    private FizzBuzzRequestTracker $tracker;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(FizzBuzzRequestRepository::class);

        $this->tracker = new FizzBuzzRequestTracker(
            $this->entityManager,
            $this->repository,
        );
    }

    #[TestDox('it persists a new request when no matching request exists')]
    public function testItPersistsNewRequest(): void
    {
        $request = new FizzBuzzRequestDto(3, 5, 15, 'fizz', 'buzz');

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'divisor1' => 3,
                'divisor2' => 5,
                'sequenceLimit' => 15,
                'str1' => 'fizz',
                'str2' => 'buzz',
            ])
            ->willReturn(null);

        $this->entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::isInstanceOf(FizzBuzzRequest::class));

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->tracker->track($request);
    }

    #[TestDox('it increments hits when a matching request already exists')]
    public function testItIncrementsHitsForExistingRequest(): void
    {
        $request = new FizzBuzzRequestDto(3, 5, 15, 'fizz', 'buzz');
        $existing = $this->createMock(FizzBuzzRequest::class);

        $this->repository
            ->expects(self::once())
            ->method('findOneBy')
            ->with([
                'divisor1' => 3,
                'divisor2' => 5,
                'sequenceLimit' => 15,
                'str1' => 'fizz',
                'str2' => 'buzz',
            ])
            ->willReturn($existing);

        $existing
            ->expects(self::once())
            ->method('incrementHits');

        $this->entityManager
            ->expects(self::never())
            ->method('persist');

        $this->entityManager
            ->expects(self::once())
            ->method('flush');

        $this->tracker->track($request);
    }
}
