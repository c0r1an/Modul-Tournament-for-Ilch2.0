<?php
/** @var \Ilch\View $this */
$tournament = $this->get('tournament');
$teams = $this->get('teams');
?>
<h1><?=$this->escape($tournament['title']) ?> - <?=$this->getTrans('teams') ?></h1>
<p><a class="btn btn-outline-secondary" href="<?=$this->getUrl(['action' => 'seed', 'id' => $tournament['id']]) ?>"><?=$this->getTrans('seedTeamsForTournament') ?></a></p>

<div class="card mb-3">
    <div class="card-header"><?=$this->getTrans('status') ?></div>
    <div class="card-body">
        <form method="POST" action="">
            <?=$this->getTokenField() ?>
            <div class="row">
                <div class="col-xl-4">
                    <select class="form-select" name="tournament_status">
                        <?php foreach (['draft', 'registration_open', 'registration_closed', 'running', 'finished', 'archived'] as $status): ?>
                            <option value="<?=$status ?>" <?=$tournament['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-xl-2">
                    <button class="btn btn-primary" type="submit"><?=$this->getTrans('save') ?></button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('team') ?></th>
                <th><?=$this->getTrans('seed') ?></th>
                <th><?=$this->getTrans('status') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($teams as $row): ?>
            <tr>
                <td><?=$this->escape($row['id']) ?></td>
                <td>
                    <form method="POST" action="" style="display:inline-block; margin-right:6px;">
                        <?=$this->getTokenField() ?>
                        <input type="hidden" name="set_status_id" value="<?=$row['id'] ?>">
                        <input type="hidden" name="set_status" value="accepted">
                        <button class="btn btn-sm btn-outline-success" type="submit"><?=$this->getTrans('accept') ?></button>
                    </form>
                    <form method="POST" action="" style="display:inline-block;">
                        <?=$this->getTokenField() ?>
                        <input type="hidden" name="set_status_id" value="<?=$row['id'] ?>">
                        <input type="hidden" name="set_status" value="rejected">
                        <button class="btn btn-sm btn-outline-danger" type="submit"><?=$this->getTrans('reject') ?></button>
                    </form>
                </td>
                <td><?=$this->escape($row['team_name']) ?></td>
                <td>
                    <form method="POST" action="" class="d-flex" style="gap:6px;">
                        <?=$this->getTokenField() ?>
                        <input type="hidden" name="row_id" value="<?=$row['id'] ?>">
                        <input class="form-control form-control-sm" type="number" min="1" name="seed" value="<?=$this->escape($row['seed']) ?>">
                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?=$this->getTrans('save') ?></button>
                    </form>
                </td>
                <td>
                    <?php
                    $statusClass = 'text-bg-secondary';
                    if ($row['status'] === 'accepted' || $row['status'] === 'checked_in') {
                        $statusClass = 'text-bg-success';
                    } elseif ($row['status'] === 'rejected') {
                        $statusClass = 'text-bg-danger';
                    }
                    ?>
                    <span class="badge <?=$statusClass ?>"><?=$this->getTrans($row['status']) ?></span>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<p><a class="btn btn-outline-primary" href="<?=$this->getUrl(['action' => 'bracket', 'id' => $tournament['id']]) ?>"><?=$this->getTrans('bracket') ?></a></p>
