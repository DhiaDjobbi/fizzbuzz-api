<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Service\FizzBuzzGenerator;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\TestCase;

#[TestDox('TEST FizzBuzzGenerator service')]
final class FizzBuzzGeneratorTest extends TestCase
{
    private FizzBuzzGenerator $generator;

    protected function setUp(): void
    {
        $this->generator = new FizzBuzzGenerator();
    }

    #[TestDox('replaces multiples of 3 with fizz, of 5 with buzz and of both with fizzbuzz')]
    public function testClassicFizzBuzz(): void
    {
        $result = $this->generator->generate(3, 5, 15, 'fizz', 'buzz');

        self::assertSame(
            ['1', '2', 'fizz', '4', 'buzz', 'fizz', '7', '8', 'fizz', 'buzz', '11', 'fizz', '13', '14', 'fizzbuzz'],
            $result,
        );
    }

    #[TestDox('accepts any divisors and strings, not only 3, 5, fizz and buzz')]
    public function testCustomDivisorsAndStrings(): void
    {
        $result = $this->generator->generate(2, 3, 6, 'foo', 'bar');

        self::assertSame(['1', 'foo', 'bar', 'foo', '5', 'foobar'], $result);
    }

    #[TestDox('concatenates both strings when both divisors are equal')]
    public function testSameDivisorTwice(): void
    {
        $result = $this->generator->generate(3, 3, 6, 'a', 'b');

        self::assertSame(['1', '2', 'ab', '4', '5', 'ab'], $result);
    }

    #[TestDox('returns only "1" when the limit is 1')]
    public function testLimitOfOne(): void
    {
        $result = $this->generator->generate(3, 5, 1, 'fizz', 'buzz');

        self::assertSame(['1'], $result);
    }
}
