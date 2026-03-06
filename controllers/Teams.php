<?php

namespace Modules\Tournament\Controllers;

use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TeamMemberMapper;
use Modules\User\Mappers\User as UserMapper;

class Teams extends \Ilch\Controller\Frontend
{
    /** @var string[] */
    private $allowedLogoExtensions = ['png', 'jpg', 'jpeg', 'webp'];

    public function indexAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $teamMapper = new TeamMapper();

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('myTeams'));
        $this->getView()->set('teams', $teamMapper->getByCaptain((int)$this->getUser()->getId()));
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $teamMapper = new TeamMapper();
        $memberMapper = new TeamMemberMapper();

        $team = $teamMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['action' => 'index']);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('myTeams'), ['action' => 'index'])
            ->add($team['name']);

        $this->getView()->set('team', $team)->set('members', $memberMapper->getByTeamId($id));
    }

    public function createAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $teamMapper = new TeamMapper();
        $memberMapper = new TeamMemberMapper();

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('myTeams'), ['action' => 'index'])
            ->add($this->getTranslator()->trans('createTeam'));

        if ($this->getRequest()->isPost()) {
            $name = trim((string)$this->getRequest()->getPost('name'));
            if ($name === '') {
                $this->redirect()->withMessage('emptyMessage', 'danger')->withInput()->to(['action' => 'create']);
            }

            $logoPath = $this->handleLogoUpload();

            $teamId = $teamMapper->save([
                'name' => $name,
                'tag' => trim((string)$this->getRequest()->getPost('tag')),
                'logo' => $logoPath,
                'captain_user_id' => (int)$this->getUser()->getId(),
                'contact_discord' => trim((string)$this->getRequest()->getPost('contact_discord')),
                'contact_email' => trim((string)$this->getRequest()->getPost('contact_email')),
            ]);

            $memberMapper->add([
                'team_id' => $teamId,
                'user_id' => (int)$this->getUser()->getId(),
                'nickname' => $this->getUser()->getName(),
                'role' => 'captain',
            ]);

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $teamId]);
        }
    }

    public function editAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $id = (int)$this->getRequest()->getParam('id');
        $teamMapper = new TeamMapper();
        $memberMapper = new TeamMemberMapper();
        $userMapper = new UserMapper();

        if (!$teamMapper->isCaptain($id, (int)$this->getUser()->getId())) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'index']);
        }

        $team = $teamMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['action' => 'index']);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('myTeams'), ['action' => 'index'])
            ->add($team['name'], ['action' => 'view', 'id' => $id])
            ->add($this->getTranslator()->trans('editTeam'));

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('remove_member_id')) {
                $memberMapper->remove((int)$this->getRequest()->getPost('remove_member_id'));
                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
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
                        // Allow adding non-registered players by nickname.
                        $memberMapper->add([
                            'team_id' => $id,
                            'user_id' => null,
                            'nickname' => $username,
                            'role' => 'member',
                        ]);
                    }
                }
                $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
            }

            $teamMapper->save([
                'name' => trim((string)$this->getRequest()->getPost('name')),
                'tag' => trim((string)$this->getRequest()->getPost('tag')),
                'contact_discord' => trim((string)$this->getRequest()->getPost('contact_discord')),
                'contact_email' => trim((string)$this->getRequest()->getPost('contact_email')),
                'logo' => $this->resolveUpdatedLogo($team),
            ], $id);

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'edit', 'id' => $id]);
        }

        $this->getView()->set('team', $team)->set('members', $memberMapper->getByTeamId($id));
    }

    public function delAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        if (!$this->getRequest()->isSecure()) {
            $this->redirect()->to(['action' => 'index']);
        }

        $id = (int)$this->getRequest()->getParam('id');
        $teamMapper = new TeamMapper();

        if (!$teamMapper->isCaptain($id, (int)$this->getUser()->getId())) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'index']);
        }

        $team = $teamMapper->getById($id);
        if (!$team) {
            $this->redirect()->to(['action' => 'index']);
        }

        if (!empty($team['logo']) && file_exists($team['logo'])) {
            @unlink($team['logo']);
        }

        $teamMapper->delete($id);
        $this->redirect()->withMessage('deleteSuccess')->to(['action' => 'index']);
    }

    private function resolveUpdatedLogo(array $team): ?string
    {
        $uploadedLogo = $this->handleLogoUpload();
        if ($uploadedLogo !== null) {
            if (!empty($team['logo']) && file_exists($team['logo'])) {
                @unlink($team['logo']);
            }
            return $uploadedLogo;
        }

        return $team['logo'] ?? null;
    }

    private function handleLogoUpload(): ?string
    {
        if (!isset($_FILES['logo']) || empty($_FILES['logo']['name']) || !is_uploaded_file($_FILES['logo']['tmp_name'])) {
            return null;
        }

        $extension = strtolower((string)pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, $this->allowedLogoExtensions, true)) {
            return null;
        }

        $imageInfo = @getimagesize($_FILES['logo']['tmp_name']);
        if ($imageInfo === false || strpos((string)$imageInfo['mime'], 'image/') !== 0) {
            return null;
        }

        $baseDir = 'application/modules/tournament/storage/teams';
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0775, true);
        }

        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $target = $baseDir . '/' . $filename;
        if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target)) {
            return null;
        }

        return $target;
    }
}
