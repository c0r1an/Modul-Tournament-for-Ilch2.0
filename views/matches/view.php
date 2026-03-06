<?php
/** @var \Ilch\View $this */
$match = $this->get('match');
$reports = $this->get('reports');
$disputes = $this->get('disputes');
$teamMapper = new \Modules\Tournament\Mappers\TeamMapper();
$team1 = !empty($match['team1_id']) ? $teamMapper->getById((int)$match['team1_id']) : null;
$team2 = !empty($match['team2_id']) ? $teamMapper->getById((int)$match['team2_id']) : null;
$user = $this->getUser();
$userId = $user ? (int)$user->getId() : 0;
$hasReportPermission = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_report'));
$hasDisputePermission = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_report') || $user->hasAccess('tournament_dispute'));
$isCaptainTeam1 = !empty($match['team1_id']) && $teamMapper->isCaptain((int)$match['team1_id'], $userId);
$isCaptainTeam2 = !empty($match['team2_id']) && $teamMapper->isCaptain((int)$match['team2_id'], $userId);
$canReport = $hasReportPermission && ($isCaptainTeam1 || $isCaptainTeam2);
$canOpenDispute = $hasDisputePermission && ($isCaptainTeam1 || $isCaptainTeam2) && $match['status'] === 'reported';
$canManageTeams = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_team_manage'));
$team1Name = !empty($team1['name']) ? $team1['name'] : $this->getTrans('tbd');
$team2Name = !empty($team2['name']) ? $team2['name'] : $this->getTrans('tbd');
$team1Tag = !empty($team1['tag']) ? $team1['tag'] : '-';
$team2Tag = !empty($team2['tag']) ? $team2['tag'] : '-';
$team1Initial = strtoupper(substr($team1Name, 0, 1));
$team2Initial = strtoupper(substr($team2Name, 0, 1));
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl('application/modules/tournament/static/css/match-view.css') ?>">

