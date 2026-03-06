<?php

namespace Modules\Tournament\Controllers\Admin;

class Index extends \Ilch\Controller\Admin
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
