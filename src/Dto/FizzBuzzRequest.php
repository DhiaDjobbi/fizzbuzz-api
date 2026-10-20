<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class FizzBuzzRequest
{
    public function __construct(
        #[Assert\Positive]
        public int $int1,
        #[Assert\Positive]
        public int $int2,
        #[Assert\Range(min: 1, max: 10000)]
        public int $limit,
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $str1,
        #[Assert\NotBlank]
        #[Assert\Length(max: 50)]
        public string $str2,
    ) {
    }
}
