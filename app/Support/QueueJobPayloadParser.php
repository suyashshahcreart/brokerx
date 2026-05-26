<?php

namespace App\Support;

use App\Jobs\ProcessTourZipFile;
use ReflectionClass;

class QueueJobPayloadParser
{
    /**
     * Laravel queue payload JSON decoded to array or empty.
     *
     * @return array<string, mixed>
     */
    public static function decodePayload(?string $json): array
    {
        if ($json === null || $json === '') {
            return [];
        }

        $data = json_decode($json, true);

        return is_array($data) ? $data : [];
    }

    public static function displayName(array $payload): string
    {
        return (string) ($payload['displayName'] ?? 'UnknownJob');
    }

    /**
     * Extract booking ID from serialized ProcessTourZipFile payloads only.
     */
    public static function bookingIdFromPayload(array $payload): ?int
    {
        if (self::displayName($payload) !== ProcessTourZipFile::class) {
            return null;
        }

        $serialized = $payload['data']['command'] ?? null;
        if (! is_string($serialized) || $serialized === '') {
            return null;
        }

        try {
            $command = @unserialize($serialized, ['allowed_classes' => [ProcessTourZipFile::class]]);
        } catch (\Throwable) {
            return null;
        }

        if (! $command instanceof ProcessTourZipFile) {
            return null;
        }

        try {
            $ref = new ReflectionClass($command);
            $prop = $ref->getProperty('bookingId');
            $prop->setAccessible(true);
            $id = $prop->getValue($command);

            return is_numeric($id) ? (int) $id : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
