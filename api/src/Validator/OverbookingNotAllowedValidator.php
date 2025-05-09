<?php
// src/Validator/OverbookingNotAllowedValidator.php
namespace App\Validator;

use App\Entity\Booking;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OverbookingNotAllowedValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OverbookingNotAllowed) {
            throw new UnexpectedTypeException($constraint, OverbookingNotAllowed::class);
        }

        if (!$value instanceof Booking) {
            throw new UnexpectedValueException($value, Booking::class);
        }

        $event = $value->getEvent();
        if (!$event) {
            return; // Let other validators handle this
        }

        $bookingCount = $this->entityManager->getRepository(Booking::class)
            ->count(['event' => $event]);

        if ($bookingCount >= $event->getCapacity()) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ event }}', $event->getName())
                ->addViolation();
        }
    }
}
