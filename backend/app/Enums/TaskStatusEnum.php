<?php

namespace App\Enums;

enum TaskStatusEnum: int
{
    case NOT_STARTED = 1;
    case IN_PROGRESS = 2;
    case DONE = 3;

    public function name(): string
    {
        return match($this) {
            self::NOT_STARTED => 'not_started',
            self::IN_PROGRESS => 'in_progress',
            self::DONE => 'done',
        };
    }
}
