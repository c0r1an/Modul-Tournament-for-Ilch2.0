<?php
/** @var \Ilch\View $this */
$dispute = $this->get('dispute');
$match = $this->get('match');
$reports = $this->get('reports');
$statusOptions = $this->get('statusOptions') ?: ['open', 'resolved', 'rejected'];
$latestReport = !empty($reports) ? $reports[0] : null;
$score1Value = $match['score1'] ?? ($latestReport['score1'] ?? '');
$score2Value = $match['score2'] ?? ($latestReport['score2'] ?? '');
?>
<h1><?=$this->getTrans('resolveDispute') ?> #<?=$dispute['id'] ?></h1>
<p><strong><?=$this->getTrans('match') ?>:</strong> #<?=$match['id'] ?> (<?=$this->getTrans('round') ?> <?=$match['round'] ?> / <?=$this->getTrans('match') ?> <?=$match['match_no'] ?>)</p>
<p><strong><?=$this->getTrans('status') ?>:</strong> <?=$this->getTrans($dispute['status']) ?></p>
<p><strong><?=$this->getTrans('reason') ?>:</strong> <?=$this->escape($dispute['reason']) ?></p>
<p><strong><?=$this->getTrans('createdAt') ?>:</strong> <?=!empty($dispute['created_at']) ? $this->escape(date('d.m.Y H:i', strtotime($dispute['created_at']))) : '-' ?></p>
<p><strong><?=$this->getTrans('resolvedAt') ?>:</strong> <?=!empty($dispute['resolved_at']) ? $this->escape(date('d.m.Y H:i', strtotime($dispute['resolved_at']))) : '-' ?></p>

<h4><?=$this->getTrans('reports') ?></h4>
<?php if ($reports): ?>
    <?php foreach ($reports as $report): ?>
        <div class="card mb-2">
            <div class="card-body">
                <p class="mb-1"><?=$this->getTrans('score') ?>: <?=$report['score1'] ?> : <?=$report['score2'] ?></p>
                <p class="mb-1"><?=$this->escape($report['comment']) ?></p>
                <?php if (!empty($report['evidence'])): ?>
                <ul>
                    <?php foreach ($report['evidence'] as $evidence): ?>
                        <li><?=$this->escape($evidence['type']) ?>: <?=$this->escape($evidence['path_or_url']) ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<h4><?=$this->getTrans('editDispute') ?></h4>
<form method="POST" action="">
    <?=$this->getTokenField() ?>
    <div class="mb-3">
        <label class="form-label" for="dispute_status"><?=$this->getTrans('status') ?></label>
        <select class="form-select" name="dispute_status" id="dispute_status">
            <?php foreach ($statusOptions as $status): ?>
                <option value="<?=$status ?>" <?=$dispute['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label" for="score1"><?=$this->getTrans('scoreTeam1') ?></label>
            <input class="form-control" type="number" min="0" name="score1" id="score1" value="<?=$this->escape((string)$score1Value) ?>">
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label" for="score2"><?=$this->getTrans('scoreTeam2') ?></label>
            <input class="form-control" type="number" min="0" name="score2" id="score2" value="<?=$this->escape((string)$score2Value) ?>">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label" for="resolution_note"><?=$this->getTrans('resolutionNote') ?></label>
        <textarea class="form-control" name="resolution_note" id="resolution_note" rows="4"><?=$this->escape((string)($dispute['resolution_note'] ?? '')) ?></textarea>
    </div>
    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
</form>

<hr>
<form method="POST" action="<?=$this->getUrl(['action' => 'del', 'id' => $dispute['id']]) ?>" onsubmit="return confirm('<?=$this->escape($this->getTrans('deleteDisputeConfirm')) ?>');">
    <?=$this->getTokenField() ?>
    <button class="btn btn-outline-danger" type="submit"><?=$this->getTrans('delete') ?></button>
</form>
