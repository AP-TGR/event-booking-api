<?php

namespace App\Tests\Validator;

use App\Entity\Booking;
use App\Entity\Event;
use App\Validator\OverbookingNotAllowed;
use App\Validator\OverbookingNotAllowedValidator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class OverbookingNotAllowedValidatorTest extends TestCase
{
    private $entityManager;
    private $repository;
    private $context;
    private $validator;

    protected function setUp(): void
    {
        // Mock the EntityManagerInterface
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Mock the EntityRepository
        $this->repository = $this->createMock(EntityRepository::class);
        $this->entityManager
            ->method('getRepository')
            ->with(Booking::class)
            ->willReturn($this->repository);

        // Mock the ExecutionContextInterface
        $this->context = $this->createMock(ExecutionContextInterface::class);

        // Instantiate the validator
        $this->validator = new OverbookingNotAllowedValidator($this->entityManager);
        $this->validator->initialize($this->context);
    }

    public function testValidateNoOverbooking(): void
    {
        // Mock the repository count method to return a number below capacity
        $this->repository
            ->method('count')
            ->willReturn(5);

        // Create a mock event with a capacity of 10
        $event = new Event();
        $event->setName('Test Event');
        $event->setCapacity(10);

        // Create a booking for the event
        $booking = new Booking();
        $booking->setEvent($event);

        // Expect no violations
        $this->context
            ->expects($this->never())
            ->method('buildViolation');

        // Validate the booking
        $constraint = new OverbookingNotAllowed(['message' => 'Event "{{ event }}" is overbooked.']);
        $this->validator->validate($booking, $constraint);
    }

    public function testValidateOverbooking(): void
    {
        // Mock the repository count method to return a number equal to capacity
        $this->repository
            ->method('count')
            ->willReturn(10);

        // Create a mock event with a capacity of 10
        $event = new Event();
        $event->setName('Test Event');
        $event->setCapacity(10);

        // Create a booking for the event
        $booking = new Booking();
        $booking->setEvent($event);

        // Mock the violation builder
        $violationBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $violationBuilder
            ->expects($this->once())
            ->method('setParameter')
            ->with('{{ event }}', 'Test Event')
            ->willReturnSelf();
        $violationBuilder
            ->expects($this->once())
            ->method('addViolation');

        // Expect a violation to be built
        $this->context
            ->expects($this->once())
            ->method('buildViolation')
            ->with('Event "{{ event }}" is overbooked.')
            ->willReturn($violationBuilder);

        // Validate the booking
        $constraint = new OverbookingNotAllowed(['message' => 'Event "{{ event }}" is overbooked.']);
        $this->validator->validate($booking, $constraint);
    }
}