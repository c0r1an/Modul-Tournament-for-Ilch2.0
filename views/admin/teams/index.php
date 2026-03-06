<?php
/** @var \Ilch\View $this */
$teams = $this->get('teams');
?>
<h1><?=$this->getTrans('teams') ?></h1>

<?php if ($teams): ?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th><?=$this->getTrans('id') ?></th>
                <th><?=$this->getTrans('actions') ?></th>
                <th><?=$this->getTrans('logo') ?></th>
                <th><?=$this->getTrans('title') ?></th>
                <th><?=$this->getTrans('tag') ?></th>
                <th><?=$this->getTrans('captain') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($teams as $team): ?>
                <tr>
                    <td><?=$team['id'] ?></td>
                    <td>
                        <?=$this->getEditIcon(['action' => 'treat', 'id' => $team['id']]) ?>
                        <?=$this->getDeleteIcon(['action' => 'del', 'id' => $team['id']]) ?>
                    </td>
                    <td>
                        <?php if (!empty($team['logo'])): ?>
                            <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->getTrans('teamLogoAlt') ?>" style="width:44px;height:44px;object-fit:cover;">
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </td>
                    <td><?=$this->escape($team['name']) ?></td>
                    <td><?=$this->escape($team['tag']) ?></td>
                    <td><?=$this->getTrans('user') ?> #<?=$this->escape($team['captain_user_id']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
    <p>-</p>
<?php endif; ?>
