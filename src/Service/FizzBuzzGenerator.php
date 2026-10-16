<?php

declare(strict_types=1);

namespace App\Service;

/**
 * A class for generating FizzBuzz sequences.
 */
final class FizzBuzzGenerator
{
    /**
     * @return list<string>
     */
    public function generate(
        int $int1,
        int $int2,
        int $limit,
        string $str1,
        string $str2,
    ): array {
        // Store the final sequence
        $result = [];

        // Go through every number from 1 to the limit
        for ($number = 1; $number <= $limit; ++$number) {
            $word = '';

            // Add the first string if the number is divisible by int1
            if (0 === $number % $int1) {
                $word .= $str1;
            }

            // Add the second string if the number is divisible by int2
            if (0 === $number % $int2) {
                $word .= $str2;
            }

            // Use the number if neither condition matched
            if ('' === $word) {
                $result[] = (string) $number;
            } else {
                $result[] = $word;
            }
        }

        return $result;
    }
}
