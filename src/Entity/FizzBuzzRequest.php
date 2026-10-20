<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\FizzBuzzRequestRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FizzBuzzRequestRepository::class)]
#[ORM\Table(name: 'fizzbuzz_request')]
#[ORM\UniqueConstraint(name: 'uniq_fizzbuzz_request', columns: ['divisor1', 'divisor2', 'sequence_limit', 'str1', 'str2'])]
class FizzBuzzRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $divisor1;

    #[ORM\Column]
    private int $divisor2;

    #[ORM\Column(name: 'sequence_limit')]
    private int $sequenceLimit;

    #[ORM\Column(length: 50)]
    private string $str1;

    #[ORM\Column(length: 50)]
    private string $str2;

    #[ORM\Column]
    private int $hits = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDivisor1(): int
    {
        return $this->divisor1;
    }

    public function setDivisor1(int $divisor1): static
    {
        $this->divisor1 = $divisor1;

        return $this;
    }

    public function getDivisor2(): int
    {
        return $this->divisor2;
    }

    public function setDivisor2(int $divisor2): static
    {
        $this->divisor2 = $divisor2;

        return $this;
    }

    public function getSequenceLimit(): int
    {
        return $this->sequenceLimit;
    }

    public function setSequenceLimit(int $sequenceLimit): static
    {
        $this->sequenceLimit = $sequenceLimit;

        return $this;
    }

    public function getStr1(): string
    {
        return $this->str1;
    }

    public function setStr1(string $str1): static
    {
        $this->str1 = $str1;

        return $this;
    }

    public function getStr2(): string
    {
        return $this->str2;
    }

    public function setStr2(string $str2): static
    {
        $this->str2 = $str2;

        return $this;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function incrementHits(): void
    {
        ++$this->hits;
    }
}
