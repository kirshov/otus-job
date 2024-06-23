<?php

namespace App\Enum;

enum StatusEnum: int
{
	case ACTIVE = 1;
	case DELETED = 2;
	case PAID = 3;
}