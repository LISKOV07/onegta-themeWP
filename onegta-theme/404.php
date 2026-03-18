<?php get_header(); ?>

<style>
/* ── 404 GTA STYLE ── */
.gta-404 {
    min-height: 100vh;
    background: #0a0a0a;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
    padding: 2rem;
}

/* City silhouette bottom */
.gta-404__city {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 220px;
    pointer-events: none;
}
.gta-404__city svg { width: 100%; height: 100%; }

/* Stars / dots bg */
.gta-404__stars {
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle, rgba(255,255,255,.6) 1px, transparent 1px),
        radial-gradient(circle, rgba(255,255,255,.3) 1px, transparent 1px);
    background-size: 120px 120px, 80px 80px;
    background-position: 0 0, 40px 40px;
    opacity: .12;
    pointer-events: none;
}

/* Orange glow */
.gta-404__glow {
    position: absolute;
    top: 30%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 600px;
    height: 300px;
    background: radial-gradient(ellipse, rgba(245,92,0,.18) 0%, transparent 70%);
    pointer-events: none;
}

/* Police light bar */
.gta-404__police-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    display: flex;
    overflow: hidden;
}
.gta-404__police-bar span {
    flex: 1;
    animation: policeLight 1s steps(1) infinite;
}
.gta-404__police-bar span:nth-child(odd)  { background: #e63946; animation-delay: 0s; }
.gta-404__police-bar span:nth-child(even) { background: #1d3557; animation-delay: .5s; }
@keyframes policeLight {
    0%   { opacity: 1; }
    50%  { opacity: 0; }
    100% { opacity: 1; }
}

/* Wanted level stars */
.gta-404__wanted {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: .3rem;
    margin-bottom: 1.5rem;
    background: #111;
    border: 2px solid #333;
    padding: 8px 20px;
    font-family: 'Orbitron', monospace;
    font-size: .65rem;
    letter-spacing: 4px;
    color: #666;
    text-transform: uppercase;
}
.gta-404__wanted-stars { display: flex; gap: 4px; margin-left: .5rem; }
.gta-404__wanted-star {
    width: 20px; height: 20px;
    clip-path: polygon(50% 0%, 61% 35%, 98% 35%, 68% 57%, 79% 91%, 50% 70%, 21% 91%, 32% 57%, 2% 35%, 39% 35%);
    background: #333;
}
.gta-404__wanted-star.active {
    background: #F55C00;
    box-shadow: 0 0 8px rgba(245,92,0,.8);
    animation: starFlash 1.5s ease-in-out infinite;
}
@keyframes starFlash {
    0%,100% { box-shadow: 0 0 8px rgba(245,92,0,.8); }
    50%      { box-shadow: 0 0 16px rgba(245,92,0,1), 0 0 30px rgba(245,92,0,.5); }
}

/* Main 404 number */
.gta-404__num {
    position: relative;
    z-index: 2;
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(8rem, 22vw, 18rem);
    line-height: .85;
    letter-spacing: -4px;
    color: transparent;
    -webkit-text-stroke: 2px #F55C00;
    text-shadow:
        0 0 40px rgba(245,92,0,.4),
        0 0 80px rgba(245,92,0,.2),
        4px 4px 0 rgba(245,92,0,.3);
    animation: glitch404 6s infinite;
    user-select: none;
}
@keyframes glitch404 {
    0%,90%,100% {
        -webkit-text-stroke-color: #F55C00;
        transform: translate(0);
    }
    92% {
        -webkit-text-stroke-color: #e63946;
        transform: translate(-4px, 2px);
        text-shadow: 4px 0 #F55C00, -4px 0 #e63946;
    }
    94% {
        -webkit-text-stroke-color: #F55C00;
        transform: translate(4px, -2px);
        text-shadow: -4px 0 #F55C00, 4px 0 #1d3557;
    }
    96% {
        transform: translate(0);
    }
}

/* Wanted poster box */
.gta-404__poster {
    position: relative;
    z-index: 2;
    background: #111;
    border: 1px solid #222;
    border-top: 4px solid #F55C00;
    padding: 2rem 2.5rem;
    text-align: center;
    max-width: 520px;
    width: 100%;
    margin-bottom: 2rem;
}
.gta-404__poster-eyebrow {
    font-family: 'Orbitron', monospace;
    font-size: .6rem;
    letter-spacing: 6px;
    color: #F55C00;
    text-transform: uppercase;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: .5rem;
}
.gta-404__poster-eyebrow::before,
.gta-404__poster-eyebrow::after {
    content: '';
    flex: 1;
    height: 1px;
    background: linear-gradient(to right, transparent, #F55C00);
}
.gta-404__poster-eyebrow::after {
    background: linear-gradient(to left, transparent, #F55C00);
}

/* Pixel art car SVG */
.gta-404__car {
    margin: 0 auto 1.5rem;
    width: 160px;
    animation: carBounce 2s ease-in-out infinite;
}
@keyframes carBounce {
    0%,100% { transform: translateY(0) rotate(0deg); }
    25%      { transform: translateY(-4px) rotate(-.5deg); }
    75%      { transform: translateY(-2px) rotate(.5deg); }
}

.gta-404__title {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(1.8rem, 4vw, 2.8rem);
    letter-spacing: 3px;
    color: #fff;
    margin-bottom: .8rem;
    line-height: 1;
}
.gta-404__title span { color: #F55C00; }

.gta-404__desc {
    font-size: .92rem;
    color: #666;
    line-height: 1.7;
    margin-bottom: 0;
}

/* Radio tuner */
.gta-404__radio {
    position: relative;
    z-index: 2;
    background: #111;
    border: 1px solid #222;
    padding: .8rem 1.4rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    max-width: 400px;
    width: 100%;
}
.gta-404__radio-icon { font-size: 1.2rem; animation: radioPulse 2s ease-in-out infinite; }
@keyframes radioPulse { 0%,100%{opacity:1} 50%{opacity:.4} }
.gta-404__radio-text {
    font-family: 'Orbitron', monospace;
    font-size: .65rem;
    letter-spacing: 2px;
    color: #555;
}
.gta-404__radio-station {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1rem;
    letter-spacing: 3px;
    color: #F55C00;
}
.gta-404__radio-bar {
    flex: 1;
    height: 3px;
    background: #1a1a1a;
    position: relative;
    overflow: hidden;
}
.gta-404__radio-bar::after {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 60%;
    height: 100%;
    background: linear-gradient(to right, transparent, #F55C00, transparent);
    animation: radioScan 3s linear infinite;
}
@keyframes radioScan { to { left: 150%; } }

/* CTA buttons */
.gta-404__cta {
    position: relative;
    z-index: 2;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

/* Scanlines overlay */
.gta-404__scanlines {
    position: absolute;
    inset: 0;
    background: repeating-linear-gradient(
        0deg,
        transparent,
        transparent 2px,
        rgba(0,0,0,.15) 2px,
        rgba(0,0,0,.15) 4px
    );
    pointer-events: none;
    z-index: 1;
    opacity: .4;
}

/* Coordinates bottom right */
.gta-404__coords {
    position: absolute;
    bottom: 1.5rem;
    right: 1.5rem;
    font-family: 'Orbitron', monospace;
    font-size: .58rem;
    letter-spacing: 2px;
    color: #333;
    z-index: 2;
    text-align: right;
    animation: coordsBlink 3s steps(1) infinite;
}
@keyframes coordsBlink { 0%,90%{opacity:1} 95%{opacity:0} 100%{opacity:1} }

/* Health/armor bars top right (decorative) */
.gta-404__hud {
    position: absolute;
    top: 5rem;
    right: 2rem;
    z-index: 2;
    display: flex;
    flex-direction: column;
    gap: .4rem;
    align-items: flex-end;
}
.gta-404__hud-bar {
    display: flex;
    align-items: center;
    gap: .5rem;
}
.gta-404__hud-icon { font-size: .8rem; }
.gta-404__hud-track {
    width: 80px;
    height: 6px;
    background: #1a1a1a;
    position: relative;
    overflow: hidden;
}
.gta-404__hud-fill {
    height: 100%;
    position: relative;
}
.gta-404__hud-fill--health { background: #e63946; width: 0%; animation: fillHealth 2s ease .5s forwards; }
.gta-404__hud-fill--armor  { background: #4895ef; width: 0%; animation: fillArmor  2s ease 1s  forwards; }
@keyframes fillHealth { to { width: 23%; } }
@keyframes fillArmor  { to { width: 0%; }  }
.gta-404__hud-val {
    font-family: 'Orbitron', monospace;
    font-size: .55rem;
    color: #444;
    letter-spacing: 1px;
    width: 28px;
    text-align: right;
}

/* Cash display */
.gta-404__cash {
    position: absolute;
    top: 5rem;
    left: 2rem;
    z-index: 2;
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.4rem;
    letter-spacing: 2px;
    color: #2d6a4f;
    text-shadow: 0 0 10px rgba(45,106,79,.5);
    animation: cashCount 3s ease forwards;
}
@keyframes cashCount { from{opacity:0} to{opacity:1} }

@media (max-width: 768px) {
    .gta-404__hud, .gta-404__cash, .gta-404__coords { display: none; }
    .gta-404__poster { padding: 1.5rem; }
    .gta-404__police-bar { height: 3px; }
}
</style>

<div class="gta-404">

    <!-- Police light bar -->
    <div class="gta-404__police-bar">
        <?php for($i=0;$i<20;$i++) echo '<span></span>'; ?>
    </div>

    <!-- BG elements -->
    <div class="gta-404__stars"></div>
    <div class="gta-404__glow"></div>
    <div class="gta-404__scanlines"></div>

    <!-- HUD decorations -->
    <div class="gta-404__cash">$0</div>
    <div class="gta-404__hud">
        <div class="gta-404__hud-bar">
            <span class="gta-404__hud-icon">❤️</span>
            <div class="gta-404__hud-track"><div class="gta-404__hud-fill gta-404__hud-fill--health"></div></div>
            <span class="gta-404__hud-val">23</span>
        </div>
        <div class="gta-404__hud-bar">
            <span class="gta-404__hud-icon">🛡️</span>
            <div class="gta-404__hud-track"><div class="gta-404__hud-fill gta-404__hud-fill--armor"></div></div>
            <span class="gta-404__hud-val">0</span>
        </div>
    </div>

    <!-- Wanted level -->
    <div class="gta-404__wanted">
        <span>Разыскивается</span>
        <div class="gta-404__wanted-stars">
            <?php for($i=1;$i<=5;$i++) echo '<div class="gta-404__wanted-star'.($i<=5?' active':'').'"></div>'; ?>
        </div>
    </div>

    <!-- BIG 404 -->
    <div class="gta-404__num" aria-hidden="true">404</div>

    <!-- Wanted poster box -->
    <div class="gta-404__poster">
        <div class="gta-404__poster-eyebrow">Страница в розыске</div>

        <!-- Pixel art car -->
        <div class="gta-404__car">
            <svg viewBox="0 0 160 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Body -->
                <rect x="10" y="35" width="140" height="30" fill="#F55C00"/>
                <rect x="30" y="18" width="90" height="22" fill="#CC4400"/>
                <!-- Windows -->
                <rect x="35" y="21" width="35" height="16" fill="#1a1a2e" rx="2"/>
                <rect x="75" y="21" width="35" height="16" fill="#1a1a2e" rx="2"/>
                <!-- Window glare -->
                <rect x="37" y="23" width="8" height="3" fill="rgba(255,255,255,.2)" rx="1"/>
                <rect x="77" y="23" width="8" height="3" fill="rgba(255,255,255,.2)" rx="1"/>
                <!-- Wheels -->
                <circle cx="38" cy="65" r="13" fill="#111"/>
                <circle cx="38" cy="65" r="8"  fill="#222"/>
                <circle cx="38" cy="65" r="3"  fill="#F55C00"/>
                <circle cx="122" cy="65" r="13" fill="#111"/>
                <circle cx="122" cy="65" r="8"  fill="#222"/>
                <circle cx="122" cy="65" r="3"  fill="#F55C00"/>
                <!-- Headlights -->
                <rect x="145" y="38" width="6" height="10" fill="#fffde7" rx="1"/>
                <rect x="145" y="38" width="6" height="10" fill="#fffde7" rx="1" opacity=".5" style="filter:blur(3px)"/>
                <!-- Taillights -->
                <rect x="9" y="38" width="5" height="10" fill="#e63946" rx="1"/>
                <!-- Roof light bar -->
                <rect x="60" y="15" width="35" height="5" fill="#111" rx="1"/>
                <rect x="62" y="16" width="8"  height="3" fill="#e63946" rx="1"/>
                <rect x="73" y="16" width="8"  height="3" fill="#1d3557" rx="1"/>
                <rect x="84" y="16" width="8"  height="3" fill="#e63946" rx="1"/>
                <!-- Door line -->
                <line x1="80" y1="36" x2="80" y2="64" stroke="#aa3300" stroke-width="1.5"/>
                <!-- Handle -->
                <rect x="84" y="48" width="8" height="2" fill="#aa3300" rx="1"/>
                <!-- Exhaust smoke -->
                <circle cx="4" cy="60" r="4" fill="#555" opacity=".4"/>
                <circle cx="-2" cy="56" r="5" fill="#444" opacity=".25"/>
                <circle cx="-8" cy="52" r="6" fill="#333" opacity=".15"/>
            </svg>
        </div>

        <h1 class="gta-404__title">
            Страница <span>угнана</span>
        </h1>
        <p class="gta-404__desc">
            Кто-то угнал эту страницу и скрылся на пятизвёздочном розыске.<br>
            Копы уже выехали, но найти не могут.
        </p>
    </div>

    <!-- Radio -->
    <div class="gta-404__radio">
        <span class="gta-404__radio-icon">📻</span>
        <div>
            <div class="gta-404__radio-text">Сейчас играет</div>
            <div class="gta-404__radio-station">OneGTA Radio — Error FM</div>
        </div>
        <div class="gta-404__radio-bar"></div>
    </div>

    <!-- CTA -->
    <div class="gta-404__cta">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn--primary">🏠 На главную</a>
        <a href="<?php echo esc_url(home_url('/news/')); ?>" class="btn btn--ghost" style="border-color:#333;color:#666;">📰 Новости</a>
        <button onclick="history.back()" class="btn btn--ghost" style="border-color:#333;color:#666;">← Назад</button>
    </div>

    <!-- City skyline -->
    <div class="gta-404__city">
        <svg viewBox="0 0 1440 220" preserveAspectRatio="none" fill="none" xmlns="http://www.w3.org/2000/svg">
            <!-- Buildings silhouette -->
            <rect x="0"    y="80"  width="60"  height="140" fill="#0f0f0f"/>
            <rect x="10"   y="50"  width="20"  height="30"  fill="#0f0f0f"/>
            <rect x="70"   y="100" width="80"  height="120" fill="#111"/>
            <rect x="90"   y="60"  width="30"  height="45"  fill="#111"/>
            <!-- Antenna -->
            <rect x="103"  y="40"  width="3"   height="25"  fill="#111"/>
            <rect x="160"  y="90"  width="50"  height="130" fill="#0d0d0d"/>
            <rect x="170"  y="55"  width="15"  height="40"  fill="#0d0d0d"/>
            <rect x="220"  y="70"  width="90"  height="150" fill="#111"/>
            <rect x="240"  y="40"  width="25"  height="35"  fill="#111"/>
            <!-- Windows lit -->
            <rect x="230"  y="80"  width="5"   height="4"   fill="#F55C00" opacity=".4"/>
            <rect x="240"  y="90"  width="5"   height="4"   fill="#F55C00" opacity=".3"/>
            <rect x="255"  y="75"  width="5"   height="4"   fill="rgba(255,255,150,.3)"/>
            <rect x="320"  y="85"  width="60"  height="135" fill="#0f0f0f"/>
            <rect x="335"  y="50"  width="25"  height="40"  fill="#0f0f0f"/>
            <rect x="390"  y="60"  width="100" height="160" fill="#111"/>
            <rect x="415"  y="30"  width="30"  height="35"  fill="#111"/>
            <!-- Antenna with light -->
            <rect x="429"  y="10"  width="3"   height="25"  fill="#111"/>
            <circle cx="430" cy="10" r="3" fill="#e63946" opacity=".8"/>
            <rect x="500"  y="95"  width="70"  height="125" fill="#0d0d0d"/>
            <rect x="580"  y="75"  width="90"  height="145" fill="#111"/>
            <rect x="600"  y="45"  width="40"  height="35"  fill="#111"/>
            <rect x="618"  y="20"  width="3"   height="30"  fill="#111"/>
            <circle cx="619" cy="20" r="3" fill="#F55C00" opacity=".7"/>
            <!-- Window lights -->
            <rect x="590"  y="85"  width="6"   height="5"   fill="rgba(255,255,150,.25)"/>
            <rect x="605"  y="95"  width="6"   height="5"   fill="#F55C00" opacity=".3"/>
            <rect x="620"  y="80"  width="6"   height="5"   fill="rgba(255,255,150,.2)"/>
            <rect x="680"  y="100" width="50"  height="120" fill="#0f0f0f"/>
            <rect x="740"  y="80"  width="80"  height="140" fill="#111"/>
            <rect x="760"  y="50"  width="25"  height="35"  fill="#111"/>
            <rect x="830"  y="70"  width="100" height="150" fill="#0d0d0d"/>
            <rect x="855"  y="35"  width="35"  height="40"  fill="#0d0d0d"/>
            <rect x="871"  y="10"  width="3"   height="30"  fill="#0d0d0d"/>
            <circle cx="872" cy="10" r="3" fill="#e63946" opacity=".9"/>
            <!-- More windows -->
            <rect x="840"  y="80"  width="6"   height="5"   fill="#F55C00" opacity=".35"/>
            <rect x="855"  y="75"  width="6"   height="5"   fill="rgba(255,255,150,.25)"/>
            <rect x="870"  y="85"  width="6"   height="5"   fill="#F55C00" opacity=".2"/>
            <rect x="940"  y="90"  width="60"  height="130" fill="#111"/>
            <rect x="1010" y="65"  width="90"  height="155" fill="#0f0f0f"/>
            <rect x="1030" y="35"  width="30"  height="35"  fill="#0f0f0f"/>
            <rect x="1044" y="12"  width="3"   height="28"  fill="#0f0f0f"/>
            <circle cx="1045" cy="12" r="3" fill="#F55C00" opacity=".8"/>
            <rect x="1110" y="85"  width="70"  height="135" fill="#111"/>
            <rect x="1190" y="75"  width="80"  height="145" fill="#0d0d0d"/>
            <rect x="1210" y="45"  width="25"  height="35"  fill="#0d0d0d"/>
            <rect x="1280" y="90"  width="60"  height="130" fill="#111"/>
            <rect x="1350" y="70"  width="90"  height="150" fill="#0f0f0f"/>
            <rect x="1370" y="40"  width="30"  height="35"  fill="#0f0f0f"/>
            <!-- Ground -->
            <rect x="0" y="200" width="1440" height="20" fill="#0a0a0a"/>
            <!-- Road -->
            <rect x="0" y="205" width="1440" height="15" fill="#111"/>
            <rect x="100" y="210" width="80" height="3" fill="#333"/>
            <rect x="300" y="210" width="80" height="3" fill="#333"/>
            <rect x="500" y="210" width="80" height="3" fill="#333"/>
            <rect x="700" y="210" width="80" height="3" fill="#333"/>
            <rect x="900" y="210" width="80" height="3" fill="#333"/>
            <rect x="1100" y="210" width="80" height="3" fill="#333"/>
            <rect x="1300" y="210" width="80" height="3" fill="#333"/>
        </svg>
    </div>

    <!-- Coords -->
    <div class="gta-404__coords">
        404.00°N — 0.00°E<br>
        WANTED_LVL: ★★★★★<br>
        STATUS: PAGE_NOT_FOUND
    </div>

</div>

<?php get_footer(); ?>
