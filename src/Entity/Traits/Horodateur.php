<?php

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;

trait Horodateur
{

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $date_creation;

    #[ORM\Column(type: 'datetime_immutable', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private $date_modif;

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getDateModif(): ?\DateTimeInterface
    {
        return $this->date_modif;
    }

    public function setDateModif(\DateTimeInterface $date_modif): self
    {
        $this->date_modif = $date_modif;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function applyHorodatage()
    {
        if($this->getDateCreation() === null)
        {
           $this->setDateCreation(new \DateTimeImmutable);
        }
        $this->setDateModif(new \DateTimeImmutable);
    }
}

?>