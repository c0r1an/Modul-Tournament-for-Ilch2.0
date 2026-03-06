<?php

namespace Modules\Tournament\Controllers\Admin;

use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TeamMemberMapper;
use Modules\User\Mappers\User as UserMapper;

class Teams extends \Ilch\Controller\Admin
{
    public function init()
    {
        $items = [
            [
                'name' => 'menuAdminTournament',
                'active' => false,
                'icon' => 'fa-solid fa-trophy',
                'url' => $this->getLayout()->getUrl(['controller' => 'tournaments', 'action' => 'index']),
                [
                    'name' => 'createTournament',
                    'active' => false,
                    'icon' => 'fa-solid fa-circle-plus',
                    'url' => $this->getLayout()->getUrl(['controller' => 'tournaments', 'action' => 'treat'])
                ]
            ],
            [
                'name' => 'teams',
                'active' => true,
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
                'active' => false,
                'icon' => 'fa-solid fa-gears',
                'url' => $this->getLayout()->getUrl(['controller' => 'settings', 'action' => 'index']),
            ],
        ];

        $this->getLayout()->addMenu('menuAdminTournament', $items);
    }

    public function indexAction()
    {
        $teamMapper = new TeamMapper();

        $this->getLayout()->getAdminHmenu()
            ->add($this->getTranslator()->trans('menuAdminTournament'), ['controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('teams'), ['action' => 'index']);

        $this->getView()->set('teams', $teamMapper->getAll());
    }

    public function treatAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $teamMapper = new TeamMapper();
        $memberMapper = new TeamMemberMapper();
        $userMapper = new UserMapper();

        $team = $teamMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['action' => 'index']);
        }

        $captainUser = $userMapper->getUserById((int)$team['captain_user_id']);
        $captainName = $captainUser ? $captainUser->getName() : '';

        $this->getLayout()->getAdminHmenu()
            ->add($this->getTranslator()->trans('menuAdminTournament'), ['controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('teams'), ['action' => 'index'])
            ->add($team['name']);

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('remove_member_id')) {
                $removeMemberId = (int)$this->getRequest()->getPost('remove_member_id');
                foreach ($memberMapper->getByTeamId($id) as $member) {
                    if ((int)$member['id'] === $removeMemberId && $member['role'] !== 'captain') {
                        $memberMapper->remove($removeMemberId);
                        break;
                    }
                }

                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'treat', 'id' => $id]);
            }

            if ($this->getRequest()->getPost('username')) {
                $username = trim((string)$this->getRequest()->getPost('username'));
                $user = $userMapper->getUserByName($username);

                if ($username !== '') {
                    if ($user) {
                        if (!$memberMapper->isUserInTeam($id, (int)$user->getId())) {
                            $memberMapper->add([
                                'team_id' => $id,
                                'user_id' => (int)$user->getId(),
                                'nickname' => $user->getName(),
                                'role' => 'member',
                            ]);
                        }
                    } else {
                        $memberMapper->add([
                            'team_id' => $id,
                            'user_id' => null,
                            'nickname' => $username,
                            'role' => 'member',
                        ]);
                    }
                }

                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'treat', 'id' => $id]);
            }

            $captainUserId = (int)$team['captain_user_id'];
            $captainUsername = trim((string)$this->getRequest()->getPost('captain_username'));
            if ($captainUsername !== '') {
                $newCaptain = $userMapper->getUserByName($captainUsername);
                if ($newCaptain) {
                    $captainUserId = (int)$newCaptain->getId();
                }
            }

            $teamMapper->save([
                'name' => trim((string)$this->getRequest()->getPost('name')),
                'tag' => trim((string)$this->getRequest()->getPost('tag')),
                'logo' => trim((string)$this->getRequest()->getPost('logo')),
                'contact_discord' => trim((string)$this->getRequest()->getPost('contact_discord')),
                'contact_email' => trim((string)$this->getRequest()->getPost('contact_email')),
                'captain_user_id' => $captainUserId,
            ], $id);

            if ($captainUserId !== (int)$team['captain_user_id']) {
                $members = $memberMapper->getByTeamId($id);
                foreach ($members as $member) {
                    if ($member['role'] === 'captain' && (int)$member['user_id'] !== $captainUserId) {
                        $memberMapper->updateRole((int)$member['id'], 'member');
                    }
                }

                $captainMember = $memberMapper->getByTeamAndUser($id, $captainUserId);
                if ($captainMember) {
                    $memberMapper->updateRole((int)$captainMember['id'], 'captain');
                } else {
                    $newCaptain = $userMapper->getUserById($captainUserId);
                    if ($newCaptain) {
                        $memberMapper->add([
                            'team_id' => $id,
                            'user_id' => $captainUserId,
                            'nickname' => $newCaptain->getName(),
                            'role' => 'captain',
                        ]);
                    }
                }
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'treat', 'id' => $id]);
        }

        $this->getView()
            ->set('team', $team)
            ->set('members', $memberMapper->getByTeamId($id))
            ->set('captainName', $captainName);
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id > 0) {
                $teamMapper = new TeamMapper();
                $teamMapper->delete($id);
                $this->addMessage('deleteSuccess');
            }
        }

        $this->redirect(['action' => 'index']);
    }
}
