<?php

namespace App\Entity;

enum NiveauAccesNotification: string
{
    case ADMINISTRATEUR = "admin";
    case ARCHIVISTE = "archivist";
    case UTILISATEUR = "user";
}
