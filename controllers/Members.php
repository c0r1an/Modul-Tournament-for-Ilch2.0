<?php

namespace Modules\Tournament\Controllers;

use Modules\Tournament\Mappers\MemberProfileMapper;
use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TeamMemberMapper;
use Modules\User\Mappers\User as UserMapper;

class Members extends \Ilch\Controller\Frontend
{
    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $memberMapper = new TeamMemberMapper();
        $teamMapper = new TeamMapper();
        $profileMapper = new MemberProfileMapper();
        $userMapper = new UserMapper();

        $member = $this->findMemberById($memberMapper, $id);
        if (!$member) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }

        $team = $teamMapper->getById((int)$member['team_id']);
        if (!$team) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }

        $profile = $profileMapper->getByTeamMemberId($id);
        $registeredUser = !empty($member['user_id']) ? $userMapper->getUserById((int)$member['user_id']) : null;
        $displayName = $profile['full_name'] ?? ($registeredUser ? $registeredUser->getName() : '');

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($this->getTranslator()->trans('teams'), ['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team['id']])
            ->add($member['nickname'] ?: $displayName ?: $this->getTranslator()->trans('memberNumber', $id));

        $this->getView()
            ->set('member', $member)
            ->set('team', $team)
            ->set('profile', $profile)
            ->set('displayName', $displayName)
            ->set('canEdit', $this->canEdit($member, $team));
    }

    public function editAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $id = (int)$this->getRequest()->getParam('id');
        $memberMapper = new TeamMemberMapper();
        $teamMapper = new TeamMapper();
        $profileMapper = new MemberProfileMapper();

        $member = $this->findMemberById($memberMapper, $id);
        if (!$member) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }

        $team = $teamMapper->getById((int)$member['team_id']);
        if (!$team) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }

        if (!$this->canEdit($member, $team)) {
            $this->redirect()->withMessage('noRights', 'danger')->to(['action' => 'view', 'id' => $id]);
        }

        if ($this->getRequest()->isPost()) {
            $ageRaw = trim((string)$this->getRequest()->getPost('age'));
            $age = $ageRaw === '' ? null : max(0, (int)$ageRaw);

            $profileMapper->saveForTeamMember($id, [
                'full_name' => trim((string)$this->getRequest()->getPost('full_name')),
                'nickname' => trim((string)$this->getRequest()->getPost('nickname')),
                'age' => $age,
                'gender' => trim((string)$this->getRequest()->getPost('gender')),
                'social_links' => trim((string)$this->getRequest()->getPost('social_links')),
                'bio' => trim((string)$this->getRequest()->getPost('bio')),
                'games' => trim((string)$this->getRequest()->getPost('games')),
                'homepage' => trim((string)$this->getRequest()->getPost('homepage')),
            ]);

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $id]);
        }

        $profile = $profileMapper->getByTeamMemberId($id);

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index'])
            ->add($team['name'], ['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team['id']])
            ->add($this->getTranslator()->trans('edit'));

        $this->getView()
            ->set('member', $member)
            ->set('team', $team)
            ->set('profile', $profile ?: []);
    }

    private function canEdit(array $member, array $team): bool
    {
        $user = $this->getUser();
        if (!$user) {
            return false;
        }

        $userId = (int)$user->getId();
        $memberUserId = (int)($member['user_id'] ?? 0);

        if ($memberUserId > 0) {
            return $userId === $memberUserId;
        }

        return $userId === (int)$team['captain_user_id'];
    }

    private function findMemberById(TeamMemberMapper $memberMapper, int $id): ?array
    {
        return $id > 0 ? $memberMapper->getById($id) : null;
    }
}
