<?php
/** @var \Ilch\View $this */
$tournament = $this->get('tournament');
$teams = $this->get('teams');
$myTeamsForRegister = $this->get('myTeamsForRegister') ?: [];
$myTeamsForCheckin = $this->get('myTeamsForCheckin') ?: [];
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
$user = $this->getUser();
$canManageTeams = $user && ($user->isAdmin() || $user->hasAccess('module_tournament') || $user->hasAccess('tournament_team_manage'));
$isRegistrationOpen = ($tournament['status'] ?? '') === 'registration_open';
$isRegistrationClosedOrHigher = in_array(($tournament['status'] ?? ''), ['registration_closed', 'running', 'finished', 'archived'], true);
$checkinRequired = (int)($tournament['checkin_required'] ?? 0) === 1;
?>
<link rel="stylesheet" href="<?=$this->getBaseUrl('application/modules/tournament/static/css/bracket.css') ?>">
<style>
    .tk-team-tile {
        display: block;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }

    .tk-team-tile .profile-card {
        height: 100%;
        max-width: 350px;
        margin: 0 auto;
        border-radius: 0;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .tk-team-tile:hover .profile-card {
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.14);
    }

    .profile-img {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .profile-img-fallback {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 700;
        background: #e6e8eb;
        color: #4a4f58;
    }
</style>
<div class="mb-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'tournaments', 'action' => 'index']) ?>"><?=$this->getTrans('menuTournament') ?></a>
    <?php if ($canManageTeams): ?>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'index']) ?>"><?=$this->getTrans('myTeams') ?></a>
        <a class="btn btn-outline-secondary btn-sm" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'create']) ?>"><?=$this->getTrans('createTeam') ?></a>
    <?php endif; ?>
</div>
<h1><?=$this->escape($tournament['title']) ?></h1>
<?php if (!empty($tournament['banner'])): ?>
    <p><img src="<?=$this->getBaseUrl($tournament['banner']) ?>" alt="<?=$this->escape($tournament['title']) ?>" style="width: 100%; max-height: 320px; object-fit: cover;"></p>
<?php endif; ?>

