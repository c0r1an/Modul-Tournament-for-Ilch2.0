<?php

namespace Modules\Tournament\Controllers;

use Modules\Tournament\Libraries\Bracket;
use Modules\Tournament\Libraries\EvidenceUploader;
use Modules\Tournament\Libraries\Status;
use Modules\Tournament\Mappers\AuditMapper;
use Modules\Tournament\Mappers\DisputeMapper;
use Modules\Tournament\Mappers\EvidenceMapper;
use Modules\Tournament\Mappers\MatchMapper;
use Modules\Tournament\Mappers\MatchReportMapper;
use Modules\Tournament\Mappers\TeamMapper;
use Modules\Tournament\Mappers\TournamentMapper;

class Matches extends \Ilch\Controller\Frontend
{
    public function viewAction()
    {
        $id = (int)$this->getRequest()->getParam('id');

        $matchMapper = new MatchMapper();
        $reportMapper = new MatchReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $disputeMapper = new DisputeMapper();
        $tournamentMapper = new TournamentMapper();

        $match = $matchMapper->getById($id);
        if (!$match) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }
        $tournament = $tournamentMapper->getById((int)$match['tournament_id']);

        $hmenu = $this->getLayout()->getHmenu()
            ->add($this->getTranslator()->trans('menuTournament'), ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        if ($tournament) {
            $hmenu->add($tournament['title'], ['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'view', 'id' => (int)$tournament['id']]);
        }
        $hmenu->add($this->getTranslator()->trans('matchNumber', $id));

        $reports = $reportMapper->getByMatchId($id);
        foreach ($reports as &$report) {
            $report['evidence'] = $evidenceMapper->getByReportId((int)$report['id']);
        }

        $this->getView()
            ->set('match', $match)
            ->set('reports', $reports)
            ->set('disputes', $disputeMapper->getByMatchId($id));
    }

    public function reportAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');

        $matchMapper = new MatchMapper();
        $reportMapper = new MatchReportMapper();
        $evidenceMapper = new EvidenceMapper();
        $teamMapper = new TeamMapper();
        $auditMapper = new AuditMapper();
        $uploader = new EvidenceUploader();

        $match = $matchMapper->getById($matchId);
        if (!$match) {
            $this->redirect()->to(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']);
        }

        if (!in_array($match['status'], [Status::MATCH_READY, Status::MATCH_SCHEDULED], true)) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$this->getUser()->getId();
        $isCaptainTeam1 = !empty($match['team1_id']) && $teamMapper->isCaptain((int)$match['team1_id'], $userId);
        $isCaptainTeam2 = !empty($match['team2_id']) && $teamMapper->isCaptain((int)$match['team2_id'], $userId);
        if (!$isCaptainTeam1 && !$isCaptainTeam2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $score1 = (int)$this->getRequest()->getPost('score1');
        $score2 = (int)$this->getRequest()->getPost('score2');

        if ($score1 < 0 || $score2 < 0 || ((int)$match['best_of'] === 1 && $score1 === $score2)) {
            $this->redirect()->withMessage('invalidState', 'danger')->withInput()->to(['action' => 'view', 'id' => $matchId]);
        }

        $winnerTeamId = $score1 > $score2 ? (int)$match['team1_id'] : (int)$match['team2_id'];
        $reportedByTeamId = $isCaptainTeam1 ? (int)$match['team1_id'] : (int)$match['team2_id'];

        $reportId = $reportMapper->create([
            'match_id' => $matchId,
            'reported_by_team_id' => $reportedByTeamId,
            'score1' => $score1,
            'score2' => $score2,
            'winner_team_id' => $winnerTeamId,
            'comment' => trim((string)$this->getRequest()->getPost('comment')),
        ]);

        $uploadedFiles = $uploader->upload($_FILES['evidence_files'] ?? [], (int)$match['tournament_id'], $matchId);
        foreach ($uploadedFiles as $path) {
            $evidenceMapper->add([
                'match_report_id' => $reportId,
                'type' => 'screenshot',
                'path_or_url' => $path,
                'note' => null,
            ]);
        }

        $linkLines = preg_split('/\r\n|\r|\n/', (string)$this->getRequest()->getPost('evidence_links'));
        foreach ($linkLines as $link) {
            $link = trim($link);
            if ($link !== '') {
                $evidenceMapper->add([
                    'match_report_id' => $reportId,
                    'type' => 'link',
                    'path_or_url' => $link,
                    'note' => null,
                ]);
            }
        }

        $matchMapper->update($matchId, ['status' => Status::MATCH_REPORTED]);

        $auditMapper->log('match', $matchId, 'reported', [
            'score1' => $score1,
            'score2' => $score2,
            'winner_team_id' => $winnerTeamId,
            'report_id' => $reportId,
        ], $userId);

        $this->redirect()->withMessage('resultSubmitted')->to(['action' => 'view', 'id' => $matchId]);
    }

    public function confirmAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');
        $matchMapper = new MatchMapper();
        $reportMapper = new MatchReportMapper();
        $teamMapper = new TeamMapper();
        $auditMapper = new AuditMapper();

        $match = $matchMapper->getById($matchId);
        $report = $reportMapper->getLatestByMatchId($matchId);

        if (!$match || !$report || $match['status'] !== Status::MATCH_REPORTED) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$this->getUser()->getId();
        $isCaptainTeam1 = !empty($match['team1_id']) && $teamMapper->isCaptain((int)$match['team1_id'], $userId);
        $isCaptainTeam2 = !empty($match['team2_id']) && $teamMapper->isCaptain((int)$match['team2_id'], $userId);

        if (!$isCaptainTeam1 && !$isCaptainTeam2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        if ((int)$report['reported_by_team_id'] === (int)$match['team1_id'] && !$isCaptainTeam2) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }
        if ((int)$report['reported_by_team_id'] === (int)$match['team2_id'] && !$isCaptainTeam1) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $matchMapper->update($matchId, [
            'score1' => (int)$report['score1'],
            'score2' => (int)$report['score2'],
            'winner_team_id' => (int)$report['winner_team_id'],
            'status' => Status::MATCH_DONE,
        ]);

        (new Bracket())->propagateWinner($matchId, (int)$report['winner_team_id']);

        $auditMapper->log('match', $matchId, 'confirmed', [
            'score1' => (int)$report['score1'],
            'score2' => (int)$report['score2'],
            'winner_team_id' => (int)$report['winner_team_id'],
            'report_id' => (int)$report['id'],
        ], $userId);

        $this->redirect()->withMessage('resultConfirmed')->to(['action' => 'view', 'id' => $matchId]);
    }

    public function disputeAction()
    {
        if (!$this->getUser()) {
            $this->redirect()->to(['module' => 'user', 'controller' => 'login', 'action' => 'index']);
        }

        $matchId = (int)$this->getRequest()->getParam('id');
        $matchMapper = new MatchMapper();
        $reportMapper = new MatchReportMapper();
        $teamMapper = new TeamMapper();
        $disputeMapper = new DisputeMapper();

        $match = $matchMapper->getById($matchId);
        $report = $reportMapper->getLatestByMatchId($matchId);

        if (!$match || !$report || $match['status'] !== Status::MATCH_REPORTED) {
            $this->redirect()->withMessage('invalidState', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $userId = (int)$this->getUser()->getId();
        $hasDisputePermission = $this->getUser()->isAdmin()
            || $this->getUser()->hasAccess('module_tournament')
            || $this->getUser()->hasAccess('tournament_report')
            || $this->getUser()->hasAccess('tournament_dispute');
        if (!$hasDisputePermission) {
            $this->redirect()->withMessage('noRights', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $isCaptainTeam1 = !empty($match['team1_id']) && $teamMapper->isCaptain((int)$match['team1_id'], $userId);
        $isCaptainTeam2 = !empty($match['team2_id']) && $teamMapper->isCaptain((int)$match['team2_id'], $userId);

        if (!$isCaptainTeam1 && !$isCaptainTeam2) {
            $this->redirect()->withMessage('notCaptain', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $openedByTeam = $isCaptainTeam1 ? (int)$match['team1_id'] : (int)$match['team2_id'];

        $reason = trim((string)$this->getRequest()->getPost('reason'));
        if ($reason === '') {
            $this->redirect()->withMessage('emptyMessage', 'danger')->to(['action' => 'view', 'id' => $matchId]);
        }

        $disputeMapper->create([
            'match_id' => $matchId,
            'opened_by_team_id' => $openedByTeam,
            'reason' => $reason,
            'status' => Status::DISPUTE_OPEN,
        ]);

        $matchMapper->update($matchId, ['status' => Status::MATCH_DISPUTE]);

        $this->redirect()->withMessage('disputeCreated')->to(['action' => 'view', 'id' => $matchId]);
    }
}
