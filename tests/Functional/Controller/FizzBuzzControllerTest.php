<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[TestDox('TEST FizzBuzz Controller')]
final class FizzBuzzControllerTest extends WebTestCase
{
    #[TestDox('it returns the fizz buzz list')]
    public function testItReturnsTheFizzBuzzList(): void
    {
        $client = static::createClient();

        $client->request('GET', '/fizzbuzz?int1=2&int2=3&limit=6&str1=foo&str2=bar');

        self::assertResponseIsSuccessful();

        self::assertSame(
            ['1', 'foo', 'bar', 'foo', '5', 'foobar'],
            json_decode($client->getResponse()->getContent(), true),
        );
    }

    #[TestDox('it rejects a zero divisor')]
    public function testItRejectsAZeroDivisor(): void
    {
        $client = static::createClient();

        $client->request('GET', '/fizzbuzz?int1=0&int2=5&limit=15&str1=fizz&str2=buzz');

        self::assertResponseStatusCodeSame(400);
    }

    #[TestDox('it rejects a limit above the maximum')]
    public function testItRejectsALimitAboveTheMaximum(): void
    {
        $client = static::createClient();

        $client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=10001&str1=fizz&str2=buzz');

        self::assertResponseStatusCodeSame(400);
    }

    #[TestDox('it rejects a missing parameter')]
    public function testItRejectsAMissingParameter(): void
    {
        $client = static::createClient();

        $client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=15&str1=fizz');

        self::assertResponseStatusCodeSame(400);

        self::assertSame(
            ['message' => 'The str2 parameter is required.'],
            json_decode($client->getResponse()->getContent(), true),
        );
    }
}
