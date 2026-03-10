<?php

namespace App\Enums;

enum PostStatus: string
{
    case Published = 'published';
    case Pending = 'pending';
    case Unpublished = 'unpublished';
}
