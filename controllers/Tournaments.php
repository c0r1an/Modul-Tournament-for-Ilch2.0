<?php

namespace Modules\Tournament\Controllers;

use Modules\Tournament\Libraries\Status;
use Modules\Tournament\Mappers\MatchMapper;
use Modules\Tournament\Mappers\TournamentMapper;
use Modules\Tournament\Mappers\TournamentTeamMapper;
use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TeamMemberMapper;

class Tournaments extends \Ilch\Controller\Frontend
{
    public function indexAction()
    {
        $mapper = new TournamentMapper();
        $statusOptions = [
            Status::TOURNAMENT_DRAFT,
            Status::TOURNAMENT_REGISTRATION_OPEN,
            Status::TOURNAMENT_REGISTRATION_CLOSED,
            Status::TOURNAMENT_RUNNING,
            Status::TOURNAMENT_FINISHED,
            Status::TOURNAMENT_ARCHIVED,
        ];
        $status = (string)$this->getRequest()->getParam('status');
        if (!in_array($status, $statusOptions, true)) {
            $status = '';
        }

        $where = [];
        if ($status) {
            $where['status'] = $status;
        }

        $tournaments = $mapper->getAll($where, ['start_at' => 'ASC']);
        if ($status === '') {
            $tournaments = array_values(array_filter($tournaments, static function (array $tournament): bool {
                return ($tournament['status'] ?? '') !== Status::TOURNAMENT_ARCHIVED;
            }));
        }

        $this->getLayout()->getHmenu()->add($this->getTranslator()->trans('menuTournament'));
        $this->getView()
            ->set('tournaments', $tournaments)
            ->set('selectedStatus', $status)
            ->set('statusOptions', $statusOptions);
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');
        $tournamentMapper = new TournamentMapper();
        $registrationMapper = new TournamentTeamMapper();
        $matchMapper = new MatchMapper();
        $teamMapper = new TeamMapper();
        $teamMemberMapper = new TeamMemberMapper();

        $tournament = $tournamentMapper->getById($id);
        if (!$tournament) {
            $this->redirect()->withMessage('noTournaments', 'danger')->to(['action' => 'index']);
        }

        $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['action' => 'index'])
            ->add($tournament['title']);

        $teams = $registrationMapper->getAcceptedByTournamentId($id);
        foreach ($teams as &$team) {
            $team['players_count'] = $teamMemberMapper->countPlayers((int)$team['team_id']);
        }

        $myTeamsForRegister = [];
        $myTeamsForCheckin = [];
        if ($this->getUser()) {
            $myTeams = $teamMapper->getByCaptain((int)$this->getUser()->getId());
            foreach ($myTeams as $myTeam) {
                $registration = $registrationMapper->getByTournamentAndTeam($id, (int)$myTeam['id']);
                if (!$registration) {
                    $myTeamsForRegister[] = $myTeam;
                    continue;
                }

                if ($registration['status'] === 'accepted') {
                    $myTeamsForCheckin[] = $myTeam;
                }
            }
        }

        $this->getView()
            ->set('tournament', $tournament)
            ->set('teams', $teams)
            ->set('myTeamsForRegister', $myTeamsForRegister)
            ->set('myTeamsForCheckin', $myTeamsForCheckin)
            ->set('rounds', $matchMapper->getGroupedByRound($id))
            ->set('bracketTheme', $this->getConfig()->get('tournament_bracket_theme') ?: 'light');
    }

    public function registerAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->withMessage('noRights', 'danger')->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $tournamentId = (int)$this->getRequest()->getParam('id');
        $teamId = (int)$this->getRequest()->getPost('team_id');

        $tournamentMapper = new TournamentMapper();
        $teamMapper = new TeamMapper();
        $teamMemberMapper = new TeamMemberMapper();
        $registrationMapper = new TournamentTeamMapper();

        $tournament = $tournamentMapper->getById($tournamentId);
        if (!$tournament) {
            $this->redirect()->withMessage('noTournaments', 'danger')->to(['action' => 'index']);
        }

        if ($tournament['status'] !== Status::TOURNAMENT_REGISTRATION_OPEN) {
            $this->redirect()->withMessage('registrationClosed', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $team = $teamMapper->getById($teamId);
        if (!$team || !$teamMapper->isCaptain($teamId, (int)$this->getUser()->getId())) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $playerCount = $teamMemberMapper->countPlayers($teamId);
        if ($playerCount < (int)$tournament['team_size']) {
            $this->redirect()->withMessage('teamNotComplete', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        if ($registrationMapper->getAcceptedCount($tournamentId) >= (int)$tournament['max_teams']) {
            $this->redirect()->withMessage('registrationFull', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        if ($registrationMapper->isRegistered($tournamentId, $teamId)) {
            $this->redirect()->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $registrationMapper->registerAccepted($tournamentId, $teamId);

        $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $tournamentId]);
    }

    public function checkinAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->withMessage('noRights', 'danger')->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $tournamentId = (int)$this->getRequest()->getParam('id');
        $teamId = (int)$this->getRequest()->getPost('team_id');

        $tournamentMapper = new TournamentMapper();
        $teamMapper = new TeamMapper();
        $registrationMapper = new TournamentTeamMapper();

        $tournament = $tournamentMapper->getById($tournamentId);
        if (!$tournament) {
            $this->redirect()->withMessage('noTournaments', 'danger')->to(['action' => 'index']);
        }

        if ((int)$tournament['checkin_required'] !== 1) {
            $this->redirect()->withMessage('checkinNotRequired', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        if ($tournament['status'] !== Status::TOURNAMENT_REGISTRATION_OPEN) {
            $this->redirect()->withMessage('checkinClosedInfo', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $team = $teamMapper->getById($teamId);
        if (!$team || !$teamMapper->isCaptain($teamId, (int)$this->getUser()->getId())) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $registration = $registrationMapper->getByTournamentAndTeam($tournamentId, $teamId);
        if (!$registration || !in_array($registration['status'], ['accepted', 'checked_in'], true)) {
            $this->redirect()->withMessage('notRegistered', 'danger')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        if ($registration['status'] === 'checked_in') {
            $this->redirect()->withMessage('alreadyCheckedIn')->to(['action' => 'view', 'id' => $tournamentId]);
        }

        $registrationMapper->markCheckedIn($tournamentId, $teamId);
        $this->redirect()->withMessage('checkinSuccess')->to(['action' => 'view', 'id' => $tournamentId]);
    }
}
