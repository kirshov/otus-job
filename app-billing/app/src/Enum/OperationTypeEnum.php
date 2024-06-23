<?php

declare(strict_types=1);

namespace App\Enum;

enum OperationTypeEnum
{
	/** Приход */
	case INCOMING;

	/** Списание */
	case OUTCOMING;
}