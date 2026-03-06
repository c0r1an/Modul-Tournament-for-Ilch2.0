<?php

namespace Modules\Tournament\Controllers;

class Index extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $this->redirect()->to([
            'module' => 'tournament',
            'controller' => 'tournaments',
            'action' => 'index',
        ]);
    }
}
