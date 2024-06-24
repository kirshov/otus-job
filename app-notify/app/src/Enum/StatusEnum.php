<?php

declare(strict_types=1);

namespace App\Enum;

enum StatusEnum: int
{
	case WAIT = 0;
	case DONE = 1;
}