<div class="tk-match-view">
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']) ?>"><?=$this->getTrans('menuTournament') ?></a>
        <?php if ($canManageTeams): ?>
            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
            <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a>
        <?php endif; ?>
    </div>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->escape($this->getTrans('matchNumber', (string)$match['id'])) ?></div>
        <div class="card-body">
            <div class="row align-items-center g-3 mb-3">
                <div class="col-md-5">
                    <?php if (!empty($team1['id'])): ?>
                        <a class="tk-team-box" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team1['id']]) ?>">
                    <?php else: ?>
                        <div class="tk-team-box">
                    <?php endif; ?>
                            <div class="tk-team-logo">
                                <?php if (!empty($team1['logo'])): ?>
                                    <img src="<?=$this->getBaseUrl($team1['logo']) ?>" alt="<?=$this->escape($team1Name) ?>">
                                <?php else: ?>
                                    <span><?=$this->escape($team1Initial) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="tk-team-text">
                                <div class="tk-team-name"><?=$this->escape($team1Name) ?></div>
                                <div class="tk-team-tag"><?=$this->getTrans('tag') ?>: <?=$this->escape($team1Tag) ?></div>
                            </div>
                    <?php if (!empty($team1['id'])): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="col-md-2 text-center">
                    <span class="tk-vs-badge"><?=$this->getTrans('vs') ?></span>
                </div>

                <div class="col-md-5">
                    <?php if (!empty($team2['id'])): ?>
                        <a class="tk-team-box" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => (int)$team2['id']]) ?>">
                    <?php else: ?>
                        <div class="tk-team-box">
                    <?php endif; ?>
                            <div class="tk-team-logo">
                                <?php if (!empty($team2['logo'])): ?>
                                    <img src="<?=$this->getBaseUrl($team2['logo']) ?>" alt="<?=$this->escape($team2Name) ?>">
                                <?php else: ?>
                                    <span><?=$this->escape($team2Initial) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="tk-team-text">
                                <div class="tk-team-name"><?=$this->escape($team2Name) ?></div>
                                <div class="tk-team-tag"><?=$this->getTrans('tag') ?>: <?=$this->escape($team2Tag) ?></div>
                            </div>
                    <?php if (!empty($team2['id'])): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row g-2">
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('round') ?></div>
                        <div class="tk-meta-value"><?=$this->escape($match['round']) ?> / <?=$this->escape($match['match_no']) ?></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('status') ?></div>
                        <div class="tk-meta-value"><span class="badge text-bg-secondary"><?=$this->getTrans($match['status']) ?></span></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('map') ?></div>
                        <div class="tk-meta-value"><?=$this->escape($match['map'] ?: '-') ?></div>
                    </div>
                </div>
                <div class="col-6 col-xl-3">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('bestOf') ?></div>
                        <div class="tk-meta-value"><?=$this->escape($match['best_of']) ?></div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="tk-meta-item">
                        <div class="tk-meta-label"><?=$this->getTrans('scheduledAt') ?></div>
                        <div class="tk-meta-value"><?=!empty($match['scheduled_at']) ? $this->escape(date('d.m.Y H:i', strtotime($match['scheduled_at']))) : '-' ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (($match['status'] === 'ready' || $match['status'] === 'scheduled') && $canReport): ?>
    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('reportResult') ?></div>
        <div class="card-body">
            <form method="POST" action="<?=$this->getUrl(['action' => 'report', 'id' => $match['id']]) ?>" enctype="multipart/form-data">
                <?=$this->getTokenField() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="score1"><?=$this->getTrans('scoreTeam1') ?></label>
                        <input class="form-control" type="number" min="0" name="score1" id="score1" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="score2"><?=$this->getTrans('scoreTeam2') ?></label>
                        <input class="form-control" type="number" min="0" name="score2" id="score2" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="comment"><?=$this->getTrans('comment') ?></label>
                    <textarea class="form-control" name="comment" id="comment" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label" for="evidence_files"><?=$this->getTrans('evidenceUpload') ?></label>
                    <input class="form-control" type="file" name="evidence_files[]" id="evidence_files" multiple accept=".png,.jpg,.jpeg,.webp,.pdf">
                </div>
                <div class="mb-3">
                    <label class="form-label" for="evidence_links"><?=$this->getTrans('evidenceLinks') ?></label>
                    <textarea class="form-control" name="evidence_links" id="evidence_links" rows="2" placeholder="https://..."></textarea>
                </div>
                <button class="btn btn-primary" type="submit"><?=$this->getTrans('reportResult') ?></button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($match['status'] === 'reported'): ?>
    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('disputes') ?></div>
        <div class="card-body">
            <form class="d-inline-block me-2 mb-3" method="POST" action="<?=$this->getUrl(['action' => 'confirm', 'id' => $match['id']]) ?>">
                <?=$this->getTokenField() ?>
                <button class="btn btn-success" type="submit"><?=$this->getTrans('confirmResult') ?></button>
            </form>
            <?php if ($canOpenDispute): ?>
                <form method="POST" action="<?=$this->getUrl(['action' => 'dispute', 'id' => $match['id']]) ?>">
                    <?=$this->getTokenField() ?>
                    <div class="mb-3">
                        <label class="form-label" for="reason"><?=$this->getTrans('reason') ?></label>
                        <textarea class="form-control" name="reason" id="reason" rows="3" required></textarea>
                    </div>
                    <button class="btn btn-danger" type="submit"><?=$this->getTrans('openDispute') ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('reports') ?></div>
        <div class="card-body">
            <?php if ($reports): ?>
                <?php foreach ($reports as $report): ?>
                    <div class="card tk-sub-card rounded-0 mb-2">
                        <div class="card-body">
                            <p class="mb-1"><strong><?=$this->getTrans('score') ?>:</strong> <?=$this->escape($report['score1']) ?> : <?=$this->escape($report['score2']) ?></p>
                            <p class="mb-2"><strong><?=$this->getTrans('comment') ?>:</strong> <?=$this->escape($report['comment'] ?: '-') ?></p>
                            <?php if (!empty($report['evidence'])): ?>
                                <p class="mb-1"><strong><?=$this->getTrans('evidence') ?>:</strong></p>
                                <ul class="mb-0">
                                    <?php foreach ($report['evidence'] as $evidence): ?>
                                        <li>
                                            <?php if ($evidence['type'] === 'link'): ?>
                                                <a href="<?=$this->escape($evidence['path_or_url']) ?>" target="_blank" rel="noopener noreferrer"><?=$this->escape($evidence['path_or_url']) ?></a>
                                            <?php else: ?>
                                                <a href="<?=$this->getBaseUrl($evidence['path_or_url']) ?>" target="_blank" rel="noopener noreferrer"><?=$this->escape($evidence['path_or_url']) ?></a>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">-</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card tk-card rounded-0 mb-3">
        <div class="card-header"><?=$this->getTrans('disputes') ?></div>
        <div class="card-body">
            <?php if ($disputes): ?>
                <?php foreach ($disputes as $dispute): ?>
                    <div class="card tk-sub-card rounded-0 mb-2">
                        <div class="card-body">
                            <div><strong>#<?=$this->escape($dispute['id']) ?></strong></div>
                            <div><strong><?=$this->getTrans('status') ?>:</strong> <?=$this->escape($this->getTrans($dispute['status'])) ?></div>
                            <div><strong><?=$this->getTrans('reason') ?>:</strong> <?=$this->escape($dispute['reason']) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mb-0">-</p>
            <?php endif; ?>
        </div>
    </div>
</div>