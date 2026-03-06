<?php

namespace Modules\Tournament\Boxes;

use Ilch\Box;
use Modules\Tournament\Mappers\MatchMapper;

class Nextmatches extends Box
{
    public function render()
    {
        $matchMapper = new MatchMapper();
        $this->getView()->set('matches', $matchMapper->getUpcomingForBox(5));
    }
}
