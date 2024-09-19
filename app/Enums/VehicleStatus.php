<?php declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class VehicleStatus extends Enum
{
    const Available = "Disponible";
    const Loaded = "En chargement";
}
