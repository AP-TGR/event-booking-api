<?php

namespace App\Tests\Repository;

use App\Entity\Booking;
use App\Entity\Event;
use App\Entity\Attendee;
use App\Repository\BookingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookingRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private BookingRepository $bookingRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->bookingRepository = $this->entityManager->getRepository(Booking::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Check if the EntityManager is open before cleaning up
        if (!$this->entityManager->isOpen()) {
            return;
        }

        // Clean up the database
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('DELETE FROM booking WHERE event_id IN (SELECT id FROM event WHERE name LIKE \'test_%\')');
        $connection->executeStatement('DELETE FROM attendee WHERE name LIKE \'test_%\'');
        $connection->executeStatement('DELETE FROM event WHERE name LIKE \'test_%\'');

        $this->entityManager->close();
    }

    public function testGetBookingCountForEvent(): void
    {
        // Create a test event
        $event = new Event();
        $event->setName('test_Event');
        $event->setCapacity(100);
        $event->setDate(new \DateTimeImmutable('2025-12-01T10:00:00+00:00'));
        $event->setCountry('USA');
        $this->entityManager->persist($event);

        // Create the first test attendee
        $attendee1 = new Attendee();
        $attendee1->setName('test_John Doe');
        $attendee1->setEmail('test_john.doe@example.com');
        $this->entityManager->persist($attendee1);

        // Create the second test attendee
        $attendee2 = new Attendee();
        $attendee2->setName('test_Jane Doe');
        $attendee2->setEmail('test_jane.doe@example.com');
        $this->entityManager->persist($attendee2);

        // Create the first booking for the event
        $booking1 = new Booking();
        $booking1->setEvent($event);
        $booking1->setAttendee($attendee1);
        $booking1->setBookedAt(new \DateTimeImmutable());
        $this->entityManager->persist($booking1);

        // Create the second booking for the event with a different attendee
        $booking2 = new Booking();
        $booking2->setEvent($event);
        $booking2->setAttendee($attendee2);
        $booking2->setBookedAt(new \DateTimeImmutable());
        $this->entityManager->persist($booking2);

        // Flush to save data to the database
        $this->entityManager->flush();

        // Call the method and assert the result
        $bookingCount = $this->bookingRepository->getBookingCountForEvent($event);
        $this->assertEquals(2, $bookingCount);
    }
}