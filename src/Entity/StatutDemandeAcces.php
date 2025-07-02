<?php

namespace App\Entity;

enum StatutDemandeAcces: string
{
    case EN_ATTENTE = "pending";
    case APPROUVE = "approved";
    case REJETE = "rejected";
    case REVOQUE = "revoked";
}
