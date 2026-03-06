<?php
/** @var \Ilch\View $this */
$tournaments = $this->get('tournaments');
?>
<link href="<?=$this->getBoxUrl('static/css/nextmatches-box.css') ?>" rel="stylesheet">
<h5 class="mb-2"><?=$this->getTrans('runningTournaments') ?></h5>

<?php if ($tournaments): ?>
    <div class="tk-module-box-list">
        <?php foreach ($tournaments as $tournament): ?>
            <a class="tk-box-link" href="<?=$this->getUrl('tournament/tournaments/view/id/' . (int)$tournament['id']) ?>">
                <div class="card tk-module-card rounded-0">
                    <div class="card-body">
                        <div class="tk-box-headline">
                            <?=!empty($tournament['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($tournament['start_at']))) : '-' ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="tk-team-tag"><?=$this->escape($tournament['title']) ?></div>
                            <span class="tk-nextmatch-vs"><?=$this->getTrans('running') ?></span>
                        </div>

                        <div class="tk-nextmatch-meta">
                            <div><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($tournament['game']) ?></div>
                            <div><strong><?=$this->getTrans('startAt') ?>:</strong> <?=!empty($tournament['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($tournament['start_at']))) : '-' ?></div>
                            <div><strong><?=$this->getTrans('status') ?>:</strong> <?=$this->getTrans('running') ?></div>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p class="mb-0"><?=$this->getTrans('noRunningTournaments') ?></p>
<?php endif; ?>
