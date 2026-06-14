<?php
session_start();
if (!isset($_SESSION['admin_connecte'])) { header('Location: login.php'); exit; }
require_once 'db.php';
try {
    $s = $pdo->query("SELECT COUNT(*) AS total, SUM(etat=1) AS presents, SUM(etat=0) AS absents FROM outils")->fetch();
    $total=(int)($s['total']??0); $pres=(int)($s['presents']??0); $abs=(int)($s['absents']??0);
    $outils=$pdo->query("SELECT id,uid_nfc,nom_outil,etat FROM outils ORDER BY nom_outil")->fetchAll();
} catch(PDOException $e){ die(htmlspecialchars($e->getMessage())); }
?>
<!DOCTYPE html><html lang="fr"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Tableau de bord – SmartRack</title>
<script src="https://cdn.tailwindcss.com"></script>
</head><body>
<?php require_once 'sidebar.php'; ?>
<main>
  <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:12px;">
    <div class="page-header" style="margin:0;">
      <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--g400);margin-bottom:4px;">SmartRack</p>
      <h1>Tableau de bord</h1>
      <p class="page-sub">Supervision en temps réel</p>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:8px;padding:8px 14px;">
      <span id="ldot" class="ldot" style="width:7px;height:7px;border-radius:50%;background:var(--gn);display:inline-block;flex-shrink:0;"></span>
      <span id="llabel" style="font-size:12px;font-weight:500;color:var(--gn);">En direct</span>
      <span style="color:var(--g200);margin:0 2px;">·</span>
      <span id="ltime" style="font-size:12px;color:var(--g400);"><?=date('d/m/Y H:i:s')?></span>
    </div>
  </div>

  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:28px;">
    <div class="card" style="padding:20px 24px;">
      <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--g400);margin-bottom:10px;">Total outils</p>
      <p id="st" class="val" style="font-size:32px;font-weight:600;"><?=$total?></p>
    </div>
    <div class="card" style="padding:20px 24px;">
      <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--g400);margin-bottom:10px;">Dans le rack</p>
      <p id="sp" class="val" style="font-size:32px;font-weight:600;color:var(--gn);"><?=$pres?></p>
    </div>
    <div class="card" style="padding:20px 24px;">
      <p style="font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--g400);margin-bottom:10px;">Empruntés</p>
      <p id="sa" class="val" style="font-size:32px;font-weight:600;color:var(--or);"><?=$abs?></p>
    </div>
  </div>

  <div class="card" style="overflow:hidden;">
    <div class="section-header">
      <div><p style="font-size:14px;font-weight:500;">État des outils</p><p style="font-size:12px;color:var(--g400);margin-top:2px;">Actualisation toutes les 1,5 s</p></div>
      <span id="uc" class="tag">Initialisation…</span>
    </div>
    <table>
      <thead><tr><th>ID</th><th>UID NFC</th><th>Nom</th><th class="tc">Statut</th></tr></thead>
      <tbody id="tb">
        <?php if(empty($outils)): ?>
          <tr id="er"><td colspan="4" style="text-align:center;color:var(--g400);padding:36px;">Aucun outil enregistré.</td></tr>
        <?php else: foreach($outils as $o): ?>
          <tr data-id="<?=(int)$o['id']?>" data-etat="<?=(int)$o['etat']?>">
            <td style="color:var(--g400);"><?=(int)$o['id']?></td>
            <td><code class="uid"><?=htmlspecialchars($o['uid_nfc'])?></code></td>
            <td style="font-weight:500;"><?=htmlspecialchars($o['nom_outil'])?></td>
            <td style="text-align:center;">
              <span class="badge <?=$o['etat']==1?'badge-green':'badge-or'?>"><span class="dot"></span><?=$o['etat']==1?'Présent':'Absent'?></span>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</main>

<script>
(function(){
  const et=document.getElementById('st'),ep=document.getElementById('sp'),ea=document.getElementById('sa');
  const tb=document.getElementById('tb'),uc=document.getElementById('uc'),lt=document.getElementById('ltime');
  const ld=document.getElementById('ldot'),ll=document.getElementById('llabel');
  let n=0,etats=new Map();
  tb.querySelectorAll('tr[data-id]').forEach(r=>etats.set(+r.dataset.id,+r.dataset.etat));

  function esc(s){const d=document.createElement('div');d.appendChild(document.createTextNode(String(s??'')));return d.innerHTML;}
  function fw(el){el.classList.add('flash');setTimeout(()=>el.classList.remove('flash'),200);}
  function badge(e){return`<span class="badge ${e===1?'badge-green':'badge-or'}"><span class="dot"></span>${e===1?'Présent':'Absent'}</span>`;}
  function row(o,n){return`<tr class="${n?'row-new':''}" data-id="${o.id}" data-etat="${o.etat}" style="border-bottom:var(--b)"><td style="color:var(--g400)">${o.id}</td><td><code class="uid">${esc(o.uid_nfc)}</code></td><td style="font-weight:500">${esc(o.nom_outil)}</td><td style="text-align:center">${badge(o.etat)}</td></tr>`;}
  function live(ok){ld.style.background=ok?'var(--gn)':'var(--rd)';ll.style.color=ok?'var(--gn)':'var(--rd)';ll.textContent=ok?'En direct':'Hors ligne';}

  function go(){
    fetch('fetch_data.php').then(r=>r.ok?r.json():Promise.reject()).then(d=>{
      if(!d.succes)throw 0;
      live(true);lt.textContent=d.timestamp;
      if(+et.textContent!==d.stats.total){fw(et);et.textContent=d.stats.total;}
      if(+ep.textContent!==d.stats.presents){fw(ep);ep.textContent=d.stats.presents;}
      if(+ea.textContent!==d.stats.absents){fw(ea);ea.textContent=d.stats.absents;}
      const nd=new Map();d.outils.forEach(o=>nd.set(o.id,o));
      tb.querySelectorAll('tr[data-id]').forEach(r=>{if(!nd.has(+r.dataset.id)){r.remove();etats.delete(+r.dataset.id);}});
      if(d.outils.length){const er=document.getElementById('er');if(er)er.remove();}
      d.outils.forEach(o=>{
        const ex=tb.querySelector(`tr[data-id="${o.id}"]`);
        if(!ex){tb.insertAdjacentHTML('beforeend',row(o,true));etats.set(o.id,o.etat);}
        else if(etats.get(o.id)!==o.etat){ex.querySelector('td:last-child').innerHTML=badge(o.etat);ex.dataset.etat=o.etat;ex.classList.remove('row-changed');void ex.offsetWidth;ex.classList.add('row-changed');etats.set(o.id,o.etat);}
      });
      if(!d.outils.length&&!document.getElementById('er'))tb.innerHTML='<tr id="er"><td colspan="4" style="text-align:center;color:var(--g400);padding:36px;">Aucun outil enregistré.</td></tr>';
      uc.textContent='Mise à jour #'+(++n)+' — '+d.timestamp;
    }).catch(()=>{live(false);uc.textContent='Reconnexion…';});
  }
  go();setInterval(go,1500);
})();
</script>
</body></html>