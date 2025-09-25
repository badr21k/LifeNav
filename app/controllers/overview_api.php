<?php

class overview_api extends Controller {
  private function requireAuth() { if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; } }
  private function tenantId(): int { return (int)($_SESSION['auth']['tenant_id'] ?? 1); }
  private function userId(): int { return (int)($_SESSION['auth']['id'] ?? 0); }
  private function json($data, int $code=200): void { http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); exit; }
  private function bodyJson(): array { $raw=file_get_contents('php://input'); $j=json_decode($raw,true); return is_array($j)?$j:[]; }

  private function ensureTable(PDO $dbh): void {
    // Emergency compatibility: do nothing here. The table has been created manually in DB.
    return;
  }

  // GET /overview_api/series
  // Returns monthly aggregates from the earliest activity month to current for the tenant
  public function series() {
    $this->requireAuth();
    try {
      $dbh = db_connect();
      $tenantId = $this->tenantId();

      $minDates = [];
      if ($this->tableExists($dbh,'expenses')) {
        $st=$dbh->prepare("SELECT MIN(date) m FROM expenses WHERE tenant_id=?"); $st->execute([$tenantId]); $r=$st->fetch(); if(!empty($r['m'])) $minDates[]=$r['m'];
      }
      if ($this->tableExists($dbh,'pay_runs')) {
        $st=$dbh->prepare("SELECT MIN(period_end) m FROM pay_runs WHERE tenant_id=?"); $st->execute([$tenantId]); $r=$st->fetch(); if(!empty($r['m'])) $minDates[]=$r['m'];
      }
      if ($this->tableExists($dbh,'income')) {
        $st=$dbh->prepare("SELECT MIN(date) m FROM income WHERE tenant_id=?"); $st->execute([$tenantId]); $r=$st->fetch(); if(!empty($r['m'])) $minDates[]=$r['m'];
      }

      $startYm = $minDates ? date('Y-m', strtotime(min($minDates))) : date('Y-m');
      $endYm = date('Y-m');

      // Grouped sums per month
      $incomeByYm = [];
      if ($this->tableExists($dbh,'income')) {
        if ($this->hasColumn($dbh,'income','amount_cents')) {
          $q=$dbh->prepare("SELECT DATE_FORMAT(date,'%Y-%m') ym, SUM(amount_cents) s FROM income WHERE tenant_id=? GROUP BY ym");
          $q->execute([$tenantId]); foreach($q->fetchAll() as $row){ $incomeByYm[$row['ym']] = ((int)$row['s']); }
        } elseif ($this->hasColumn($dbh,'income','amount')) {
          $q=$dbh->prepare("SELECT DATE_FORMAT(date,'%Y-%m') ym, SUM(amount) s FROM income WHERE tenant_id=? GROUP BY ym");
          $q->execute([$tenantId]); foreach($q->fetchAll() as $row){ $incomeByYm[$row['ym']] = (int)round(((float)$row['s'])*100); }
        }
      }

      $payByYm = [];
      if ($this->tableExists($dbh,'pay_runs')) {
        $q=$dbh->prepare("SELECT DATE_FORMAT(period_end,'%Y-%m') ym, SUM(net_cents) s FROM pay_runs WHERE tenant_id=? GROUP BY ym");
        $q->execute([$tenantId]); foreach($q->fetchAll() as $row){ $payByYm[$row['ym']] = ((int)$row['s']); }
      }

      $spendByYm = [];
      if ($this->tableExists($dbh,'expenses')) {
        $q=$dbh->prepare("SELECT DATE_FORMAT(date,'%Y-%m') ym, SUM(amount_cents) s FROM expenses WHERE tenant_id=? GROUP BY ym");
        $q->execute([$tenantId]); foreach($q->fetchAll() as $row){ $spendByYm[$row['ym']] = ((int)$row['s']); }
      }

      // Build continuous series from startYm to endYm
      $labels=[]; $income=[]; $paycheck=[]; $spending=[];
      $cursor = strtotime($startYm.'-01');
      $end = strtotime($endYm.'-01');
      while ($cursor <= $end) {
        $ym = date('Y-m', $cursor);
        $labels[] = $ym;
        $income[] = (($incomeByYm[$ym] ?? 0) + ($payByYm[$ym] ?? 0))/100; // total income = income + payruns
        $paycheck[] = ($payByYm[$ym] ?? 0)/100; // paycheck = payruns only
        $spending[] = ($spendByYm[$ym] ?? 0)/100;
        $cursor = strtotime('+1 month', $cursor);
      }

      return $this->json(['ok'=>true,'data'=>compact('labels','income','paycheck','spending')]);
    } catch (Throwable $e) { return $this->json(['ok'=>false,'error'=>$e->getMessage()],500); }
  }

  // GET /overview_api/diagnose/<YYYY-MM>
  public function diagnose($monthParam=null) {
    $this->requireAuth();
    try {
      $dbh=db_connect(); $tenantId=$this->tenantId();
      $month = $monthParam && preg_match('/^\d{4}-\d{2}$/',$monthParam) ? $monthParam : date('Y-m');
      $start=$month.'-01'; $end=date('Y-m-d', strtotime($start.' +1 month'));

      $out = [ 'tenant_id'=>$tenantId, 'month'=>$month ];
      // pay_runs
      if ($this->tableExists($dbh,'pay_runs')) {
        $st=$dbh->prepare('SELECT COALESCE(SUM(net_cents),0) s FROM pay_runs WHERE tenant_id=? AND period_end>=? AND period_end<?');
        $st->execute([$tenantId,$start,$end]); $out['pay_runs_net_cents']=(int)($st->fetch()['s']??0);
      }
      // income
      if ($this->tableExists($dbh,'income')) {
        if ($this->hasColumn($dbh,'income','amount_cents')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM income WHERE tenant_id=? AND date>=? AND date<?');
          $st->execute([$tenantId,$start,$end]); $out['income_amount_cents']=(int)($st->fetch()['s']??0);
        } elseif ($this->hasColumn($dbh,'income','amount')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(amount),0) s FROM income WHERE tenant_id=? AND date>=? AND date<?');
          $st->execute([$tenantId,$start,$end]); $out['income_amount_cents']=(int)round(((float)($st->fetch()['s']??0))*100);
        }
      }
      // expenses
      if ($this->tableExists($dbh,'expenses')) {
        $st=$dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<?');
        $st->execute([$tenantId,$start,$end]); $out['expenses_amount_cents']=(int)($st->fetch()['s']??0);
      }
      // debts
      if ($this->tableExists($dbh,'debts')) {
        if ($this->hasColumn($dbh,'debts','min_payment_cents')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(min_payment_cents),0) s FROM debts WHERE tenant_id=?');
          $st->execute([$tenantId]); $out['debts_min_cents']=(int)($st->fetch()['s']??0);
        } elseif ($this->hasColumn($dbh,'debts','min_payment')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(min_payment),0) s FROM debts WHERE tenant_id=?');
          $st->execute([$tenantId]); $out['debts_min_cents']=(int)round(((float)($st->fetch()['s']??0))*100);
        }
      }
      return $this->json(['ok'=>true,'data'=>$out]);
    } catch (Throwable $e) { return $this->json(['ok'=>false,'error'=>$e->getMessage()],500); }
  }

  private function tableExists(PDO $dbh, string $table): bool {
    try { $st=$dbh->query("SHOW TABLES LIKE ".$dbh->quote($table)); return (bool)$st->fetch(); } catch (Throwable $e) { return false; }
  }

  private function hasColumn(PDO $dbh, string $table, string $column): bool {
    try { $st=$dbh->query("SHOW COLUMNS FROM `{$table}` LIKE ".$dbh->quote($column)); return (bool)$st->fetch(); } catch (Throwable $e) { return false; }
  }

  private function defaultCurrency(PDO $dbh, int $tenantId): string {
    if ($this->tableExists($dbh,'tenant_settings')) {
      $st=$dbh->prepare('SELECT default_currency FROM tenant_settings WHERE tenant_id=?'); $st->execute([$tenantId]);
      $r=$st->fetch(); if ($r && !empty($r['default_currency'])) return $r['default_currency'];
    }
    return 'CAD';
  }

  public function save($monthParam=null) {
    $this->requireAuth();
    try {
      $dbh = db_connect(); $this->ensureTable($dbh);
      $tenantId=$this->tenantId(); $userId=$this->userId();
      $b=$this->bodyJson();
      $ymBody = isset($b['month']) && preg_match('/^\d{4}-\d{2}$/',$b['month']) ? $b['month'] : null;
      $ym = $monthParam && preg_match('/^\d{4}-\d{2}$/',$monthParam) ? $monthParam : ($ymBody ?: date('Y-m'));
      $start=$ym.'-01'; $end=date('Y-m-d', strtotime($start.' +1 month'));

      // Currency
      $currency = $this->defaultCurrency($dbh,$tenantId);

      // Income (month): sum of income.amount_cents and pay_runs.net_cents within month
      $income_cents = 0;
      if ($this->tableExists($dbh,'income')) {
        // try cents column first
        if ($this->hasColumn($dbh,'income','amount_cents')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM income WHERE tenant_id=? AND date>=? AND date<?');
          $st->execute([$tenantId,$start,$end]); $income_cents += (int)($st->fetch()['s'] ?? 0);
        } elseif ($this->hasColumn($dbh,'income','amount')) {
          $st=$dbh->prepare('SELECT COALESCE(SUM(amount),0) s FROM income WHERE tenant_id=? AND date>=? AND date<?');
          $st->execute([$tenantId,$start,$end]); $income_cents += (int)round(((float)($st->fetch()['s'] ?? 0))*100);
        }
      }
      $st=$dbh->prepare('SELECT COALESCE(SUM(net_cents),0) s FROM pay_runs WHERE tenant_id=? AND period_end>=? AND period_end<?');
      $st->execute([$tenantId,$start,$end]); $payruns_net_cents=(int)($st->fetch()['s'] ?? 0);
      $income_cents += $payruns_net_cents;

      // Spending (month): sum of expenses.amount_cents within month with mode split (if available)
      $spending_cents=0; $weeklySpentCents=0; $weeklySpentTravelCents=0; $catsNormal=[]; $catsTravel=[]; $totalNormalCents=0; $totalTravelCents=0;
      if ($this->tableExists($dbh,'expenses')) {
        $hasMode = $this->hasColumn($dbh,'expenses','mode');
        $q=$dbh->prepare('SELECT e.*, c.name AS category_name FROM expenses e LEFT JOIN categories c ON c.id=e.category_id WHERE e.tenant_id=? AND e.date>=? AND e.date<?');
        $q->execute([$tenantId,$start,$end]); $rows=$q->fetchAll();
        foreach ($rows as $r) {
          $amt=(int)$r['amount_cents']; $spending_cents += $amt;
          $cat = $r['category_name'] ?: 'Other';
          $mode = $hasMode ? ($r['mode'] ?: 'normal') : 'normal';
          if ($mode === 'travel') { $catsTravel[$cat] = ($catsTravel[$cat] ?? 0) + $amt; $totalTravelCents += $amt; }
          else { $catsNormal[$cat] = ($catsNormal[$cat] ?? 0) + $amt; $totalNormalCents += $amt; }
        }
        // Spent this week: use count_weekly flag when present within current week window, split by mode when available
        $hasCount = $this->hasColumn($dbh,'expenses','count_weekly');
        $wStart = date('Y-m-d', strtotime('monday this week'));
        $wEnd = date('Y-m-d', strtotime($wStart.' +6 days'));
        if ($hasMode) {
          $qN = $hasCount
            ? $dbh->prepare("SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=? AND (count_weekly=1 OR count_weekly IS NULL) AND mode='normal'")
            : $dbh->prepare("SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=? AND mode='normal'");
          $qT = $hasCount
            ? $dbh->prepare("SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=? AND (count_weekly=1 OR count_weekly IS NULL) AND mode='travel'")
            : $dbh->prepare("SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=? AND mode='travel'");
          $qN->execute([$tenantId,$wStart,$wEnd]); $weeklySpentCents = (int)($qN->fetch()['s'] ?? 0);
          $qT->execute([$tenantId,$wStart,$wEnd]); $weeklySpentTravelCents = (int)($qT->fetch()['s'] ?? 0);
        } else {
          $qq = $hasCount
            ? $dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=? AND (count_weekly=1 OR count_weekly IS NULL)')
            : $dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date>=? AND date<=?');
          $qq->execute([$tenantId,$wStart,$wEnd]); $weeklySpentCents=(int)($qq->fetch()['s'] ?? 0);
        }
      }

      // Debts: subtract monthly minimums (best-effort)
      $debts_cents = 0;
      if ($this->tableExists($dbh,'debts')) {
        if ($this->hasColumn($dbh,'debts','min_payment_cents')) {
          $ds=$dbh->prepare('SELECT COALESCE(SUM(min_payment_cents),0) s FROM debts WHERE tenant_id=?');
          $ds->execute([$tenantId]); $debts_cents = (int)($ds->fetch()['s'] ?? 0);
        } elseif ($this->hasColumn($dbh,'debts','min_payment')) {
          $ds=$dbh->prepare('SELECT COALESCE(SUM(min_payment),0) s FROM debts WHERE tenant_id=?');
          $ds->execute([$tenantId]); $debts_cents = (int)round(((float)($ds->fetch()['s'] ?? 0))*100);
        }
      }
      $net_cents = $income_cents - $spending_cents - $debts_cents;
      // paycheck = net from pay_runs only
      $paycheck_cents = $payruns_net_cents;

      // KPIs
      $weekly_budget_normal_cents = null; $weekly_budget_travel_cents = null;
      if ($this->tableExists($dbh,'tenant_settings')) {
        $ts=$dbh->prepare('SELECT weekly_budget_normal_cents, weekly_budget_travel_cents FROM tenant_settings WHERE tenant_id=?');
        $ts->execute([$tenantId]); $row=$ts->fetch();
        if ($row) { $weekly_budget_normal_cents = isset($row['weekly_budget_normal_cents'])?(int)$row['weekly_budget_normal_cents']:null; $weekly_budget_travel_cents = isset($row['weekly_budget_travel_cents'])?(int)$row['weekly_budget_travel_cents']:null; }
      }
      // Last 7 days spending series (total across modes)
      $weekSeries = [];
      try {
        for ($i = 6; $i >= 0; $i--) {
          $d = date('Y-m-d', strtotime('-'.$i.' days'));
          $sum = 0;
          if ($this->tableExists($dbh,'expenses')) {
            $hasCount = $this->hasColumn($dbh,'expenses','count_weekly');
            $stmt = $hasCount
              ? $dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date=? AND (count_weekly=1 OR count_weekly IS NULL)')
              : $dbh->prepare('SELECT COALESCE(SUM(amount_cents),0) s FROM expenses WHERE tenant_id=? AND date=?');
            $stmt->execute([$tenantId,$d]); $sum = (int)($stmt->fetch()['s'] ?? 0);
          }
          $weekSeries[] = round($sum/100,2);
        }
      } catch (Throwable $e) { $weekSeries = array_fill(0,7,0); }

      // Optional snapshots (best-effort)
      $debtsTotal = 0; $investmentsTotal = 0; $savingsTotal = 0;
      if ($this->tableExists($dbh,'debts')) {
        // try common columns: balance or balance_cents
        if ($this->hasColumn($dbh,'debts','balance_cents')) {
          $st2=$dbh->prepare('SELECT COALESCE(SUM(balance_cents),0) s FROM debts WHERE tenant_id=?'); $st2->execute([$tenantId]); $debtsTotal = ((int)($st2->fetch()['s']??0))/100;
        } else if ($this->hasColumn($dbh,'debts','balance')) {
          $st2=$dbh->prepare('SELECT COALESCE(SUM(balance),0) s FROM debts WHERE tenant_id=?'); $st2->execute([$tenantId]); $debtsTotal = (float)($st2->fetch()['s']??0);
        }
      }
      if ($this->tableExists($dbh,'investment_accounts')) {
        if ($this->hasColumn($dbh,'investment_accounts','value')) {
          $st3=$dbh->prepare('SELECT COALESCE(SUM(value),0) s FROM investment_accounts WHERE tenant_id=?'); $st3->execute([$tenantId]); $investmentsTotal = (float)($st3->fetch()['s']??0);
        }
      }
      if ($this->tableExists($dbh,'savings_goals')) {
        if ($this->hasColumn($dbh,'savings_goals','saved')) {
          $st4=$dbh->prepare('SELECT COALESCE(SUM(saved),0) s FROM savings_goals WHERE tenant_id=?'); $st4->execute([$tenantId]); $savingsTotal = (float)($st4->fetch()['s']??0);
        }
      }

      $kpis = [
        'weekly_budget' => $weekly_budget_normal_cents ? $weekly_budget_normal_cents/100 : 0,
        'weekly_budget_travel' => $weekly_budget_travel_cents ? $weekly_budget_travel_cents/100 : 0,
        'spent_this_week' => $weeklySpentCents/100,
        'spent_this_week_travel' => $weeklySpentTravelCents/100,
        'remaining_week' => ($weekly_budget_normal_cents ? max(0, $weekly_budget_normal_cents - $weeklySpentCents) : 0)/100,
        // Totals by mode
        'total_normal' => $totalNormalCents/100,
        'total_travel' => $totalTravelCents/100,
        // Snapshots
        'debts_total' => $debtsTotal,
        'investments_total' => $investmentsTotal,
        'savings_total' => $savingsTotal,
        // Weekly series
        'week_series' => $weekSeries,
      ];

      $totals = [
        'income_month' => $income_cents/100,
        'spending_month' => $spending_cents/100,
        'net_month' => $net_cents/100,
        'paycheck_month' => $paycheck_cents/100,
      ];

      // Build categories JSON (amounts in base currency units)
      $catsNOut = (object)[]; foreach ($catsNormal as $k=>$v){ $catsNOut->$k = round($v/100, 2); }
      $catsTOut = (object)[]; foreach ($catsTravel as $k=>$v){ $catsTOut->$k = round($v/100, 2); }

      // UPSERT row
      $st=$dbh->prepare('INSERT INTO monthly_summaries (tenant_id,user_id,year_month,currency,totals_json,categories_normal_json,categories_travel_json,kpis_json)
                         VALUES (?,?,?,?,?,?,?,?)
                         ON DUPLICATE KEY UPDATE currency=VALUES(currency), totals_json=VALUES(totals_json), categories_normal_json=VALUES(categories_normal_json), categories_travel_json=VALUES(categories_travel_json), kpis_json=VALUES(kpis_json)');
      $st->execute([$tenantId,$userId,$ym,$currency, json_encode($totals), json_encode($catsNOut), json_encode($catsTOut), json_encode($kpis)]);

      return $this->json(['ok'=>true]);
    } catch (Throwable $e) {
      return $this->json(['ok'=>false,'error'=>$e->getMessage()],500);
    }
  }

  public function get($monthParam=null) {
    $this->requireAuth();
    try {
      $dbh=db_connect(); $this->ensureTable($dbh); $tenantId=$this->tenantId(); $userId=$this->userId();
      $month = $monthParam && preg_match('/^\d{4}-\d{2}$/',$monthParam) ? $monthParam : trim($_GET['month'] ?? ''); if (!preg_match('/^\d{4}-\d{2}$/',$month)) $month = date('Y-m');
      $st=$dbh->prepare('SELECT * FROM monthly_summaries WHERE tenant_id=? AND user_id=? AND year_month=? LIMIT 1');
      $st->execute([$tenantId,$userId,$month]); $row=$st->fetch();
      if (!$row) {
        // Even if monthly_summaries is empty, return budgets from tenant_settings for UI
        $wbN = 0; $wbT = 0;
        if ($this->tableExists($dbh,'tenant_settings')) {
          $ts=$dbh->prepare('SELECT weekly_budget_normal_cents, weekly_budget_travel_cents FROM tenant_settings WHERE tenant_id=?');
          $ts->execute([$tenantId]); $r=$ts->fetch();
          if ($r) { $wbN = isset($r['weekly_budget_normal_cents']) ? ((int)$r['weekly_budget_normal_cents'])/100 : 0; $wbT = isset($r['weekly_budget_travel_cents']) ? ((int)$r['weekly_budget_travel_cents'])/100 : 0; }
        }
        return $this->json(['ok'=>true,'data'=>[
          'year_month'=>$month,
          'currency'=>$this->defaultCurrency($dbh,$tenantId),
          'totals'=>['income_month'=>0,'spending_month'=>0,'net_month'=>0,'paycheck_month'=>0],
          'categories_normal'=>new stdClass(),
          'categories_travel'=>new stdClass(),
          'kpis'=>['weekly_budget'=>$wbN,'weekly_budget_travel'=>$wbT,'spent_this_week'=>0,'spent_this_week_travel'=>0,'remaining_week'=>$wbN]
        ]]);
      }
      $data=[
        'year_month'=>$row['year_month'],
        'currency'=>$row['currency'] ?: $this->defaultCurrency($dbh,$tenantId),
        'totals'=> json_decode($row['totals_json'] ?: '{}', true),
        'categories_normal'=> json_decode($row['categories_normal_json'] ?: '{}', true),
        'categories_travel'=> json_decode($row['categories_travel_json'] ?: '{}', true),
        'kpis'=> json_decode($row['kpis_json'] ?: '{}', true),
      ];
      return $this->json(['ok'=>true,'data'=>$data]);
    } catch (Throwable $e) { return $this->json(['ok'=>false,'error'=>$e->getMessage()],500); }
  }
}
