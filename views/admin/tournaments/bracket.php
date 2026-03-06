<?php
/** @var \Ilch\View $this */
$tournament = $this->get('tournament');
$rounds = $this->get('rounds');
$bracketTheme = $this->get('bracketTheme') ?: 'light';
$roundKeys = array_keys($rounds ?: []);
sort($roundKeys);
$layout = [];
$cardHeight = 104;
$groupGap = 20;
$basePairGap = 12;
$prevPairGap = $basePairGap;
$prevOffset = 0;
foreach ($roundKeys as $rk) {
    $roundNo = (int)$rk;
    if ($roundNo === 1) {
        $pairGap = $basePairGap;
        $offset = 0;
    } else {
        $pairGap = $cardHeight + $prevPairGap + $groupGap;
        $offset = $prevOffset + (int)($cardHeight / 2 + $prevPairGap / 2);
    }
    $layout[$roundNo] = ['pairGap' => $pairGap, 'offset' => $offset];
    $prevPairGap = $pairGap;
    $prevOffset = $offset;
}
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl('application/modules/tournament/static/css/bracket.css') ?>">
<h1><?=$this->escape($tournament['title']) ?> - <?=$this->getTrans('bracket') ?></h1>

<form method="POST" action="" class="mb-3">
    <?=$this->getTokenField() ?>
    <button class="btn btn-primary" type="submit" name="generate" value="1"><?=$this->getTrans('generateBracket') ?></button>
</form>

