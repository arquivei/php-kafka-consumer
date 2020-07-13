<?php

declare(strict_types=1);

namespace Kafka\Consumer\Commit;

interface Committer
{
    public function commitMessage(): void;
    public function commitDlq(): void;
}
