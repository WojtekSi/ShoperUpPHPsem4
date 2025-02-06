<?php

namespace App\Entity;

use App\Repository\ParkingSpacesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ParkingSpacesRepository::class)]
class ParkingSpaces
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, Reservations>
     */
    #[ORM\OneToMany(targetEntity: Reservations::class, mappedBy: 'parking_space')]
    private Collection $dates;

    public function __construct()
    {
        $this->dates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Reservations>
     */
    public function getDates(): Collection
    {
        return $this->dates;
    }

    public function addDate(Reservations $date): static
    {
        if (!$this->dates->contains($date)) {
            $this->dates->add($date);
            $date->setParkingSpace($this);
        }

        return $this;
    }

    public function removeDate(Reservations $date): static
    {
        if ($this->dates->removeElement($date)) {
            // set the owning side to null (unless already changed)
            if ($date->getParkingSpace() === $this) {
                $date->setParkingSpace(null);
            }
        }

        return $this;
    }

}
