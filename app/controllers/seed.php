<?php

class seed {
  private function requireAuth() {
    if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
  }
  private function tenantId(): int { return (int)($_SESSION['auth']['tenant_id'] ?? 1); }
  private function userId(): int { return (int)($_SESSION['auth']['id'] ?? 1); }
  private function json($data, int $code=200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); exit; }

  private function tableExists(PDO $dbh, string $table): bool {
    try { $st=$dbh->prepare("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?"); $st->execute([$table]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; }
  }
  private function hasColumn(PDO $dbh, string $table, string $col): bool {
    try { $st=$dbh->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?"); $st->execute([$table,$col]); return (bool)$st->fetch(); } catch(Throwable $e){ return false; }
  }

  // POST or GET /seed/demo (dev convenience)
  public function demo(){
    $this->requireAuth();
    if (!in_array($_SERVER['REQUEST_METHOD'], ['POST','GET'], true)) return $this->json(['error'=>'Method not allowed'],405);
    try {
      $dbh = db_connect();
      $tenantId = $this->tenantId();
      $userId = $this->userId();
      $today = new DateTime('now');
      $ym = $today->format('Y-m');

      // Week start (Monday)
      $dow = (int)$today->format('N');
      $monday = clone $today; $monday->modify('-'.($dow-1).' days');
      $weekStart = $monday->format('Y-m-d');
      $weekEnd = (clone $monday)->modify('+7 days')->format('Y-m-d');

      // 1) Ensure budgets in tenant_settings
      if ($this->tableExists($dbh,'tenant_settings')) {
        $hasN = $this->hasColumn($dbh,'tenant_settings','weekly_budget_normal_cents');
        $hasT = $this->hasColumn($dbh,'tenant_settings','weekly_budget_travel_cents');
        if ($hasN || $hasT) {
          $st = $dbh->prepare('INSERT INTO tenant_settings (tenant_id, weekly_budget_normal_cents, weekly_budget_travel_cents) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE weekly_budget_normal_cents=VALUES(weekly_budget_normal_cents), weekly_budget_travel_cents=VALUES(weekly_budget_travel_cents)');
          $st->execute([$tenantId, 15000, 0]); // $150.00 normal, $0 travel
        }
      }

      // 2) Seed expenses (two normal-mode expenses this week) if none exist
      if ($this->tableExists($dbh,'expenses')) {
        $st=$dbh->prepare('SELECT COUNT(*) c FROM expenses WHERE tenant_id=? AND date>=? AND date<?');
        $st->execute([$tenantId,$weekStart,$weekEnd]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          $insCols = ['tenant_id','date','amount_cents','mode','note'];
          $vals = [[$tenantId, $monday->format('Y-m-d'), 4500, 'normal', 'Groceries'], [$tenantId, (clone $monday)->modify('+2 days')->format('Y-m-d'), 2000, 'normal', 'Transport']];
          $sql = 'INSERT INTO expenses ('.implode(',',$insCols).') VALUES (?,?,?,?,?)';
          $ins=$dbh->prepare($sql);
          foreach($vals as $v){ try{ $ins->execute($v); }catch(Throwable $e){} }
        }
      }

      // 3) Seed pay run (this month) if none exists
      if ($this->tableExists($dbh,'pay_runs')) {
        $start = $ym.'-01'; $end = (new DateTime($start))->modify('+1 month')->format('Y-m-d');
        $st=$dbh->prepare('SELECT COUNT(*) c FROM pay_runs WHERE tenant_id=? AND period_end>=? AND period_end<?');
        $st->execute([$tenantId,$start,$end]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          $st2=$dbh->prepare('INSERT INTO pay_runs (tenant_id, employer_id, period_start, period_end, gross_cents, net_cents) VALUES (?,?,?,?,?,?)');
          $st2->execute([$tenantId, null, $start, $ym.'-15', 90000, 81000]); // $900 gross, $810 net
        }
      }

      // 4) Seed income row (bonus) if income table exists and none this month
      if ($this->tableExists($dbh,'income')) {
        $start = $ym.'-01'; $end = (new DateTime($start))->modify('+1 month')->format('Y-m-d');
        $hasCents = $this->hasColumn($dbh,'income','amount_cents');
        $st=$dbh->prepare('SELECT COUNT(*) c FROM income WHERE tenant_id=? AND date>=? AND date<?');
        $st->execute([$tenantId,$start,$end]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          if ($hasCents) {
            $dbh->prepare('INSERT INTO income (tenant_id, date, amount_cents, note) VALUES (?,?,?,?)')->execute([$tenantId, $ym.'-10', 25000, 'Bonus']);
          } else {
            $dbh->prepare('INSERT INTO income (tenant_id, date, amount, note) VALUES (?,?,?,?)')->execute([$tenantId, $ym.'-10', 250.00, 'Bonus']);
          }
        }
      }

      // 4b) Seed a shift (today)
      if ($this->tableExists($dbh,'shifts')) {
        $st=$dbh->prepare('SELECT COUNT(*) c FROM shifts WHERE tenant_id=? AND date=?');
        $d=$today->format('Y-m-d'); $st->execute([$tenantId,$d]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          $cols=['tenant_id','date','start_time','end_time','break_minutes','role','rate','tips'];
          $dbh->prepare('INSERT INTO shifts (tenant_id,date,start_time,end_time,break_minutes,role,rate,tips) VALUES (?,?,?,?,?,?,?,?)')
              ->execute([$tenantId,$d,'09:00:00','17:00:00',30,'Server',20.00,0.00]);
        }
      }

      // 4c) Seed a transfer (this month)
      if ($this->tableExists($dbh,'transfers')) {
        $start=$ym.'-01'; $end=(new DateTime($start))->modify('+1 month')->format('Y-m-d');
        $hasCents = $this->hasColumn($dbh,'transfers','amount_cents');
        $st=$dbh->prepare('SELECT COUNT(*) c FROM transfers WHERE tenant_id=? AND date>=? AND date<?');
        $st->execute([$tenantId,$start,$end]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          if ($hasCents) {
            $dbh->prepare('INSERT INTO transfers (tenant_id, date, amount_cents, note) VALUES (?,?,?,?)')->execute([$tenantId,$ym.'-12',5000,'Move to savings']);
          } else {
            $dbh->prepare('INSERT INTO transfers (tenant_id, date, amount, note) VALUES (?,?,?,?)')->execute([$tenantId,$ym.'-12',50.00,'Move to savings']);
          }
        }
      }

      // 4d) Seed investment account and holdings
      if ($this->tableExists($dbh,'investment_accounts')) {
        $st=$dbh->prepare('SELECT id FROM investment_accounts WHERE tenant_id=? LIMIT 1'); $st->execute([$tenantId]); $acc=$st->fetch();
        if (!$acc) {
          if ($this->hasColumn($dbh,'investment_accounts','value_cents')) {
            $dbh->prepare('INSERT INTO investment_accounts (tenant_id,name,type,value_cents) VALUES (?,?,?,?)')->execute([$tenantId,'Brokerage','TFSA', 500000]);
          } else {
            $dbh->prepare('INSERT INTO investment_accounts (tenant_id,name,type,value) VALUES (?,?,?,?)')->execute([$tenantId,'Brokerage','TFSA', 5000.00]);
          }
          $st=$dbh->prepare('SELECT id FROM investment_accounts WHERE tenant_id=? ORDER BY id DESC LIMIT 1'); $st->execute([$tenantId]); $acc=$st->fetch();
        }
        if ($acc && $this->tableExists($dbh,'investments')) {
          $st=$dbh->prepare('SELECT COUNT(*) c FROM investments WHERE tenant_id=? AND account_id=?'); $st->execute([$tenantId,$acc['id']]); $cnt=(int)($st->fetch()['c']??0);
          if ($cnt===0) {
            $dbh->prepare('INSERT INTO investments (tenant_id, account_id, name, symbol, quantity, price) VALUES (?,?,?,?,?,?)')
                ->execute([$tenantId,$acc['id'],'Apple Inc.','AAPL', 2.0, 200.00]);
          }
        }
      }

      // 5) Optional snapshots: one debt and one savings goal if absent
      if ($this->tableExists($dbh,'debts')) {
        $st=$dbh->prepare('SELECT COUNT(*) c FROM debts WHERE tenant_id=?'); $st->execute([$tenantId]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          if ($this->hasColumn($dbh,'debts','balance_cents')) {
            $dbh->prepare('INSERT INTO debts (tenant_id, lender, type, balance_cents, min_payment_cents) VALUES (?,?,?,?,?)')->execute([$tenantId,'Visa','credit', 120000, 3000]);
          } else {
            $dbh->prepare('INSERT INTO debts (tenant_id, lender, type, balance, min_payment) VALUES (?,?,?,?,?)')->execute([$tenantId,'Visa','credit', 1200.00, 30.00]);
          }
        }
      }
      if ($this->tableExists($dbh,'savings_goals')) {
        $st=$dbh->prepare('SELECT COUNT(*) c FROM savings_goals WHERE tenant_id=?'); $st->execute([$tenantId]); $cnt=(int)($st->fetch()['c']??0);
        if ($cnt===0) {
          if ($this->hasColumn($dbh,'savings_goals','target_cents')) {
            $dbh->prepare('INSERT INTO savings_goals (tenant_id, name, target_cents, saved_cents) VALUES (?,?,?,?)')->execute([$tenantId,'Emergency Fund', 1000000, 150000]);
          } else {
            $dbh->prepare('INSERT INTO savings_goals (tenant_id, name, target, saved) VALUES (?,?,?,?)')->execute([$tenantId,'Emergency Fund', 10000.00, 1500.00]);
          }
        }
      }

      // 6) Recompute overview cache for current month
      try { $ch = curl_init(); curl_setopt_array($ch,[CURLOPT_URL=>urljoin('/overview_api/save'),CURLOPT_POST=>1,CURLOPT_RETURNTRANSFER=>true,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode(['month'=>$ym])]); curl_exec($ch); curl_close($ch); } catch(Throwable $e) { /* ignore */ }

      return $this->json(['ok'=>true,'message'=>'Demo data seeded','month'=>$ym]);
    } catch (Throwable $e) { return $this->json(['ok'=>false,'error'=>$e->getMessage()],500); }
  }
}

if (!function_exists('urljoin')){
  function urljoin(string $path): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme.'://'.$host.$path;
  }
}
