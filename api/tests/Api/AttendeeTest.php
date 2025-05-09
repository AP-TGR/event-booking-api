<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

class AttendeeTest extends ApiTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        // Get the EntityManager
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Remove test data
        $connection = $entityManager->getConnection();

        // Delete `booking` records referencing test attendees
        $connection->executeStatement('DELETE FROM booking WHERE attendee_id IN (SELECT id FROM attendee WHERE name LIKE \'test_%\')');

        // Delete `attendee` records with the `test_` prefix
        $connection->executeStatement('DELETE FROM attendee WHERE name LIKE \'test_%\'');

        // Close the EntityManager
        $entityManager->close();
    }

    public function testCreateAttendee(): void
    {
        $response = static::createClient()->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe', // Use the test_ prefix
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Attendee',
            '@type' => 'Attendee',
            'name' => 'test_John Doe',
            'email' => 'test_john.doe@example.com',
        ]);
    }

    public function testCreateAttendeeWithInvalidData(): void
    {
        $response = static::createClient()->request('POST', '/attendees', [
            'json' => [
                'name' => '', // Invalid: name is blank
                'email' => 'invalid-email', // Invalid: not a valid email
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(422); // Unprocessable Entity
        $this->assertJsonContains([
            'violations' => [
                ['propertyPath' => 'name', 'message' => 'This value should not be blank.'],
                ['propertyPath' => 'email', 'message' => 'This value is not a valid email address.'],
            ],
        ]);
    }

    public function testGetAttendeeCollection(): void
    {
        $response = static::createClient()->request('GET', '/attendees', [
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            '@context' => '/contexts/Attendee',
            '@type' => 'Collection',
        ]);
    }

    public function testUpdateAttendee(): void
    {
        $client = static::createClient();

        // Create an attendee first
        $response = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe', // Use the test_ prefix
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $attendeeId = $response->toArray()['id'];

        // Update the attendee
        $client->request('PUT', "/attendees/$attendeeId", [
            'json' => [
                'name' => 'test_John Smith', // Use the test_ prefix
                'email' => 'test_john.smith@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'name' => 'test_John Smith',
            'email' => 'test_john.smith@example.com',
        ]);
    }

    public function testDeleteAttendee(): void
    {
        $client = static::createClient();

        // Create an attendee first
        $response = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe', // Use the test_ prefix
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        $attendeeId = $response->toArray()['id'];

        // Delete the attendee
        $client->request('DELETE', "/attendees/$attendeeId");

        $this->assertResponseStatusCodeSame(204); // No Content
    }
}