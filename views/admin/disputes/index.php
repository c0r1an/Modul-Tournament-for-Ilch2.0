<?php
/** @var \Ilch\View $this */
$disputes = $this->get('disputes');
$selectedStatus = (string)($this->get('selectedStatus') ?? '');
$statusOptions = $this->get('statusOptions') ?: ['open', 'resolved', 'rejected'];
?>
<h1><?=$this->getTrans('disputes') ?></h1>

<div class="mb-3 d-flex flex-wrap gap-2 align-items-center">
    <span class="small text-muted me-1"><?=$this->getTrans('status') ?>:</span>
    <a class="btn btn-outline-secondary btn-sm <?=$selectedStatus === '' ? 'active' : '' ?>" href="<?=$this->getUrl(['action' => 'index']) ?>"><?=$this->getTrans('all') ?></a>
    <?php foreach ($statusOptions as $status): ?>
        <a class="btn btn-outline-secondary btn-sm <?=$selectedStatus === $status ? 'active' : '' ?>" href="<?=$this->getUrl(['action' => 'index', 'status' => $status]) ?>"><?=$this->getTrans($status) ?></a>
    <?php endforeach; ?>
</div>

<?php if ($disputes): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('matches') ?></th>
                <th><?=$this->getTrans('tournament') ?></th>
                <th><?=$this->getTrans('status') ?></th>
                <th><?=$this->getTrans('reason') ?></th>
                <th><?=$this->getTrans('createdAt') ?></th>
                <th><?=$this->getTrans('resolvedAt') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($disputes as $dispute): ?>
            <tr>
                <td><?=$dispute['id'] ?></td>
                <td>
                    <?=$this->getEditIcon(['action' => 'view', 'id' => $dispute['id']]) ?>
                    <?=$this->getDeleteIcon(['action' => 'del', 'id' => $dispute['id']]) ?>
                </td>
                <td><?=$this->getTrans('round') ?> <?=$dispute['round'] ?> / <?=$this->getTrans('match') ?> <?=$dispute['match_no'] ?></td>
                <td>#<?=$dispute['tournament_id'] ?></td>
                <td>
                    <?php
                    $statusClass = 'text-bg-secondary';
                    if ($dispute['status'] === 'open') {
                        $statusClass = 'text-bg-warning';
                    } elseif ($dispute['status'] === 'resolved') {
                        $statusClass = 'text-bg-success';
                    } elseif ($dispute['status'] === 'rejected') {
                        $statusClass = 'text-bg-danger';
                    }
                    ?>
                    <span class="badge <?=$statusClass ?>"><?=$this->getTrans($dispute['status']) ?></span>
                </td>
                <td>
                    <?php $reason = (string)$dispute['reason']; ?>
                    <?=$this->escape(strlen($reason) > 60 ? substr($reason, 0, 57) . '...' : $reason) ?>
                </td>
                <td><?=!empty($dispute['created_at']) ? $this->escape(date('d.m.Y H:i', strtotime($dispute['created_at']))) : '-' ?></td>
                <td><?=!empty($dispute['resolved_at']) ? $this->escape(date('d.m.Y H:i', strtotime($dispute['resolved_at']))) : '-' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p>-</p>
<?php endif; ?>
