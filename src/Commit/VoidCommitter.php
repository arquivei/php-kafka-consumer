<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

class VoidCommitter implements Committer
{
    public function commitMessage(): void
    {
    }

    public function commitDlq(): void
    {
    }
}
