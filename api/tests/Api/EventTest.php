<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

class EventTest extends ApiTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Get the EntityManager
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Remove test data
        $connection = $entityManager->getConnection();

        // Delete `booking` records referencing test data
        $connection->executeStatement('DELETE FROM booking WHERE event_id IN (SELECT id FROM event WHERE name LIKE \'test_%\')');
        $connection->executeStatement('DELETE FROM booking WHERE attendee_id IN (SELECT id FROM attendee WHERE name LIKE \'test_%\')');

        // Delete `attendee` records with the `test_` prefix
        $connection->executeStatement('DELETE FROM attendee WHERE name LIKE \'test_%\'');

        // Delete `event` records with the `test_` prefix
        $connection->executeStatement('DELETE FROM event WHERE name LIKE \'test_%\'');

        // Close the EntityManager
        $entityManager->close();
    }

    public function testCreateEvent(): void
    {
        $response = static::createClient()->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025', // Use the test_ prefix
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Event',
            '@type' => 'Event',
            'name' => 'test_Tech Conference 2025',
            'capacity' => 100,
            'date' => '2025-12-01T10:00:00+00:00',
            'country' => 'USA',
        ]);
    }

    public function testCreateEventWithInvalidData(): void
    {
        $response = static::createClient()->request('POST', '/events', [
            'json' => [
                'name' => 'test_', // Invalid: name is blank
                'capacity' => -10, // Invalid: capacity must be positive
                'date' => '2020-01-01T10:00:00+00:00', // Invalid: date is in the past
                'country' => '', // Invalid: country is blank
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422); // Unprocessable Entity
        $this->assertJsonContains([
            'violations' => [
                ['propertyPath' => 'capacity', 'message' => 'This value should be positive.'],
                ['propertyPath' => 'date', 'message' => 'The event date must be in the future.'],
                ['propertyPath' => 'country', 'message' => 'This value should not be blank.'],
            ],
        ]);
    }

    public function testGetEventCollection(): void
    {
        $response = static::createClient()->request('GET', '/events', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Event',
            '@type' => 'Collection', // Updated to match the actual response
        ]);
    }

    public function testUpdateEvent(): void
    {
        $client = static::createClient();

        // Create an event first
        $response = $client->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025', // Use the test_ prefix
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $eventId = $response->toArray()['id'];

        // Update the event
        $client->request('PUT', "/events/$eventId", [
            'json' => [
                'name' => 'test_Updated Tech Conference', // Use the test_ prefix
                'capacity' => 200,
                'date' => '2025-12-01T10:00:00+00:00', // Include required field
                'country' => 'USA', // Include required field
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'name' => 'test_Updated Tech Conference',
            'capacity' => 200,
        ]);
    }

    public function testDeleteEvent(): void
    {
        $client = static::createClient();

        // Create an event first
        $response = $client->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025', // Use the test_ prefix
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $eventId = $response->toArray()['id'];

        // Delete the event
        $client->request('DELETE', "/events/$eventId");

        $this->assertResponseStatusCodeSame(204); // No Content
    }
}