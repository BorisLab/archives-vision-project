<?php

namespace App\Entity;

enum StatutNotification: string
{
    case NON_LU = "unread";
    case LU = "read";
}
