<?php

namespace Modules\Tournament\Controllers\Admin;

use Modules\Tournament\Libraries\Bracket;
use Modules\Tournament\Libraries\Status;
use Modules\Tournament\Mappers\MatchMapper;
use Modules\Tournament\Mappers\MatchReportMapper;
use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TeamMemberMapper;
use Modules\Tournament\Mappers\TournamentMapper;
use Modules\Tournament\Mappers\TournamentTeamMapper;

class Tournaments extends \Ilch\Controller\Admin
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
                'active' => false,
                'icon' => 'fa-solid fa-users',
                'url' => $this->getLayout()->getUrl(['controller' => 'teams', 'action' => 'index'])
            ],
            [
                'name' => 'disputes',
                'active' => false,
                'icon' => 'fa-solid fa-gavel',
                'url' => $this->getLayout()->getUrl(['controller' => 'disputes', 'action' => 'index'])
            ],
            [
                'name' => 'settings',
                'active' => false,
                'icon' => 'fa-solid fa-gears',
                'url' => $this->getLayout()->getUrl(['controller' => 'settings', 'action' => 'index'])
            ]
        ];

        if ($this->getRequest()->getActionName() === 'treat') {
            $items[0][0]['active'] = true;
        } else {
            $items[0]['active'] = true;
        }

        $this->getLayout()->addMenu('menuAdminTournament', $items);
    }

    public function indexAction()
    {
        $mapper = new TournamentMapper();

        $this->getLayout()->getAdminHmenu()->add($this->getTranslator()->trans('menuAdminTournament'));

        $this->getView()->set('tournaments', $mapper->getAll([], ['id' => 'DESC']));
    }

    public function seedAction()
    {
        $tournamentId = (int)$this->getRequest()->getParam('id');

        $teamMapper = new TeamMapper();
        $teamMemberMapper = new TeamMemberMapper();
        $tournamentMapper = new TournamentMapper();
        $registrationMapper = new TournamentTeamMapper();

        $tournament = null;
        if ($tournamentId > 0) {
            $tournament = $tournamentMapper->getById($tournamentId);
            if (!$tournament) {
                $this->redirect()->withMessage('noTournaments', 'danger')->to(['action' => 'index']);
            }
        }

        $createdTeams = 0;
        $captainUserId = (int)$this->getUser()->getId();
        $ts = date('YmdHis');

        for ($i = 1; $i <= 8; $i++) {
            $teamName = 'Test Team ' . $i . ' ' . $ts;
            $teamId = $teamMapper->save([
                'name' => $teamName,
                'tag' => 'T' . $i,
                'captain_user_id' => $captainUserId,
                'contact_discord' => 'test_team_' . $i . '#000' . $i,
                'contact_email' => 'testteam' . $i . '@example.test',
            ]);

            $teamMemberMapper->add([
                'team_id' => $teamId,
                'user_id' => $captainUserId,
                'nickname' => 'Captain ' . $i,
                'role' => 'captain',
            ]);

            for ($p = 2; $p <= 4; $p++) {
                $teamMemberMapper->add([
                    'team_id' => $teamId,
                    'user_id' => null,
                    'nickname' => 'Player ' . $i . '-' . $p,
                    'role' => 'member',
                ]);
            }

            if ($tournament && $tournament['status'] === Status::TOURNAMENT_REGISTRATION_OPEN && !$registrationMapper->isRegistered($tournamentId, $teamId)) {
                if ($registrationMapper->getAcceptedCount($tournamentId) < (int)$tournament['max_teams']) {
                    $registrationMapper->registerAccepted($tournamentId, $teamId);
                }
            }

            $createdTeams++;
        }

        $this->redirect()->withMessage($this->getTranslator()->trans('seedDone', $createdTeams))->to(
            $tournamentId > 0 ? ['action' => 'teams', 'id' => $tournamentId] : ['action' => 'index']
        );
    }

    public function treatAction()
    {
        $mapper = new TournamentMapper();
        $id = (int)$this->getRequest()->getParam('id');
        $entry = $id ? $mapper->getById($id) : null;

        if ($id && !$entry) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            $allowedMaxTeams = [2, 4, 8, 16, 32, 64, 128];
            $startAt = trim((string)$this->getRequest()->getPost('start_at'));
            if ($startAt !== '') {
                $startAt = str_replace('T', ' ', $startAt);
                if (strlen($startAt) === 16) {
                    $startAt .= ':00';
                }
            }

            $maxTeams = (int)$this->getRequest()->getPost('max_teams');
            if (!in_array($maxTeams, $allowedMaxTeams, true)) {
                $maxTeams = 8;
            }

            $payload = [
                'title' => trim((string)$this->getRequest()->getPost('title')),
                'slug' => trim((string)$this->getRequest()->getPost('slug')),
                'banner' => trim((string)$this->getRequest()->getPost('banner')),
                'game' => trim((string)$this->getRequest()->getPost('game')),
                'mode' => 'single_elimination',
                'team_size' => max(1, (int)$this->getRequest()->getPost('team_size')),
                'max_teams' => $maxTeams,
                'start_at' => $startAt,
                'checkin_required' => (int)$this->getRequest()->getPost('checkin_required') === 1 ? 1 : 0,
                'rules' => (string)$this->getRequest()->getPost('rules'),
                'status' => (string)$this->getRequest()->getPost('status') ?: Status::TOURNAMENT_DRAFT,
                'created_by' => $entry['created_by'] ?? (int)$this->getUser()->getId(),
            ];

            $savedId = $mapper->save($payload, $id ?: null);
            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'teams', 'id' => $savedId]);
        }

        $this->getView()->set('entry', $entry);
    }

    public function teamsAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $tournamentMapper = new TournamentMapper();
        $registrationMapper = new TournamentTeamMapper();

        $tournament = $tournamentMapper->getById($id);
        if (!$tournament) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('row_id') && $this->getRequest()->getPost('seed') !== null) {
                $registrationMapper->updateSeed((int)$this->getRequest()->getPost('row_id'), (int)$this->getRequest()->getPost('seed'));
            }

            if ($this->getRequest()->getPost('set_status_id') && $this->getRequest()->getPost('set_status')) {
                $registrationMapper->updateStatus((int)$this->getRequest()->getPost('set_status_id'), (string)$this->getRequest()->getPost('set_status'));
            }

            if ($this->getRequest()->getPost('tournament_status')) {
                $tournamentMapper->setStatus($id, (string)$this->getRequest()->getPost('tournament_status'));
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'teams', 'id' => $id]);
        }

        $this->getView()->set('tournament', $tournament)->set('teams', $registrationMapper->getByTournamentId($id));
    }

    public function bracketAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $tournamentMapper = new TournamentMapper();
        $registrationMapper = new TournamentTeamMapper();
        $matchMapper = new MatchMapper();
        $matchReportMapper = new MatchReportMapper();

        $tournament = $tournamentMapper->getById($id);
        if (!$tournament) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            if ($this->getRequest()->getPost('generate')) {
                $participants = ((int)$tournament['checkin_required'] === 1)
                    ? $registrationMapper->getCheckedInByTournamentId($id)
                    : $registrationMapper->getAcceptedByTournamentId($id);

                $teamIds = array_map(function ($row) {
                    return (int)$row['team_id'];
                }, $participants);

                if (!Status::isPowerOfTwo(count($teamIds))) {
                    $messageKey = ((int)$tournament['checkin_required'] === 1) ? 'insufficientCheckedInTeams' : 'insufficientTeams';
                    $this->redirect()->withMessage($messageKey, 'danger')->to(['action' => 'bracket', 'id' => $id]);
                }

                if ((new Bracket())->generate($id, $teamIds)) {
                    $tournamentMapper->setStatus($id, Status::TOURNAMENT_RUNNING);
                }
            }

            if ($this->getRequest()->getPost('match_id')) {
                $matchId = (int)$this->getRequest()->getPost('match_id');
                $newStatus = trim((string)$this->getRequest()->getPost('status')) ?: Status::MATCH_PENDING;
                $matchMapper->update($matchId, [
                    'map' => trim((string)$this->getRequest()->getPost('map')),
                    'best_of' => max(1, (int)$this->getRequest()->getPost('best_of')),
                    'scheduled_at' => trim((string)$this->getRequest()->getPost('scheduled_at')) ?: null,
                    'status' => $newStatus,
                ]);

                // If an admin manually confirms/completes a match, ensure winner propagation happens.
                if (in_array($newStatus, [Status::MATCH_CONFIRMED, Status::MATCH_DONE], true)) {
                    $match = $matchMapper->getById($matchId);
                    if ($match) {
                        $winnerTeamId = (int)($match['winner_team_id'] ?? 0);

                        if ($winnerTeamId === 0) {
                            $latestReport = $matchReportMapper->getLatestByMatchId($matchId);
                            if ($latestReport) {
                                $winnerTeamId = (int)$latestReport['winner_team_id'];
                                $matchMapper->update($matchId, [
                                    'score1' => (int)$latestReport['score1'],
                                    'score2' => (int)$latestReport['score2'],
                                    'winner_team_id' => $winnerTeamId,
                                ]);
                            }
                        }

                        if ($winnerTeamId > 0) {
                            (new Bracket())->propagateWinner($matchId, $winnerTeamId);
                        }
                    }
                }
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'bracket', 'id' => $id]);
        }

        $this->getView()
            ->set('tournament', $tournament)
            ->set('rounds', $matchMapper->getGroupedByRound($id))
            ->set('bracketTheme', 'light');
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id > 0) {
                $mapper = new TournamentMapper();
                $mapper->delete($id);
                $this->addMessage('deleteSuccess');
            }
        }

        $this->redirect(['action' => 'index']);
    }
}
