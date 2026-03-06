<?php

namespace Modules\Tournament\Controllers\Admin;

use Modules\Tournament\Libraries\Bracket;
use Modules\Tournament\Libraries\Status;
use Modules\Tournament\Mappers\AuditMapper;
use Modules\Tournament\Mappers\DisputeMapper;
use Modules\Tournament\Mappers\EvidenceMapper;
use Modules\Tournament\Mappers\MatchMapper;
use Modules\Tournament\Mappers\MatchReportMapper;

class Disputes extends \Ilch\Controller\Admin
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
                'active' => true,
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

        $this->getLayout()->addMenu('menuAdminTournament', $items);
    }

    public function indexAction()
    {
        $mapper = new DisputeMapper();
        $allowedStatuses = [Status::DISPUTE_OPEN, Status::DISPUTE_RESOLVED, Status::DISPUTE_REJECTED];
        $status = (string)$this->getRequest()->getParam('status');
        if (!in_array($status, $allowedStatuses, true)) {
            $status = '';
        }

        $this->getView()
            ->set('disputes', $mapper->getAll($status))
            ->set('selectedStatus', $status)
            ->set('statusOptions', $allowedStatuses);
    }

    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $disputeMapper = new DisputeMapper();
        $matchMapper = new MatchMapper();
        $reportMapper = new MatchReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $auditMapper = new AuditMapper();
        $allowedStatuses = [Status::DISPUTE_OPEN, Status::DISPUTE_RESOLVED, Status::DISPUTE_REJECTED];

        $dispute = $disputeMapper->getById($id);
        if (!$dispute) {
            $this->redirect()->to(['action' => 'index']);
        }

        $match = $matchMapper->getById((int)$dispute['match_id']);
        if (!$match) {
            $this->redirect()->to(['action' => 'index']);
        }

        if ($this->getRequest()->isPost()) {
            $newStatus = (string)$this->getRequest()->getPost('dispute_status');
            if (!in_array($newStatus, $allowedStatuses, true)) {
                $newStatus = Status::DISPUTE_OPEN;
            }

            $note = trim((string)$this->getRequest()->getPost('resolution_note'));
            $userId = (int)$this->getUser()->getId();

            if ($newStatus === Status::DISPUTE_RESOLVED) {
                $score1Raw = trim((string)$this->getRequest()->getPost('score1'));
                $score2Raw = trim((string)$this->getRequest()->getPost('score2'));
                $score1 = $score1Raw === '' ? -1 : (int)$score1Raw;
                $score2 = $score2Raw === '' ? -1 : (int)$score2Raw;

                if ($score1 < 0 || $score2 < 0 || ((int)$match['best_of'] === 1 && $score1 === $score2)) {
                    $this->redirect()->withMessage('invalidState', 'danger')->withInput()->to(['action' => 'view', 'id' => $id]);
                }

                $winnerTeamId = $score1 > $score2 ? (int)$match['team1_id'] : (int)$match['team2_id'];

                $matchMapper->update((int)$match['id'], [
                    'score1' => $score1,
                    'score2' => $score2,
                    'winner_team_id' => $winnerTeamId,
                    'status' => Status::MATCH_DONE,
                ]);

                (new Bracket())->propagateWinner((int)$match['id'], $winnerTeamId);

                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_RESOLVED,
                    'resolved_by_user_id' => $userId,
                    'resolution_note' => $note,
                    'resolved_at' => date('Y-m-d H:i:s'),
                ]);

                $auditMapper->log('match', (int)$match['id'], 'dispute_resolved', [
                    'dispute_id' => $id,
                    'score1' => $score1,
                    'score2' => $score2,
                    'winner_team_id' => $winnerTeamId,
                    'note' => $note,
                ], $userId);
            } elseif ($newStatus === Status::DISPUTE_REJECTED) {
                $latestReport = $reportMapper->getLatestByMatchId((int)$match['id']);
                if ($latestReport) {
                    $winnerTeamId = (int)$latestReport['winner_team_id'];
                    $matchMapper->update((int)$match['id'], [
                        'score1' => (int)$latestReport['score1'],
                        'score2' => (int)$latestReport['score2'],
                        'winner_team_id' => $winnerTeamId,
                        'status' => Status::MATCH_DONE,
                    ]);

                    if ($winnerTeamId > 0) {
                        (new Bracket())->propagateWinner((int)$match['id'], $winnerTeamId);
                    }
                }

                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_REJECTED,
                    'resolved_by_user_id' => $userId,
                    'resolution_note' => $note,
                    'resolved_at' => date('Y-m-d H:i:s'),
                ]);

                $auditMapper->log('match', (int)$match['id'], 'dispute_rejected', [
                    'dispute_id' => $id,
                    'note' => $note,
                ], $userId);
            } else {
                $disputeMapper->updateById($id, [
                    'status' => Status::DISPUTE_OPEN,
                    'resolved_by_user_id' => null,
                    'resolution_note' => $note,
                    'resolved_at' => null,
                ]);

                if ($match['status'] !== Status::MATCH_DONE) {
                    $matchMapper->update((int)$match['id'], ['status' => Status::MATCH_DISPUTE]);
                }

                $auditMapper->log('match', (int)$match['id'], 'dispute_reopened', [
                    'dispute_id' => $id,
                    'note' => $note,
                ], $userId);
            }

            $this->redirect()->withMessage('saveSuccess')->to(['action' => 'view', 'id' => $id]);
        }

        $reports = $reportMapper->getByMatchId((int)$match['id']);
        foreach ($reports as &$report) {
            $report['evidence'] = $evidenceMapper->getByReportId((int)$report['id']);
        }

        $this->getView()
            ->set('dispute', $dispute)
            ->set('match', $match)
            ->set('reports', $reports)
            ->set('statusOptions', $allowedStatuses);
    }

    public function delAction()
    {
        if ($this->getRequest()->isSecure()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id > 0) {
                $mapper = new DisputeMapper();
                $mapper->delete($id);
                $this->addMessage('deleteSuccess');
            }
        }

        $this->redirect(['action' => 'index']);
    }
}