<div class="row">
    <div class="col-xl-8">
        <div class="card mb-3">
            <div class="card-header"><?=$this->getTrans('overview') ?></div>
            <div class="card-body">
                <p><strong><?=$this->getTrans('game') ?>:</strong> <?=$this->escape($tournament['game']) ?></p>
                <p><strong><?=$this->getTrans('teamSize') ?>:</strong> <?=$this->escape($tournament['team_size']) ?></p>
                <p><strong><?=$this->getTrans('maxTeams') ?>:</strong> <?=$this->escape($tournament['max_teams']) ?></p>
                <p><strong><?=$this->getTrans('startAt') ?>:</strong> <?=!empty($tournament['start_at']) ? $this->escape(date('d.m.Y H:i', strtotime($tournament['start_at']))) : '-' ?></p>
                <p><strong><?=$this->getTrans('status') ?>:</strong> <span class="badge text-bg-secondary"><?=$this->getTrans($tournament['status']) ?></span></p>
                <div><?=$tournament['rules'] ?></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <?php if ($this->getUser()): ?>
        <div class="card mb-3">
            <div class="card-header"><?=$this->getTrans('register') ?></div>
            <div class="card-body">
                <?php if ($isRegistrationOpen): ?>
                    <?php if ($myTeamsForRegister): ?>
                        <form method="POST" action="<?=$this->getUrl(['action' => 'register', 'id' => $tournament['id']]) ?>">
                            <?=$this->getTokenField() ?>
                            <div class="mb-3">
                                <label class="form-label" for="team_id"><?=$this->getTrans('teams') ?></label>
                                <select class="form-select" name="team_id" id="team_id" required>
                                    <option value="">-</option>
                                    <?php foreach ($myTeamsForRegister as $myTeam): ?>
                                        <option value="<?=$myTeam['id'] ?>"><?=$this->escape($myTeam['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button class="btn btn-primary" type="submit"><?=$this->getTrans('register') ?></button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?=$this->getTrans('registrationNoEligibleTeam') ?></div>
                    <?php endif; ?>
                <?php elseif ($isRegistrationClosedOrHigher): ?>
                    <div class="alert alert-secondary mb-0"><?=$this->getTrans('registrationClosedInfo') ?></div>
                <?php else: ?>
                    <div class="alert alert-info mb-0"><?=$this->getTrans('registrationNotOpenYet') ?></div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($checkinRequired): ?>
            <div class="card mb-3">
                <div class="card-header"><?=$this->getTrans('checkin') ?></div>
                <div class="card-body">
                    <?php if ($isRegistrationOpen): ?>
                        <?php if ($myTeamsForCheckin): ?>
                            <form method="POST" action="<?=$this->getUrl(['action' => 'checkin', 'id' => $tournament['id']]) ?>">
                                <?=$this->getTokenField() ?>
                                <div class="mb-3">
                                    <label class="form-label" for="checkin_team_id"><?=$this->getTrans('teams') ?></label>
                                    <select class="form-select" name="team_id" id="checkin_team_id" required>
                                        <option value="">-</option>
                                        <?php foreach ($myTeamsForCheckin as $myTeam): ?>
                                            <option value="<?=$myTeam['id'] ?>"><?=$this->escape($myTeam['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button class="btn btn-success" type="submit"><?=$this->getTrans('checkinNow') ?></button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-info mb-0"><?=$this->getTrans('checkinNoEligibleTeam') ?></div>
                        <?php endif; ?>
                    <?php elseif ($isRegistrationClosedOrHigher): ?>
                        <div class="alert alert-secondary mb-0"><?=$this->getTrans('checkinClosedInfo') ?></div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0"><?=$this->getTrans('checkinNotOpenYet') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><?=$this->getTrans('acceptedTeams') ?></div>
    <div class="card-body">
        <?php if ($teams): ?>
            <div class="row g-3">
                <?php foreach ($teams as $team): ?>
                    <div class="col-6 col-md-4 col-xl-3">
                        <a class="tk-team-tile" href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'teams', 'action' => 'view', 'id' => $team['team_id']]) ?>">
                            <div class="card profile-card rounded-0">
                                <div class="card-body text-center">
                                    <?php if (!empty($team['logo'])): ?>
                                        <img src="<?=$this->getBaseUrl($team['logo']) ?>" alt="<?=$this->escape($team['team_name']) ?>" class="rounded-circle profile-img mb-3">
                                    <?php else: ?>
                                        <span class="rounded-circle profile-img profile-img-fallback mb-3"><?=$this->escape(strtoupper(substr($team['team_name'], 0, 1))) ?></span>
                                    <?php endif; ?>
                                    <h3 class="card-title mb-2"><?=$this->escape($team['team_name']) ?></h3>
                                    <p class="card-text text-muted mb-3"><?=$this->getTrans('tag') ?>: <?=$this->escape($team['tag'] ?: '-') ?></p>
                                    <p class="card-text text-muted mb-0"><?=$this->getTrans('playersCount') ?>: <?=$this->escape((string)($team['players_count'] ?? 0)) ?></p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="mb-0">-</p>
        <?php endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><?=$this->getTrans('bracket') ?></div>
    <div class="card-body">
        <?php if ($rounds): ?>
            <div class="tk-theme tk-theme-<?=$this->escape($bracketTheme) ?> tk-bracket-public tk-bracket-wrapper">
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
                                                    <div class="tk-match-meta"><a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'matches', 'action' => 'view', 'id' => $match['id']]) ?>"><?=$this->getTrans('matchView') ?></a></div>
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
                                            <div class="tk-match-meta"><a href="<?=$this->getUrl(['module' => 'tournament', 'controller' => 'matches', 'action' => 'view', 'id' => $match['id']]) ?>"><?=$this->getTrans('matchView') ?></a></div>
                                        </article>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="mb-0">-</p>
        <?php endif; ?>
    </div>
</div>
