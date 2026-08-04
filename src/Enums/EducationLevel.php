<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Enums;

enum EducationLevel: string
{
    case Honors = 'honors';
    case Masters = 'masters';
    case Phd = 'phd';
    case Diploma = 'diploma';
    case Certificate = 'certificate';
}
