<?php

namespace App\Entity;

enum StatutUtilisateur: string
{
    case INACTIF = "inactive";
    case ACTIF = "active";
}
