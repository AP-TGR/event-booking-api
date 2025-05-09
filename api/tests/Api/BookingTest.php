<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

class BookingTest extends ApiTestCase
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

    public function testCreateBooking(): void
    {
        $client = static::createClient();

        // Create an event
        $eventResponse = $client->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025',
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $eventId = $eventResponse->toArray()['id'];

        // Create an attendee
        $attendeeResponse = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe',
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $attendeeId = $attendeeResponse->toArray()['id'];

        // Create a booking
        $response = $client->request('POST', '/bookings', [
            'json' => [
                'event' => "/events/$eventId",
                'attendee' => "/attendees/$attendeeId",
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            '@context' => '/contexts/Booking',
            '@type' => 'Booking',
            'event' => "/events/$eventId",
            'attendee' => "/attendees/$attendeeId",
        ]);
    }

    public function testCreateBookingWithInvalidData(): void
    {
        $client = static::createClient();

        // Create an event
        $eventResponse = $client->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025',
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $eventId = $eventResponse->toArray()['id'];

        // Create an attendee
        $attendeeResponse = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe',
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $attendeeId = $attendeeResponse->toArray()['id'];

        // Attempt to create a booking with invalid data
        $response = $client->request('POST', '/bookings', [
            'json' => [
                'event' => null, // Invalid: event is null
                'attendee' => "/attendees/$attendeeId", // Valid attendee
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);

        $this->assertResponseStatusCodeSame(400); // Bad Request
        $this->assertJsonContains([
            'detail' => 'The type of the "event" attribute must be "array" (nested document) or "string" (IRI), "NULL" given.',
        ]);
    }

    public function testUpdateBooking(): void
    {
        $client = static::createClient();

        // Create an event
        $eventResponse = $client->request('POST', '/events', [
            'json' => [
                'name' => 'test_Tech Conference 2025',
                'capacity' => 100,
                'date' => '2025-12-01T10:00:00+00:00',
                'country' => 'USA',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $eventId = $eventResponse->toArray()['id'];

        // Create an attendee
        $attendeeResponse = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_John Doe',
                'email' => 'test_john.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $attendeeId = $attendeeResponse->toArray()['id'];

        // Create a booking
        $bookingResponse = $client->request('POST', '/bookings', [
            'json' => [
                'event' => "/events/$eventId",
                'attendee' => "/attendees/$attendeeId",
            ],
            'headers' => [
                'Content-Type' => 'application/json',
            ],
        ]);
        $bookingId = $bookingResponse->toArray()['id'];

        // Update the booking (e.g., change the attendee)
        $newAttendeeResponse = $client->request('POST', '/attendees', [
            'json' => [
                'name' => 'test_Jane Doe',
                'email' => 'test_jane.doe@example.com',
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json',
            ],
        ]);
        $newAttendeeId = $newAttendeeResponse->toArray()['id'];

        $client->request('PUT', "/bookings/$bookingId", [
            'json' => [
                'event' => "/events/$eventId",
                'attendee' => "/attendees/$newAttendeeId",
            ],
            'headers' => [
                'Content-Type' => 'application/ld+json', // Corrected Content-Type
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);
        $this->assertJsonContains([
            'event' => "/events/$eventId",
            'attendee' => "/attendees/$newAttendeeId",
        ]);
    }
}