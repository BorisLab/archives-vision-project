<?php

namespace App\Entity;

enum TypeNotification: string
{
    case DEMANDE = "request";
    case REJET_REPONSE = "rejection-response";
    case REVOC_REPONSE = "revocation-response";
    case APPROB_REPONSE = "approval-response";
    case INFO_REPONSE = "info-response";
    case MESSAGE = "chat";
}