<?php if ($rounds): ?>
<div class="tk-theme tk-theme-<?=$this->escape($bracketTheme) ?> tk-bracket-admin tk-bracket-wrapper">
    <div class="tk-bracket">
    <?php foreach ($rounds as $round => $matches): ?>
        <?php $style = isset($layout[(int)$round]) ? '--tk-pair-gap:' . (int)$layout[(int)$round]['pairGap'] . 'px;--tk-round-offset:' . (int)$layout[(int)$round]['offset'] . 'px;' : ''; ?>
        <section class="tk-round tk-round-<?=$round ?>" style="<?=$style ?>">
            <h5 class="tk-round-title"><?=$this->getTrans('round') ?> <?=$round ?></h5>
            <div class="tk-round-body">
                <?php for ($i = 0; $i < count($matches); $i += 2): ?>
                    <?php if (isset($matches[$i + 1])): ?>
                        <div class="tk-pair">
                            <?php for ($j = $i; $j <= $i + 1; $j++): ?>
                                <?php $match = $matches[$j]; ?>
                                <?php $statusClass = in_array($match['status'], ['ready', 'reported', 'dispute', 'done'], true) ? ' tk-status-' . $match['status'] : ''; ?>
                                <?php
                                $showTeams = ((int)$round === 1) || in_array($match['status'], ['scheduled', 'ready', 'reported', 'confirmed', 'dispute', 'done'], true);
                                $team1Label = $showTeams ? ($match['team1_tag'] ?: 'TBD') : 'TBD';
                                $team2Label = $showTeams ? ($match['team2_tag'] ?: 'TBD') : 'TBD';
                                ?>
                                <article class="tk-match">
                                    <div class="tk-match-header">
                                        <span class="tk-match-id">#<?=$match['match_no'] ?></span>
                                        <span class="tk-status<?=$statusClass ?>"><?=$this->getTrans($match['status']) ?></span>
                                    </div>
                                    <div class="tk-match-teams d-flex align-items-center justify-content-between">
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <?php if ($showTeams && !empty($match['team1_logo'])): ?>
                                                <img src="<?=$this->getBaseUrl($match['team1_logo']) ?>" alt="<?=$this->escape($team1Label) ?>" style="width:22px;height:22px;object-fit:cover;">
                                            <?php endif; ?>
                                            <span><?=$this->escape($team1Label) ?></span>
                                        </span>
                                        <span class="px-2"><?=$this->getTrans('vs') ?></span>
                                        <span class="d-inline-flex align-items-center gap-2">
                                            <span><?=$this->escape($team2Label) ?></span>
                                            <?php if ($showTeams && !empty($match['team2_logo'])): ?>
                                                <img src="<?=$this->getBaseUrl($match['team2_logo']) ?>" alt="<?=$this->escape($team2Label) ?>" style="width:22px;height:22px;object-fit:cover;">
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <form method="POST" action="">
                                        <?=$this->getTokenField() ?>
                                        <input type="hidden" name="match_id" value="<?=$match['id'] ?>">
                                        <input class="form-control form-control-sm mb-1" type="text" name="map" placeholder="<?=$this->getTrans('mapPlaceholder') ?>" value="<?=$this->escape($match['map']) ?>">
                                        <input class="form-control form-control-sm mb-1" type="number" min="1" name="best_of" placeholder="<?=$this->getTrans('bestOfPlaceholder') ?>" value="<?=$this->escape($match['best_of']) ?>">
                                        <input class="form-control form-control-sm mb-1" type="datetime-local" name="scheduled_at" value="<?=!empty($match['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($match['scheduled_at'])) : '' ?>">
                                        <select class="form-select form-select-sm mb-1" name="status">
                                            <?php foreach (['pending','scheduled','ready','reported','confirmed','dispute','done'] as $status): ?>
                                                <option value="<?=$status ?>" <?=$match['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button class="btn btn-sm btn-outline-secondary" type="submit"><?=$this->getTrans('save') ?></button>
                                    </form>
                                    <div class="tk-match-meta"><a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'matches', 'action' => 'view', 'id' => $match['id']], '') ?>"><?=$this->getTrans('matchView') ?></a></div>
                                </article>
                            <?php endfor; ?>
                        </div>
                    <?php else: ?>
                        <?php $match = $matches[$i]; ?>
                        <?php $statusClass = in_array($match['status'], ['ready', 'reported', 'dispute', 'done'], true) ? ' tk-status-' . $match['status'] : ''; ?>
                        <?php
                        $showTeams = ((int)$round === 1) || in_array($match['status'], ['scheduled', 'ready', 'reported', 'confirmed', 'dispute', 'done'], true);
                        $team1Label = $showTeams ? ($match['team1_tag'] ?: 'TBD') : 'TBD';
                        $team2Label = $showTeams ? ($match['team2_tag'] ?: 'TBD') : 'TBD';
                        ?>
                        <article class="tk-match">
                            <div class="tk-match-header">
                                <span class="tk-match-id">#<?=$match['match_no'] ?></span>
                                <span class="tk-status<?=$statusClass ?>"><?=$this->getTrans($match['status']) ?></span>
                            </div>
                            <div class="tk-match-teams d-flex align-items-center justify-content-between">
                                <span class="d-inline-flex align-items-center gap-2">
                                    <?php if ($showTeams && !empty($match['team1_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['team1_logo']) ?>" alt="<?=$this->escape($team1Label) ?>" style="width:22px;height:22px;object-fit:cover;">
                                    <?php endif; ?>
                                    <span><?=$this->escape($team1Label) ?></span>
                                </span>
                                <span class="px-2"><?=$this->getTrans('vs') ?></span>
                                <span class="d-inline-flex align-items-center gap-2">
                                    <span><?=$this->escape($team2Label) ?></span>
                                    <?php if ($showTeams && !empty($match['team2_logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($match['team2_logo']) ?>" alt="<?=$this->escape($team2Label) ?>" style="width:22px;height:22px;object-fit:cover;">
                                    <?php endif; ?>
                                </span>
                            </div>
                            <form method="POST" action="">
                                <?=$this->getTokenField() ?>
                                <input type="hidden" name="match_id" value="<?=$match['id'] ?>">
                                <input class="form-control form-control-sm mb-1" type="text" name="map" placeholder="<?=$this->getTrans('mapPlaceholder') ?>" value="<?=$this->escape($match['map']) ?>">
                                <input class="form-control form-control-sm mb-1" type="number" min="1" name="best_of" placeholder="<?=$this->getTrans('bestOfPlaceholder') ?>" value="<?=$this->escape($match['best_of']) ?>">
                                <input class="form-control form-control-sm mb-1" type="datetime-local" name="scheduled_at" value="<?=!empty($match['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($match['scheduled_at'])) : '' ?>">
                                <select class="form-select form-select-sm mb-1" name="status">
                                    <?php foreach (['pending','scheduled','ready','reported','confirmed','dispute','done'] as $status): ?>
                                        <option value="<?=$status ?>" <?=$match['status'] === $status ? 'selected' : '' ?>><?=$this->getTrans($status) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button class="btn btn-sm btn-outline-secondary" type="submit"><?=$this->getTrans('save') ?></button>
                            </form>
                            <div class="tk-match-meta"><a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'matches', 'action' => 'view', 'id' => $match['id']], '') ?>"><?=$this->getTrans('matchView') ?></a></div>
                        </article>
                    <?php endif; ?>
                <?php endfor; ?>
            </div>
        </section>
    <?php endforeach; ?>
    </div>
</div>
<?php else: ?>
<p>-</p>
<?php endif; ?>
