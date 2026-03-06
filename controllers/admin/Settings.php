<?php

namespace Modules\Tournament\Controllers\Admin;

class Settings extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'menuAdminTournament',
                'active' => false,
                'icon' => 'fa-solid fa-trophy',
                'url' => $this->getLayout()->getUrl(['controller' => 'tournaments', 'action' => 'index']),
            ],
            [
                'name' => 'teams',
                'active' => false,
                'icon' => 'fa-solid fa-users',
                'url' => $this->getLayout()->getUrl(['controller' => 'teams', 'action' => 'index']),
            ],
            [
                'name' => 'disputes',
                'active' => false,
                'icon' => 'fa-solid fa-gavel',
                'url' => $this->getLayout()->getUrl(['controller' => 'disputes', 'action' => 'index']),
            ],
            [
                'name' => 'settings',
                'active' => true,
                'icon' => 'fa-solid fa-gears',
                'url' => $this->getLayout()->getUrl(['controller' => 'settings', 'action' => 'index']),
            ],
        ];

        $this->getLayout()->addMenu('menuAdminTournament', $items);
    }

    public function indexAction()
    {
        $this->getLayout()->getAdminHmenu()
            ->add($this->getTranslator()->trans('menuAdminTournament'), ['controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('settings'), ['action' => 'index']);

        if ($this->getRequest()->isPost()) {
            $theme = (string)$this->getRequest()->getPost('bracket_theme');
            if (!in_array($theme, ['light', 'dark'], true)) {
                $theme = 'light';
            }

            $this->getConfig()->set('tournament_bracket_theme', $theme);
            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'index']);
        }

        $this->getView()->set('bracketTheme', $this->getConfig()->get('tournament_bracket_theme') ?: 'light');
    }
}
