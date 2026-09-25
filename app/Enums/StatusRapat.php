<?php

namespace App\Enums;

enum StatusRapat: string
{
    case DRAFT = 'draft';
    case DIBUKA = 'dibuka';
    case DITUTUP = 'ditutup';
}
