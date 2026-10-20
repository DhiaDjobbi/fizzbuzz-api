<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[TestDox('TEST Stats Controller')]
final class StatsControllerTest extends WebTestCase
{
    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = static::createClient();

        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $entityManager->createQuery('DELETE FROM App\Entity\FizzBuzzRequest')->execute();
    }

    #[TestDox('it returns the most used request')]
    public function testItReturnsTheMostUsedRequest(): void
    {
        $this->client->request('GET', '/fizzbuzz?int1=2&int2=3&limit=10&str1=foo&str2=bar');
        $this->client->request('GET', '/fizzbuzz?int1=2&int2=3&limit=10&str1=foo&str2=bar');
        $this->client->request('GET', '/fizzbuzz?int1=3&int2=5&limit=20&str1=fizz&str2=buzz');

        $this->client->request('GET', '/stats');

        self::assertResponseIsSuccessful();

        self::assertSame(
            [
                'int1' => 2,
                'int2' => 3,
                'limit' => 10,
                'str1' => 'foo',
                'str2' => 'bar',
                'hits' => 2,
            ],
            json_decode($this->client->getResponse()->getContent(), true),
        );
    }

    #[TestDox('it returns not found when no requests have been tracked')]
    public function testItReturnsNotFoundWhenNoRequestsHaveBeenTracked(): void
    {
        $this->client->request('GET', '/stats');

        self::assertResponseStatusCodeSame(404);
    }

    #[TestDox('it rejects query parameters')]
    public function testItRejectsQueryParameters(): void
    {
        $this->client->request('GET', '/stats?foo=bar');

        self::assertResponseStatusCodeSame(400);
    }
}
