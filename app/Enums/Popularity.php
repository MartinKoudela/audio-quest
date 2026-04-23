<?php

namespace App\Enums;

enum Popularity: string
{
    case Any         = 'any';
    case Mainstream  = 'mainstream';
    case Underground = 'underground';
    case Obscure     = 'obscure';
}