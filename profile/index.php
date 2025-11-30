
<?php
require_once __DIR__ . '/../config/db.php';
include __DIR__ . '/../layout/header.php';

$stmt = $pdo->query("SELECT p.profileid, a.email, p.displayname, p.gender, p.birthdate, p.locationat, p.locationon, p.prefgender, p.prefagemin, p.prefagemax
 FROM profile p JOIN account a ON a.accountid = p.accountid
 ORDER BY p.profileid");
$rows = $stmt->fetchAll();
?>
<div class="card">
  <h2 style="margin-top:0">Daftar Profile</h2>
  <p>
    <a class="btn btn-primary" href="create.php">+ Tambah Profile</a>
    <input id="search" placeholder="Cari nama atau email..." style="margin-left:12px;padding:8px;border-radius:8px;border:1px solid #eee;max-width:320px">
    <button id="surprise" class="btn" style="margin-left:8px">Surprise</button>
  </p>

  <style>
    .profiles-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;margin-top:14px}
    .profile-card{display:flex;flex-direction:column;align-items:start;padding:12px;border-radius:10px;border:1px solid #f1e6ea;background:linear-gradient(180deg,#fff,#fff);transition:transform .18s,box-shadow .18s;position:relative}
    .profile-card:hover{transform:translateY(-6px);box-shadow:0 8px 20px rgba(0,0,0,.08)}
    .avatar{width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--pink),var(--pink-dark));color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:20px;margin-bottom:8px}
    .info h3{margin:0 0 6px 0;font-size:16px}
    .info p{margin:0 0 6px 0}
    .badge{display:inline-block;padding:5px 8px;border-radius:999px;background:#f3f3f3;color:#333;margin-right:6px;font-size:12px}
    .actions{margin-top:8px;display:flex;gap:8px;width:100%}
    .profile-card .muted{font-size:13px;color:#666}
    .highlight{box-shadow:0 10px 30px rgba(233,30,99,.25);outline:3px solid rgba(233,30,99,.08)}
  </style>

  <div class="profiles-grid" id="profiles">
    <?php
    function initials($name){
      $name = trim($name);
      if($name === '') return '';
      $parts = preg_split('/\s+/', $name);
      $initials = '';
      foreach($parts as $p){
        $initials .= mb_substr($p,0,1);
        if(mb_strlen($initials) >= 2) break;
      }
      return strtoupper($initials);
    }
    function age_from_dob($dob){
      if(!$dob) return '';
      try{ $d = new DateTime($dob); $now = new DateTime(); return $now->diff($d)->y; }catch(Exception $e){return '';}
    }

    foreach ($rows as $r):
      $age = age_from_dob($r['birthdate']);
      $initial = initials($r['displayname'] ?: $r['email']);
      $dataName = strtolower($r['displayname'] . ' ' . $r['email']);
    ?>
      <div class="profile-card" data-name="<?= htmlspecialchars($dataName) ?>">
        <div class="avatar"><?= htmlspecialchars($initial) ?></div>
        <div class="info">
          <h3><?= htmlspecialchars($r['displayname']) ?></h3>
          <p class="muted"><?= htmlspecialchars($r['email']) ?></p>
          <p>
            <span class="badge"><?= htmlspecialchars($r['gender']) ?></span>
            <span class="badge"><?= htmlspecialchars($r['locationat']) ?></span>
            <span class="badge">Age: <?= htmlspecialchars($age) ?></span>
          </p>
          <p class="muted">Pref: <?= htmlspecialchars($r['prefgender']) ?>, <?= htmlspecialchars($r['prefagemin']) ?>–<?= htmlspecialchars($r['prefagemax']) ?></p>
        </div>
        <div class="actions">
          <a class="btn" href="edit.php?id=<?= urlencode($r['profileid']) ?>">Edit</a>
          <form action="delete.php" method="POST" style="display:inline;margin-left:auto" onsubmit="return confirm('Yakin hapus profile ini?');">
            <input type="hidden" name="id" value="<?= htmlspecialchars($r['profileid']) ?>">
            <button class="btn btn-danger" type="submit">Delete</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <script>
    (function(){
      const search = document.getElementById('search');
      const grid = document.getElementById('profiles');
      const cards = Array.from(grid.children);
      search.addEventListener('input', function(){
        const q = this.value.trim().toLowerCase();
        cards.forEach(c => {
          const name = c.getAttribute('data-name') || '';
          c.style.display = name.indexOf(q) !== -1 ? '' : 'none';
        });
      });
      document.getElementById('surprise').addEventListener('click', function(){
        const visible = cards.filter(c => c.style.display !== 'none');
        if(!visible.length) return alert('Tidak ada profile yang cocok.');
        visible.forEach(c => c.classList.remove('highlight'));
        const pick = visible[Math.floor(Math.random()*visible.length)];
        pick.classList.add('highlight');
        setTimeout(()=> pick.classList.remove('highlight'), 2500);
        pick.scrollIntoView({behavior:'smooth',block:'center'});
      });
    })();
  </script>

</div>
<?php include __DIR__ . '/../layout/footer.php'; ?>
