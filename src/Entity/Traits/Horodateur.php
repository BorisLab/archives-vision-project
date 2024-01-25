<?php

namespace App\Entity\Traits;

use DateTimeImmutable;
use DateTimeInterface;

trait Horodateur
{

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $dateAjout;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $dateModif;

    public function getDateAjout(): ?\DateTimeInterface
    {
        return $this->dateAjout;
    }

    public function setDateAjout(\DateTimeInterface $dateAjout): self
    {
        $this->dateAjout = $dateAjout;

        return $this;
    }

    public function getDateModif(): ?\DateTimeInterface
    {
        return $this->dateModif;
    }

    public function setDateModif(\DateTimeInterface $dateModif): self
    {
        $this->dateModif = $dateModif;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function modifHorodatage()
    {
        if($this->getDateAjout() == null)
        {
           $this->setDateAjout(new \DateTimeImmutable);
        }
        $this->setDateModif(new \DateTimeImmutable);
    }
}

?>