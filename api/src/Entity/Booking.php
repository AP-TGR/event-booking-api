<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\BookingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use App\Validator\OverbookingNotAllowed;
use Symfony\Component\Validator\Constraints as Assert;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\RequestBody;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_booking', columns: ['event_id', 'attendee_id'])]
#[ApiResource(
    normalizationContext: ['groups' => ['booking:read']],
    denormalizationContext: ['groups' => ['booking:write']],
    operations: [
        new \ApiPlatform\Metadata\GetCollection(),
        new \ApiPlatform\Metadata\Get(),
        // new \ApiPlatform\Metadata\Post(),
        new \ApiPlatform\Metadata\Post(
            uriTemplate: '/bookings',
            openapi: new Operation(
                summary: 'Book an event',
                requestBody: new RequestBody(
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'event' => [
                                        'type' => 'string',
                                        'example' => 'events/1'
                                    ],
                                    'attendee' => [
                                        'type' => 'string',
                                        'example' => 'attendees/2'
                                    ],
                                ],
                                'required' => ['event', 'attendee'],
                            ],
                            'example' => [
                                'event' => 'events/1',
                                'attendee' => 'attendees/2',
                            ],
                        ]
                    ])
                )
            ),
            inputFormats: ['json' => ['application/json']]
        ),
        new \ApiPlatform\Metadata\Put(),
        new \ApiPlatform\Metadata\Delete()
    ]
)]
#[OverbookingNotAllowed]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['booking:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['booking:read', 'booking:write'])]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'bookings')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['booking:read', 'booking:write'])]
    private ?Attendee $attendee = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\LessThanOrEqual('now', message: 'The booking date cannot be in the future.')]
    #[Groups(['booking:read'])]
    private ?\DateTimeImmutable $bookedAt = null;

    public function __construct()
    {
        $this->bookedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

        return $this;
    }

    public function getAttendee(): ?Attendee
    {
        return $this->attendee;
    }

    public function setAttendee(?Attendee $attendee): static
    {
        $this->attendee = $attendee;

        return $this;
    }

    public function getBookedAt(): ?\DateTimeImmutable
    {
        return $this->bookedAt;
    }

    public function setBookedAt(\DateTimeImmutable $bookedAt): static
    {
        $this->bookedAt = $bookedAt;

        return $this;
    }
}
