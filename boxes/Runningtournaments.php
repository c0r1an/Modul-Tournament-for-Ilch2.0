<?php

namespace Modules\Tournament\Boxes;

use Ilch\Box;
use Modules\Tournament\Mappers\TournamentMapper;

class Runningtournaments extends Box
{
    public function render()
    {
        $mapper = new TournamentMapper();
        $this->getView()->set('tournaments', $mapper->getRunningForBox(5));
    }
}
