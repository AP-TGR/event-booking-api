<?php
namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class OverbookingNotAllowed extends Constraint
{
    public string $message = 'The event "{{ event }}" is already fully booked.';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
