<?php

declare(strict_types=1);

namespace Alumkit\Alumkit\Observers;

use Alumkit\Alumkit\Models\Content;
use Alumkit\Alumkit\Models\Page;

class PageObserver
{
    public function deleting(Page $page): void
    {
        Content::where('owner', "page:{$page->slug}")->delete();
    }
}
