<?php
/**
 * Template Name: Читы GTA (Полная База)
 */
get_header(); the_post();
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <?php if(function_exists('onegta_breadcrumb')) onegta_breadcrumb(); ?>
    <div class="section-label">База данных</div>
    <h1 class="page-hero__title">Все читы для GTA</h1>
    <p style="max-width:540px;margin-top:.5rem;">Самая полная коллекция кодов для GTA V, IV, San Andreas и Vice City. Нажми на код, чтобы скопировать.</p>
  </div>
</section>

<div class="filter-bar">
  <div class="container filter-bar__inner">
    <button class="filter-btn active" data-game="all">Все игры</button>
    <?php foreach (['gta5'=>'GTA V','gta4'=>'GTA IV','sa'=>'San Andreas','vc'=>'Vice City'] as $k=>$v) :
      echo '<button class="filter-btn" data-game="'.esc_attr($k).'">'.esc_html($v).'</button>';
    endforeach; ?>
  </div>
</div>

<div style="padding:2rem 0 4rem;background:var(--bg);">
  <div class="container">

    <div style="display:flex;gap:.5rem;margin-bottom:2rem;flex-wrap:wrap;">
      <button class="filter-btn active" data-platform="pc">PC</button>
      <button class="filter-btn" data-platform="ps">PlayStation</button>
      <button class="filter-btn" data-platform="xbox">Xbox</button>
    </div>

    <?php
    $cheats = [
        'Оружие и Бой' => [
            // GTA 5
            ['name' => 'Набор оружия', 'code_pc' => 'TOOLUP', 'code_ps' => '△, R2, ←, L1, X, →, △, ↓, ■, L1, L1, L1', 'code_xbox' => 'Y, RT, ←, LB, A, →, Y, ↓, X, LB, LB, LB', 'game' => 'gta5'],
            ['name' => 'Взрывные выстрелы', 'code_pc' => 'HIGHEX', 'code_ps' => '→, ■, X, ←, R1, R2, ←, →, →, L1, L1, L1', 'code_xbox' => '→, X, A, ←, RB, RT, ←, →, →, LB, LB, LB', 'game' => 'gta5'],
            ['name' => 'Пылающие пули', 'code_pc' => 'INCENDIARY', 'code_ps' => 'L1, R1, ■, R1, ←, R2, R1, ←, ■, →, L1, L1', 'code_xbox' => 'LB, RB, X, RB, ←, RT, RB, ←, X, →, LB, LB', 'game' => 'gta5'],
            ['name' => 'Взрывной удар (рукопашная)', 'code_pc' => 'HOTHANDS', 'code_ps' => '→, ←, X, △, R1, ●, ●, ●, L2', 'code_xbox' => '→, ←, A, Y, RB, B, B, B, LT', 'game' => 'gta5'],
            // GTA SA
            ['name' => 'Набор оружия 1 (LCPD)', 'code_pc' => 'LXGIWYL', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, →, ↑', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, →, ↑', 'game' => 'sa'],
            ['name' => 'Набор оружия 2 (Pro)', 'code_pc' => 'PROFESSIONALSKIT', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, ↓, ←', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, ↓, ←', 'game' => 'sa'],
            ['name' => 'Набор оружия 3 (Nutter)', 'code_pc' => 'UZUMYMW', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, ↓, ↓', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, ↓, ↓', 'game' => 'sa'],
            ['name' => 'Бесконечные патроны', 'code_pc' => 'FULLCLIP', 'code_ps' => 'L1, R1, ■, R1, ←, R2, R1, ←, ■, ↓, L1, L1', 'code_xbox' => 'LB, RB, X, RB, ←, RT, RB, ←, X, ↓, LB, LB', 'game' => 'sa'],
            // GTA VC
            ['name' => 'Набор оружия 1', 'code_pc' => 'THUGSTOOLS', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, →, ↑', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, →, ↑', 'game' => 'vc'],
            ['name' => 'Набор оружия 2', 'code_pc' => 'PROFESSIONALTOOLS', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, ↓, ←', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, ↓, ←', 'game' => 'vc'],
            ['name' => 'Набор оружия 3', 'code_pc' => 'NUTTERTOOLS', 'code_ps' => 'R1, R2, L1, R2, ←, ↓, →, ↑, ←, ↓, ↓, ↓', 'code_xbox' => 'RB, RT, LB, RT, ←, ↓, →, ↑, ←, ↓, ↓, ↓', 'game' => 'vc'],
            // GTA 4
            ['name' => 'Набор оружия 1', 'code_pc' => '486-555-0100', 'code_ps' => '486-555-0100', 'code_xbox' => '486-555-0100', 'game' => 'gta4'],
            ['name' => 'Набор оружия 2', 'code_pc' => '486-555-0150', 'code_ps' => '486-555-0150', 'code_xbox' => '486-555-0150', 'game' => 'gta4'],
        ],
        'Здоровье и Броня' => [
            ['name' => 'Макс. здоровье и броня (V)', 'code_pc' => 'TURTLE', 'code_ps' => '●, L1, △, R2, X, ■, ●, →, ■, L1, L1, L1', 'code_xbox' => 'B, LB, Y, RT, A, X, B, →, X, LB, LB, LB', 'game' => 'gta5'],
            ['name' => 'Бессмертие (V - 5 мин)', 'code_pc' => 'PAINKILLER', 'code_ps' => '→, X, →, ←, →, R1, →, ←, X, △', 'code_xbox' => '→, A, →, ←, →, RB, →, ←, A, Y', 'game' => 'gta5'],
            ['name' => 'Здоровье, Броня, $250k (SA)', 'code_pc' => 'HESOYAM', 'code_ps' => 'R1, R2, L1, X, ←, ↓, →, ↑, ←, ↓, →, ↑', 'code_xbox' => 'RB, RT, LB, A, ←, ↓, →, ↑, ←, ↓, →, ↑', 'game' => 'sa'],
            ['name' => 'Бесконечное здоровье (SA)', 'code_pc' => 'BAGUVIX', 'code_ps' => '↓, X, →, ←, →, R1, →, ↓, ↑, △', 'code_xbox' => '↓, A, →, ←, →, RB, →, ↓, ↑, Y', 'game' => 'sa'],
            ['name' => 'Полное здоровье (VC)', 'code_pc' => 'ASPIRINE', 'code_ps' => 'R1, R2, L1, ●, ←, ↓, →, ↑, ←, ↓, →, ↑', 'code_xbox' => 'RB, RT, LB, B, ←, ↓, →, ↑, ←, ↓, →, ↑', 'game' => 'vc'],
            ['name' => 'Полная броня (VC)', 'code_pc' => 'PRECIOUSPROTECTION', 'code_ps' => 'R1, R2, L1, X, ←, ↓, →, ↑, ←, ↓, →, ↑', 'code_xbox' => 'RB, RT, LB, A, ←, ↓, →, ↑, ←, ↓, →, ↑', 'game' => 'vc'],
            ['name' => 'Суицид (SA)', 'code_pc' => 'GOODBYECRUELWORLD', 'code_ps' => '→, L2, ↓, R1, ←, ←, R1, L1, L2, L1', 'code_xbox' => '→, LT, ↓, RB, ←, ←, RB, LB, LT, LB', 'game' => 'sa'],
        ],
        'Полиция' => [
            ['name' => 'Понизить уровень розыска', 'code_pc' => 'LAWYERUP', 'code_ps' => 'R1, R1, ●, R2, →, ←, →, ←, →, ←', 'code_xbox' => 'RB, RB, B, RT, →, ←, →, ←, →, ←', 'game' => 'gta5'],
            ['name' => 'Повысить уровень розыска', 'code_pc' => 'FUGITIVE', 'code_ps' => 'R1, R1, ●, R2, ←, →, ←, →, ←, →', 'code_xbox' => 'RB, RB, B, RT, ←, →, ←, →, ←, →', 'game' => 'gta5'],
            ['name' => 'Снять розыск (SA)', 'code_pc' => 'TURNDOWNTHEHEAT', 'code_ps' => 'R1, R1, ●, R2, ↑, ↓, ↑, ↓, ↑, ↓', 'code_xbox' => 'RB, RB, B, RT, ↑, ↓, ↑, ↓, ↑, ↓', 'game' => 'sa'],
            ['name' => 'Зафиксировать (без розыска)', 'code_pc' => 'AEZAKMI', 'code_ps' => '●, →, ●, →, ←, ■, △, ↑', 'code_xbox' => 'B, →, B, →, ←, X, Y, ↑', 'game' => 'sa'],
            ['name' => 'Шесть звезд (SA)', 'code_pc' => 'BRINGITON', 'code_ps' => '●, →, ●, →, ←, ■, X, ↓', 'code_xbox' => 'B, →, B, →, ←, X, A, ↓', 'game' => 'sa'],
            ['name' => 'Убрать розыск (VC)', 'code_pc' => 'LEAVEMEALONE', 'code_ps' => 'R1, R1, ●, R2, ↑, ↓, ↑, ↓, ↑, ↓', 'code_xbox' => 'RB, RB, B, RT, ↑, ↓, ↑, ↓, ↑, ↓', 'game' => 'vc'],
        ],
        'Транспорт (Спецтехника)' => [
            ['name' => 'Вертолет Buzzard', 'code_pc' => 'BUZZOFF', 'code_ps' => '●, ●, L1, ●, ●, ●, L1, L2, R1, △, ●, △', 'code_xbox' => 'B, B, LB, B, B, B, LB, LT, RB, Y, B, Y', 'game' => 'gta5'],
            ['name' => 'Самолет Stunt Plane', 'code_pc' => 'BARNSTORM', 'code_ps' => '●, →, L1, L2, ←, R1, L1, L1, ←, ←, X, △', 'code_xbox' => 'B, →, LB, LT, ←, RB, LB, LB, ←, ←, A, Y', 'game' => 'gta5'],
            ['name' => 'Танк Rhino (SA)', 'code_pc' => 'PANZER', 'code_ps' => '●, ●, L1, ●, ●, ●, L1, L2, R1, △, ●, △', 'code_xbox' => 'B, B, LB, B, B, B, LB, LT, RB, Y, B, Y', 'game' => 'sa'],
            ['name' => 'Истребитель Hydra (SA)', 'code_pc' => 'JUMPJET', 'code_ps' => '△, △, ■, ●, X, L1, L1, ↓, ↑', 'code_xbox' => 'Y, Y, X, B, A, LB, LB, ↓, ↑', 'game' => 'sa'],
            ['name' => 'Вертолет Hunter (SA)', 'code_pc' => 'OHDUDE', 'code_ps' => '●, X, L1, ●, ●, L1, ●, R1, R2, L2, L1, L1', 'code_xbox' => 'B, A, LB, B, B, LB, B, RB, RT, LT, LB, LB', 'game' => 'sa'],
            ['name' => 'Джетпак (SA)', 'code_pc' => 'ROCKETMAN', 'code_ps' => 'L1, L2, R1, R2, ↑, ↓, ←, →, L1, L2, R1, R2, ↑, ↓, ←, →', 'code_xbox' => 'LB, LT, RB, RT, ↑, ↓, ←, →, LB, LT, RB, RT, ↑, ↓, ←, →', 'game' => 'sa'],
            ['name' => 'Танк Rhino (VC)', 'code_pc' => 'PANZER', 'code_ps' => '●, ●, L1, ●, ●, ●, L1, L2, R1, △, ●, △', 'code_xbox' => 'B, B, LB, B, B, B, LB, LT, RB, Y, B, Y', 'game' => 'vc'],
            ['name' => 'Вертолет Hunter (VC)', 'code_pc' => 'AMERICAHELICOPTER', 'code_ps' => '●, X, L1, ●, ●, L1, ●, R1, R2, L2, L1, L1', 'code_xbox' => 'B, A, LB, B, B, LB, B, RB, RT, LT, LB, LB', 'game' => 'vc'],
            ['name' => 'Самолет Dodo (VC)', 'code_pc' => 'FLYINGWAYS', 'code_ps' => '→, R2, ●, R1, L2, ↓, L1, R1', 'code_xbox' => '→, RT, B, RB, LT, ↓, LB, RB', 'game' => 'vc'],
        ],
        'Транспорт (Автомобили)' => [
            ['name' => 'Спорткар Comet (V)', 'code_pc' => 'COMET', 'code_ps' => 'R1, ●, R2, →, L1, L2, X, X, ■, R1', 'code_xbox' => 'RB, B, RT, →, LB, LT, A, A, X, RB', 'game' => 'gta5'],
            ['name' => 'Лимузин Stretch (V)', 'code_pc' => 'VINEWOOD', 'code_ps' => 'R2, →, L2, ←, ←, R1, L1, ●, →', 'code_xbox' => 'RT, →, LT, ←, ←, RB, LB, B, →', 'game' => 'gta5'],
            ['name' => 'Монстр-трак (SA)', 'code_pc' => 'MONSTERMASH', 'code_ps' => '→, ↑, R1, R1, R1, ↓, △, △, X, ●, L1, L1', 'code_xbox' => '→, ↑, RB, RB, RB, ↓, Y, Y, A, B, LB, LB', 'game' => 'sa'],
            ['name' => 'Тягач с прицепом (SA)', 'code_pc' => 'AMOMHRER', 'code_ps' => 'R1, ↑, ←, →, R2, ↑, →, ■, →, L2, L1, L1', 'code_xbox' => 'RB, ↑, ←, →, RT, ↑, →, X, →, LT, LB, LB', 'game' => 'sa'],
            ['name' => 'Квадроцикл (SA)', 'code_pc' => 'FOURWHEELFUN', 'code_ps' => '←, ←, ↓, ↓, ↑, ↑, ■, ●, △, R1, R2', 'code_xbox' => '←, ←, ↓, ↓, ↑, ↑, X, B, Y, RB, RT', 'game' => 'sa'],
            ['name' => 'Машина Love Fist (VC)', 'code_pc' => 'ROCKANDROLLCAR', 'code_ps' => 'R2, ↑, L2, ←, ←, R1, L1, ●, →', 'code_xbox' => 'RT, ↑, LT, ←, ←, RB, LB, B, →', 'game' => 'vc'],
            ['name' => 'Гоночный Bloodring (VC)', 'code_pc' => 'GETTHEREVERYFASTINDEED', 'code_ps' => '↓, R1, ●, L2, L2, X, R1, L1, ←, ←', 'code_xbox' => '↓, RB, B, LT, LT, A, RB, LB, ←, ←', 'game' => 'vc'],
            ['name' => 'Гольф-кар Caddy (VC)', 'code_pc' => 'BETTERTHANWALKING', 'code_ps' => '●, L1, ↑, R1, L2, X, R1, L1, ●, X', 'code_xbox' => 'B, LB, ↑, RB, LT, A, RB, LB, B, A', 'game' => 'vc'],
        ],
        'Мир и Геймплей' => [
            ['name' => 'Замедление времени (V)', 'code_pc' => 'SLOWMO', 'code_ps' => '△, ←, →, →, ■, R2, R1', 'code_xbox' => 'Y, ←, →, →, X, RT, RB', 'game' => 'gta5'],
            ['name' => 'Лунная гравитация (V)', 'code_pc' => 'FLOATER', 'code_ps' => '←, ←, L1, R1, L1, →, ←, L1, ←', 'code_xbox' => '←, ←, LB, RB, LB, →, ←, LB, ←', 'game' => 'gta5'],
            ['name' => 'Суперпрыжок (V)', 'code_pc' => 'HOPTOIT', 'code_ps' => '←, ←, △, △, →, →, ←, →, ■, R1, R2', 'code_xbox' => '←, ←, Y, Y, →, →, ←, →, X, RB, RT', 'game' => 'gta5'],
            ['name' => 'Очень толстый CJ (SA)', 'code_pc' => 'BTCDBCB', 'code_ps' => '△, ↑, ↑, ←, →, ■, ●, ↓', 'code_xbox' => 'Y, ↑, ↑, ←, →, X, B, ↓', 'game' => 'sa'],
            ['name' => 'Очень худой CJ (SA)', 'code_pc' => 'KVGYZQK', 'code_ps' => '△, ↑, ↑, ←, →, ■, ●, →', 'code_xbox' => 'Y, ↑, ↑, ←, →, X, B, →', 'game' => 'sa'],
            ['name' => 'Режим Хаоса (SA)', 'code_pc' => 'STATEOFEMERGENCY', 'code_ps' => 'L2, →, L1, △, →, →, R1, L1, →, L1, L1, L1', 'code_xbox' => 'LT, →, LB, Y, →, →, RB, LB, →, LB, LB, LB', 'game' => 'sa'],
            ['name' => 'Все пешеходы с оружием (VC)', 'code_pc' => 'OURGODGIVENRIGHTTOBEARARMS', 'code_ps' => 'R2, R1, X, △, X, △, ↑, ↓', 'code_xbox' => 'RT, RB, A, Y, A, Y, ↑, ↓', 'game' => 'vc'],
            ['name' => 'Пешеходы атакуют (VC)', 'code_pc' => 'NOBODYLIKESME', 'code_ps' => '↓, ↑, ↑, ↑, X, R2, R1, L2, L2', 'code_xbox' => '↓, ↑, ↑, ↑, A, RT, RB, LT, LT', 'game' => 'vc'],
            ['name' => 'Женское внимание (VC)', 'code_pc' => 'FANNYMAGNET', 'code_ps' => '●, X, L1, L1, R2, X, X, ●, △', 'code_xbox' => 'B, A, LB, LB, RT, A, A, B, Y', 'game' => 'vc'],
        ],
        'Погода и Время' => [
            ['name' => 'Ясная погода (V)', 'code_pc' => 'MAKEITDRY', 'code_ps' => 'R2, X, L1, L1, L2, L2, L2, ↓', 'code_xbox' => 'RT, A, LB, LB, LT, LT, LT, ↓', 'game' => 'gta5'],
            ['name' => 'Снег / Изменение погоды (V)', 'code_pc' => 'SNOWDAY', 'code_ps' => 'R2, X, L1, L1, L2, L2, L2, ■', 'code_xbox' => 'RT, A, LB, LB, LT, LT, LT, X', 'game' => 'gta5'],
            ['name' => 'Солнечно (SA)', 'code_pc' => 'AFZLLQLL', 'code_ps' => 'R2, X, L1, L1, L2, L2, L2, ↓', 'code_xbox' => 'RT, A, LB, LB, LT, LT, LT, ↓', 'game' => 'sa'],
            ['name' => 'Песчаная буря (SA)', 'code_pc' => 'CWJXUOC', 'code_ps' => '↑, ↓, L1, L1, L2, L2, L1, L2, R1, R2', 'code_xbox' => '↑, ↓, LB, LB, LT, LT, LB, LT, RB, RT', 'game' => 'sa'],
            ['name' => 'Туман (VC)', 'code_pc' => 'CANTSEEATHING', 'code_ps' => 'R2, X, L1, L1, L2, L2, L2, X', 'code_xbox' => 'RT, A, LB, LB, LT, LT, LT, A', 'game' => 'vc'],
        ],
    ];

    foreach ($cheats as $cat => $items) :
    ?>
      <div style="margin-bottom:3rem;">
        <div class="section-label" style="margin-bottom:1.2rem; font-size: 1rem; color: var(--orange);"><?php echo esc_html($cat); ?></div>
        <div class="cheats-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(400px, 1fr));gap:10px;">
          <?php foreach ($items as $ch) : ?>
            <div class="cheat-card" data-game="<?php echo esc_attr($ch['game']); ?>" style="background:var(--white);border:1px solid var(--border);padding:1.2rem;border-left:4px solid var(--orange);transition:all .2s; position: relative; overflow: hidden;">
              <div style="font-family:'Orbitron',monospace;font-size:.6rem;letter-spacing:2px;color:var(--text3);margin-bottom:.3rem;text-transform:uppercase;"><?php echo strtoupper($ch['game']); ?></div>
              <div style="font-family:'Bebas Neue',sans-serif;font-size:1.3rem;letter-spacing:1px;color:var(--text);margin-bottom:1rem;"><?php echo esc_html($ch['name']); ?></div>
              
              <div class="platforms-wrap">
                  <div class="cheat-code-wrap" data-platform-code="pc" style="background:var(--bg);border:1px solid var(--border);padding:.6rem .9rem;font-family:'Courier New',monospace;font-size:.9rem;color:var(--orange);display:flex;align-items:center;justify-content:space-between;gap:.5rem; border-radius: 4px;">
                    <span style="font-weight: 700;"><?php echo esc_html($ch['code_pc']); ?></span>
                    <button class="cheat-copy-btn" data-copy="<?php echo esc_attr($ch['code_pc']); ?>" style="background:var(--white);color:var(--text);font-size:.65rem;cursor:pointer;padding:4px 8px;border:1px solid var(--border);font-family:'DM Sans',sans-serif;font-weight:700; border-radius: 3px;">Copy</button>
                  </div>
                  
                  <div class="cheat-code-wrap" data-platform-code="ps" style="display:none;background:var(--bg);border:1px solid var(--border);padding:.6rem .9rem;font-size:.8rem;color:var(--text2);justify-content:space-between;align-items:center;gap:.5rem; border-radius: 4px;">
                    <span style="word-break: break-all; line-height: 1.4;"><?php echo esc_html($ch['code_ps']); ?></span>
                    <button class="cheat-copy-btn" data-copy="<?php echo esc_attr($ch['code_ps']); ?>" style="background:var(--white);color:var(--text);font-size:.65rem;cursor:pointer;padding:4px 8px;border:1px solid var(--border);font-family:'DM Sans',sans-serif;font-weight:700; border-radius: 3px;">Copy</button>
                  </div>
                  
                  <div class="cheat-code-wrap" data-platform-code="xbox" style="display:none;background:var(--bg);border:1px solid var(--border);padding:.6rem .9rem;font-size:.8rem;color:var(--text2);justify-content:space-between;align-items:center;gap:.5rem; border-radius: 4px;">
                    <span style="word-break: break-all; line-height: 1.4;"><?php echo esc_html($ch['code_xbox']); ?></span>
                    <button class="cheat-copy-btn" data-copy="<?php echo esc_attr($ch['code_xbox']); ?>" style="background:none;color:var(--text);font-size:.65rem;cursor:pointer;padding:4px 8px;border:1px solid var(--border);font-family:'DM Sans',sans-serif;font-weight:700; border-radius: 3px;">Copy</button>
                  </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  </div>
</div>

<?php get_footer(); ?>