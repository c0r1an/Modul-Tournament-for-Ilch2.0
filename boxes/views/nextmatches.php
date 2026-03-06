<?php
/** @var \Ilch\View $this */
$matches = $this->get('matches');

$getStageLabel = function (array $match): string {
    $round = (int)($match['round'] ?? 0);
    $maxRound = (int)($match['max_round'] ?? 0);

    if ($round <= 0 || $maxRound <= 0) {
        return $this->getTrans('round') . ' ' . max(1, $round);
    }

    if ($round === $maxRound) {
        return $this->getTrans('final');
    }

    if ($round === $maxRound - 1) {
        return $this->getTrans('semifinal');
    }

    if ($round === $maxRound - 2) {
        return $this->getTrans('quarterfinal');
    }

    return $this->getTrans('round') . ' ' . $round;
};
?>
<link href="<?=$this->getBoxUrl('static/css/nextmatches-box.css') ?>" rel="stylesheet">
<h5 class="mb-2"><?=$this->getTrans('nextMatches') ?></h5>

<?php if ($matches): ?>
    <div class="tk-module-box-list">
        <?php foreach ($matches as $match): ?>
            <a class="tk-box-link" href="<?=$this->getUrl('tournament/matches/view/id/' . (int)$match['id']) ?>">
                <div class="card tk-module-card rounded-0">
                    <div class="card-body">
                        <div class="tk-box-headline">
                            <?=!empty($match['scheduled_at']) ? $this->escape(date('d.m.Y H:i', strtotime($match['scheduled_at']))) : '-' ?>
                        </div>

                        <div class="tk-nextmatch-grid">
                            <div class="tk-team tk-team-left">
                                <div class="tk-team-logo">
                                    <?php if (!empty($match['team1_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['team1_logo']) ?>" alt="<?=$this->escape($match['team1_tag'] ?: $this->getTrans('teamOne')) ?>">
                                    <?php else: ?>
                                        <span class="tk-logo-fallback"><?=$this->escape($match['team1_tag'] ?: 'T1') ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="tk-team-tag"><?=$this->escape($match['team1_tag'] ?: $this->getTrans('tbd')) ?></div>
                            </div>

                            <div class="tk-nextmatch-vs"><?=$this->getTrans('vs') ?></div>

                            <div class="tk-team tk-team-right">
                                <div class="tk-team-tag"><?=$this->escape($match['team2_tag'] ?: $this->getTrans('tbd')) ?></div>
                                <div class="tk-team-logo">
                                    <?php if (!empty($match['team2_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['team2_logo']) ?>" alt="<?=$this->escape($match['team2_tag'] ?: $this->getTrans('teamTwo')) ?>">
                                    <?php else: ?>
                                        <span class="tk-logo-fallback"><?=$this->escape($match['team2_tag'] ?: 'T2') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="tk-nextmatch-meta">
                            <div><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($match['tournament_game'] ?: '-') ?></div>
                            <div><strong><?=$this->getTrans('tournament') ?>:</strong> <?=$this->escape($match['tournament_title'] ?: '-') ?></div>
                            <div><strong><?=$this->getTrans('tournamentStage') ?>:</strong> <?=$this->escape($getStageLabel($match)) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="mb-0"><?=$this->getTrans('noUpcomingMatches') ?></p>
<?php endif; ?>
