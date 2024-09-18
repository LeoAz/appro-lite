<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class LoadStatus extends Enum
{
    const Pending = "EN COURS";
    const Unloaded = "DECHARGÉ";
}
