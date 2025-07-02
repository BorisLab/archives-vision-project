<?php

namespace App\Entity;

enum StatutMessage: string
{
    case NON_LU = "unread";
    case LU = "read";
}
