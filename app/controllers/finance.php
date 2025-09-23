<?php

class Finance extends Controller {

    private function requireAuth() {
        if (!isset($_SESSION['auth'])) { header('Location: /login'); exit; }
    }

    /* -------------------- Budgets & Lines -------------------- */
    private function budgets(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $st=$dbh->prepare('SELECT * FROM budgets WHERE tenant_id=? AND id=?');
                    $st->execute([$tenantId,(int)$id]);
                    $row=$st->fetch();
                    if ($row) {
                        $q=$dbh->prepare('SELECT * FROM budget_lines WHERE budget_id=? ORDER BY id');
                        $q->execute([(int)$row['id']]);
                        $row['lines']=$q->fetchAll();
                    }
                    return $this->json($row ?: ['error'=>'Not found'], $row?200:404);
                } else {
                    $st=$dbh->prepare('SELECT * FROM budgets WHERE tenant_id=? ORDER BY id DESC LIMIT 200');
                    $st->execute([$tenantId]);
                    return $this->json($st->fetchAll());
                }
            case 'POST':
                $b=$this->bodyJson();
                $name=mb_substr(trim($b['name'] ?? ''),0,120);
                $period=in_array(($b['period_type'] ?? 'monthly'),['monthly','weekly','custom'],true)?$b['period_type']:'monthly';
                $start = isset($b['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$b['start_date']) ? $b['start_date'] : null;
                $end   = isset($b['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$b['end_date']) ? $b['end_date'] : null;
                $currency = strtoupper(mb_substr(trim($b['currency'] ?? 'CAD'),0,8));
                if ($name==='') return $this->json(['error'=>'Name required'],422);
                $st=$dbh->prepare('INSERT INTO budgets (tenant_id,name,period_type,start_date,end_date,currency) VALUES (?,?,?,?,?,?)');
                $st->execute([$tenantId,$name,$period,$start,$end,$currency]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson(); $id=(int)$id;
                $fields=[]; $vals=[];
                foreach(['name','period_type','start_date','end_date','currency'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE budgets SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?';
                $vals[]=$tenantId; $vals[]=$id;
                $st=$dbh->prepare($sql); $st->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $dbh->prepare('DELETE FROM budget_lines WHERE budget_id=?')->execute([(int)$id]);
                $st=$dbh->prepare('DELETE FROM budgets WHERE tenant_id=? AND id=?');
                $st->execute([$tenantId,(int)$id]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    private function budgetLines(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if (!$id) return $this->json(['error'=>'Budget ID required'],400);
                $bid=(int)$id;
                // optional: verify budget tenant
                $st=$dbh->prepare('SELECT id FROM budgets WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,$bid]);
                if(!$st->fetch()) return $this->json(['error'=>'Not found'],404);
                $q=$dbh->prepare('SELECT * FROM budget_lines WHERE budget_id=? ORDER BY id');
                $q->execute([$bid]);
                return $this->json($q->fetchAll());
            case 'POST':
                $b=$this->bodyJson();
                $bid=(int)($b['budget_id'] ?? 0);
                if($bid<=0) return $this->json(['error'=>'budget_id required'],422);
                $st=$dbh->prepare('SELECT id FROM budgets WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,$bid]);
                if(!$st->fetch()) return $this->json(['error'=>'Not found'],404);
                $name=mb_substr(trim($b['name'] ?? ''),0,120);
                $cat = isset($b['category_id']) && $b['category_id'] !== '' ? (int)$b['category_id'] : null;
                $sub = isset($b['subcategory_id']) && $b['subcategory_id'] !== '' ? (int)$b['subcategory_id'] : null;
                $planned = (int)($b['planned_cents'] ?? 0);
                if($name==='') return $this->json(['error'=>'Name required'],422);
                $q=$dbh->prepare('INSERT INTO budget_lines (budget_id,category_id,subcategory_id,name,planned_cents) VALUES (?,?,?,?,?)');
                $q->execute([$bid,$cat,$sub,$name,$planned]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                $lineId = (int)($id ?? 0);
                if($lineId<=0) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson();
                // tenant check via budget join
                $chk=$dbh->prepare('SELECT bl.id FROM budget_lines bl INNER JOIN budgets b ON b.id=bl.budget_id WHERE b.tenant_id=? AND bl.id=?');
                $chk->execute([$tenantId,$lineId]); if(!$chk->fetch()) return $this->json(['error'=>'Not found'],404);
                $fields=[]; $vals=[];
                foreach(['name'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(isset($b['planned_cents'])){ $fields[]='planned_cents=?'; $vals[]=(int)$b['planned_cents']; }
                if(isset($b['category_id'])){ $fields[]='category_id=?'; $vals[]=($b['category_id']!==''?(int)$b['category_id']:null); }
                if(isset($b['subcategory_id'])){ $fields[]='subcategory_id=?'; $vals[]=($b['subcategory_id']!==''?(int)$b['subcategory_id']:null); }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE budget_lines SET '.implode(',', $fields).' WHERE id=?';
                $vals[]=$lineId;
                $q=$dbh->prepare($sql); $q->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                $lineId=(int)($id ?? 0);
                if($lineId<=0) return $this->json(['error'=>'ID required'],400);
                $chk=$dbh->prepare('SELECT bl.id FROM budget_lines bl INNER JOIN budgets b ON b.id=bl.budget_id WHERE b.tenant_id=? AND bl.id=?');
                $chk->execute([$tenantId,$lineId]); if(!$chk->fetch()) return $this->json(['error'=>'Not found'],404);
                $dbh->prepare('DELETE FROM budget_lines WHERE id=?')->execute([$lineId]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* -------------------- Income -------------------- */
    private function income(PDO $dbh, int $tenantId, string $method, $id) {
        switch($method){
            case 'GET':
                if($id){
                    $st=$dbh->prepare('SELECT * FROM income WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]);
                    $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);
                }
                $st=$dbh->prepare('SELECT * FROM income WHERE tenant_id=? ORDER BY date DESC, id DESC LIMIT 500'); $st->execute([$tenantId]);
                return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson();
                $date = trim($b['date'] ?? ''); if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return $this->json(['error'=>'Invalid date'],422);
                $amount=(int)($b['amount_cents'] ?? 0); if($amount<=0) return $this->json(['error'=>'Invalid amount'],422);
                $source=mb_substr(trim($b['source'] ?? ''),0,120);
                $notes = mb_substr(trim($b['notes'] ?? ''),0,255);
                $currency=strtoupper(mb_substr(trim($b['currency'] ?? 'CAD'),0,8));
                $accountId = isset($b['account_id']) && $b['account_id']!=='' ? (int)$b['account_id'] : null;
                $st=$dbh->prepare('INSERT INTO income (tenant_id,date,amount_cents,source,notes,currency,account_id) VALUES (?,?,?,?,?,?,?)');
                $st->execute([$tenantId,$date,$amount,$source,$notes,$currency,$accountId]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson(); $id=(int)$id;
                $fields=[]; $vals=[];
                foreach(['date','source','notes','currency'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(isset($b['amount_cents'])){ $fields[]='amount_cents=?'; $vals[]=(int)$b['amount_cents']; }
                if(isset($b['account_id'])){ $fields[]='account_id=?'; $vals[]=($b['account_id']!==''?(int)$b['account_id']:null); }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE income SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?';
                $vals[]=$tenantId; $vals[]=$id; $st=$dbh->prepare($sql); $st->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400);
                $st=$dbh->prepare('DELETE FROM income WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* -------------------- Transfers -------------------- */
    private function transfers(PDO $dbh, int $tenantId, string $method, $id) {
        switch($method){
            case 'GET':
                if($id){ $st=$dbh->prepare('SELECT * FROM transfers WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}            
                $st=$dbh->prepare('SELECT * FROM transfers WHERE tenant_id=? ORDER BY date DESC, id DESC LIMIT 500'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson();
                $date = trim($b['date'] ?? ''); if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return $this->json(['error'=>'Invalid date'],422);
                $amount=(int)($b['amount_cents'] ?? 0); if($amount<=0) return $this->json(['error'=>'Invalid amount'],422);
                $from=(int)($b['from_account_id'] ?? 0); $to=(int)($b['to_account_id'] ?? 0);
                if($from<=0 || $to<=0 || $from===$to) return $this->json(['error'=>'Invalid accounts'],422);
                $notes=mb_substr(trim($b['notes'] ?? ''),0,255);
                $currency=strtoupper(mb_substr(trim($b['currency'] ?? 'CAD'),0,8));
                $st=$dbh->prepare('INSERT INTO transfers (tenant_id,date,amount_cents,from_account_id,to_account_id,notes,currency) VALUES (?,?,?,?,?,?,?)');
                $st->execute([$tenantId,$date,$amount,$from,$to,$notes,$currency]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson(); $id=(int)$id;
                $fields=[]; $vals=[];
                foreach(['date','notes','currency'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(isset($b['amount_cents'])){ $fields[]='amount_cents=?'; $vals[]=(int)$b['amount_cents']; }
                if(isset($b['from_account_id'])){ $fields[]='from_account_id=?'; $vals[]=(int)$b['from_account_id']; }
                if(isset($b['to_account_id'])){ $fields[]='to_account_id=?'; $vals[]=(int)$b['to_account_id']; }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE transfers SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?';
                $vals[]=$tenantId; $vals[]=$id; $st=$dbh->prepare($sql); $st->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400);
                $st=$dbh->prepare('DELETE FROM transfers WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }
    private function tenantId(): int { return (int)($_SESSION['auth']['tenant_id'] ?? 0); }
    private function json($data, int $code=200): void {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    private function bodyJson(): array {
        $raw = file_get_contents('php://input');
        $j = json_decode($raw, true);
        return is_array($j) ? $j : [];
    }

    public function index() {
        $this->view('finance/index');
        die;
    }

    // /finance/api/{resource}/{id?}
    public function api($resource = null, $id = null) {
        $this->requireAuth();

        try {
            // CSRF header check on mutating requests
            if (function_exists('csrf_verify_header')) csrf_verify_header('X-CSRF-Token');

            $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
            $tenantId = $this->tenantId();
            $dbh = db_connect();

            switch ($resource) {
                case 'ping':
                    return $this->json(['ok'=>true,'tenant_id'=>$tenantId]);
            case 'init':
                if ($method !== 'GET') return $this->json(['error'=>'Method not allowed'],405);
                $resp = $this->initData($dbh, $tenantId);
                return $this->json($resp);

            case 'rates':
                if ($method !== 'GET') return $this->json(['error'=>'Method not allowed'],405);
                $base = strtoupper(trim($_GET['base'] ?? 'CAD'));
                $rates = $this->fetchRates($base);
                return $this->json(['base'=>$base,'rates'=>$rates]);

            case 'accounts':
                return $this->accounts($dbh, $tenantId, $method, $id);

            case 'savings_goals':
                return $this->savingsGoals($dbh, $tenantId, $method, $id);

            case 'budgets':
                return $this->budgets($dbh, $tenantId, $method, $id);

            case 'budget_lines':
                return $this->budgetLines($dbh, $tenantId, $method, $id);

            case 'income':
                return $this->income($dbh, $tenantId, $method, $id);

            case 'transfers':
                return $this->transfers($dbh, $tenantId, $method, $id);

            case 'employers':
                return $this->employers($dbh, $tenantId, $method, $id);

            case 'payruns':
                return $this->payruns($dbh, $tenantId, $method, $id);

            case 'shifts':
                return $this->shifts($dbh, $tenantId, $method, $id);

            case 'debts':
                return $this->debts($dbh, $tenantId, $method, $id);

            case 'investment_accounts':
                return $this->investmentAccounts($dbh, $tenantId, $method, $id);

            case 'investments':
                return $this->investments($dbh, $tenantId, $method, $id);

            // TODO: reports
            }
            return $this->json(['error'=>'Not found'],404);
        } catch (Throwable $e) {
            return $this->json(['error'=>'Server error','message'=>$e->getMessage()],500);
        }
    }

    private function initData(PDO $dbh, int $tenantId): array {
        // categories
        if ($this->hasColumn($dbh,'categories','tenant_id')) {
            $st=$dbh->prepare('SELECT id,name FROM categories WHERE active=1 AND (tenant_id IS NULL OR tenant_id=?) ORDER BY id');
            $st->execute([$tenantId]);
            $categories=$st->fetchAll();
        } else {
            $categories=$dbh->query('SELECT id,name FROM categories WHERE active=1 ORDER BY id')->fetchAll();
        }
        // subcategories
        if ($this->hasColumn($dbh,'subcategories','tenant_id')) {
            $st=$dbh->prepare('SELECT id,category_id,name FROM subcategories WHERE active=1 AND (tenant_id IS NULL OR tenant_id=?) ORDER BY category_id,name');
            $st->execute([$tenantId]);
            $subcategories=$st->fetchAll();
        } else {
            $subcategories=$dbh->query('SELECT id,category_id,name FROM subcategories WHERE active=1 ORDER BY category_id,name')->fetchAll();
        }
        // payment methods
        if ($this->hasColumn($dbh,'payment_methods','tenant_id')) {
            $st=$dbh->prepare('SELECT id,name FROM payment_methods WHERE active=1 AND (tenant_id IS NULL OR tenant_id=?) ORDER BY id');
            $st->execute([$tenantId]);
            $payment_methods=$st->fetchAll();
        } else {
            $payment_methods=$dbh->query('SELECT id,name FROM payment_methods WHERE active=1 ORDER BY id')->fetchAll();
        }
        // accounts
        $st=$dbh->prepare('SELECT * FROM accounts WHERE tenant_id=? ORDER BY name');
        $st->execute([$tenantId]);
        $accounts=$st->fetchAll();
        // savings goals
        $st=$dbh->prepare('SELECT * FROM savings_goals WHERE tenant_id=? ORDER BY id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $goals=$st->fetchAll();

        // budgets with lines
        $st=$dbh->prepare('SELECT * FROM budgets WHERE tenant_id=? ORDER BY id DESC LIMIT 100');
        $st->execute([$tenantId]);
        $budgets=$st->fetchAll();
        $budgetIds = array_map(fn($b)=> (int)$b['id'], $budgets);
        $linesByBudget = [];
        if ($budgetIds) {
            $in = implode(',', array_fill(0, count($budgetIds), '?'));
            $q = $dbh->prepare("SELECT * FROM budget_lines WHERE budget_id IN ($in) ORDER BY id");
            $i=1; foreach ($budgetIds as $bid) $q->bindValue($i++,$bid,PDO::PARAM_INT);
            $q->execute();
            foreach ($q->fetchAll() as $r) { $linesByBudget[(int)$r['budget_id']][] = $r; }
        }
        foreach ($budgets as &$b) { $b['lines'] = $linesByBudget[(int)$b['id']] ?? []; }

        // income recent
        $st=$dbh->prepare('SELECT * FROM income WHERE tenant_id=? ORDER BY date DESC, id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $income=$st->fetchAll();

        // transfers recent
        $st=$dbh->prepare('SELECT * FROM transfers WHERE tenant_id=? ORDER BY date DESC, id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $transfers=$st->fetchAll();

        // employers
        $st=$dbh->prepare('SELECT * FROM employers WHERE tenant_id=? ORDER BY id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $employers=$st->fetchAll();

        // pay runs
        $st=$dbh->prepare('SELECT * FROM pay_runs WHERE tenant_id=? ORDER BY period_end DESC, id DESC LIMIT 300');
        $st->execute([$tenantId]);
        $payruns=$st->fetchAll();

        // shifts
        $st=$dbh->prepare('SELECT * FROM shifts WHERE tenant_id=? ORDER BY date DESC, id DESC LIMIT 500');
        $st->execute([$tenantId]);
        $shifts=$st->fetchAll();

        // debts
        $st=$dbh->prepare('SELECT * FROM debts WHERE tenant_id=? ORDER BY id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $debts=$st->fetchAll();

        // investment accounts
        $st=$dbh->prepare('SELECT * FROM investment_accounts WHERE tenant_id=? ORDER BY id DESC LIMIT 200');
        $st->execute([$tenantId]);
        $investment_accounts=$st->fetchAll();

        // investments
        $st=$dbh->prepare('SELECT * FROM investments WHERE tenant_id=? ORDER BY id DESC LIMIT 500');
        $st->execute([$tenantId]);
        $investments=$st->fetchAll();

        // default currency: pick first account currency or CAD
        $defaultCurrency = $accounts[0]['currency'] ?? 'CAD';

        return compact('categories','subcategories','payment_methods','accounts','goals','budgets','income','transfers','employers','payruns','shifts','debts','investment_accounts','investments','defaultCurrency');
    }

    /* ---------------- Employers ---------------- */
    private function employers(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM employers WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM employers WHERE tenant_id=? ORDER BY id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $name=mb_substr(trim($b['name']??''),0,120); $schedule=mb_substr(trim($b['pay_schedule']??'weekly'),0,40); $base=(float)($b['base_rate']??0);
                if($name==='') return $this->json(['error'=>'Name required'],422);
                $st=$dbh->prepare('INSERT INTO employers (tenant_id,name,pay_schedule,base_rate) VALUES (?,?,?,?)');
                $st->execute([$tenantId,$name,$schedule,$base]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['name','pay_schedule'] as $k){ if(isset($b[$k])){ $fields[ ]="$k=?"; $vals[]=$b[$k]; }}
                if(isset($b['base_rate'])){ $fields[]='base_rate=?'; $vals[]=(float)$b['base_rate']; }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE employers SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $st=$dbh->prepare('DELETE FROM employers WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* ---------------- Pay Runs ---------------- */
    private function payruns(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM pay_runs WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM pay_runs WHERE tenant_id=? ORDER BY period_end DESC, id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $eid=(int)($b['employer_id']??0); $ps=$b['period_start']??null; $pe=$b['period_end']??null; $gross=(int)($b['gross_cents']??0); $net=(int)($b['net_cents']??0);
                if($eid<=0 || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$ps??'') || !preg_match('/^\d{4}-\d{2}-\d{2}$/',$pe??'')) return $this->json(['error'=>'Invalid payload'],422);
                $st=$dbh->prepare('INSERT INTO pay_runs (tenant_id,employer_id,period_start,period_end,gross_cents,net_cents) VALUES (?,?,?,?,?,?)');
                $st->execute([$tenantId,$eid,$ps,$pe,$gross,$net]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['employer_id','period_start','period_end'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                foreach(['gross_cents','net_cents'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=(int)$b[$k]; }}
                if(!$fields) return $this->json(['error'=>'No fields'],400); $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE pay_runs SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $dbh->prepare('DELETE FROM pay_runs WHERE tenant_id=? AND id=?')->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* ---------------- Shifts ---------------- */
    private function shifts(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM shifts WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM shifts WHERE tenant_id=? ORDER BY date DESC, id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $date=$b['date']??''; if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)) return $this->json(['error'=>'Invalid date'],422);
                $start=mb_substr(trim($b['start_time']??''),0,5); $end=mb_substr(trim($b['end_time']??''),0,5); $break=(int)($b['break_minutes']??0);
                $eid = isset($b['employer_id']) && $b['employer_id']!=='' ? (int)$b['employer_id'] : null;
                $role=mb_substr(trim($b['role']??''),0,64); $rate=(float)($b['rate']??0); $tips=(float)($b['tips']??0); $loc=mb_substr(trim($b['location']??''),0,120); $notes=mb_substr(trim($b['notes']??''),0,255);
                $st=$dbh->prepare('INSERT INTO shifts (tenant_id,date,start_time,end_time,break_minutes,employer_id,role,rate,tips,location,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
                $st->execute([$tenantId,$date,$start,$end,$break,$eid,$role,$rate,$tips,$loc,$notes]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['date','start_time','end_time','break_minutes','employer_id','role','rate','tips','location','notes'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(!$fields) return $this->json(['error'=>'No fields'],400); $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE shifts SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $dbh->prepare('DELETE FROM shifts WHERE tenant_id=? AND id=?')->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* ---------------- Debts ---------------- */
    private function debts(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM debts WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM debts WHERE tenant_id=? ORDER BY id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $lender=mb_substr(trim($b['lender']??''),0,120); $type=mb_substr(trim($b['type']??'other'),0,40); $balance=(float)($b['balance']??0); $limit=(float)($b['limit_amount']??0); $apr=(float)($b['apr']??0); $min=(float)($b['min_payment']??0); $due=(int)($b['due_day']??1);
                if($lender==='') return $this->json(['error'=>'Lender required'],422);
                $st=$dbh->prepare('INSERT INTO debts (tenant_id,lender,type,balance,`limit`,apr,min_payment,due_day) VALUES (?,?,?,?,?,?,?,?)');
                $st->execute([$tenantId,$lender,$type,$balance,$limit,$apr,$min,$due]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['lender','type','balance','limit','apr','min_payment','due_day'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(!$fields) return $this->json(['error'=>'No fields'],400); $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE debts SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $dbh->prepare('DELETE FROM debts WHERE tenant_id=? AND id=?')->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* ---------------- Investment Accounts ---------------- */
    private function investmentAccounts(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM investment_accounts WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM investment_accounts WHERE tenant_id=? ORDER BY id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $name=mb_substr(trim($b['name']??''),0,120); $type=mb_substr(trim($b['type']??'brokerage'),0,40); $value=(float)($b['value']??0);
                if($name==='') return $this->json(['error'=>'Name required'],422);
                $st=$dbh->prepare('INSERT INTO investment_accounts (tenant_id,name,type,value) VALUES (?,?,?,?)');
                $st->execute([$tenantId,$name,$type,$value]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['name','type'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }} if(isset($b['value'])){ $fields[]='value=?'; $vals[]=(float)$b['value']; }
                if(!$fields) return $this->json(['error'=>'No fields'],400); $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE investment_accounts SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $dbh->prepare('DELETE FROM investment_accounts WHERE tenant_id=? AND id=?')->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    /* ---------------- Investments ---------------- */
    private function investments(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) { $st=$dbh->prepare('SELECT * FROM investments WHERE tenant_id=? AND id=?'); $st->execute([$tenantId,(int)$id]); $row=$st->fetch(); return $this->json($row?:['error'=>'Not found'],$row?200:404);}                
                $st=$dbh->prepare('SELECT * FROM investments WHERE tenant_id=? ORDER BY id DESC'); $st->execute([$tenantId]); return $this->json($st->fetchAll());
            case 'POST':
                $b=$this->bodyJson(); $aid=(int)($b['account_id']??0); $name=mb_substr(trim($b['name']??''),0,120); $symbol=mb_substr(trim($b['symbol']??''),0,20); $qty=(float)($b['quantity']??0); $price=(float)($b['price']??0);
                if($aid<=0 || $name==='') return $this->json(['error'=>'Invalid payload'],422);
                $st=$dbh->prepare('INSERT INTO investments (tenant_id,account_id,name,symbol,quantity,price) VALUES (?,?,?,?,?,?)');
                $st->execute([$tenantId,$aid,$name,$symbol,$qty,$price]); return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if(!$id) return $this->json(['error'=>'ID required'],400); $b=$this->bodyJson(); $fields=[]; $vals=[];
                foreach(['account_id','name','symbol','quantity','price'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=$b[$k]; }}
                if(!$fields) return $this->json(['error'=>'No fields'],400); $vals[]=$tenantId; $vals[]=(int)$id; $sql='UPDATE investments SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?'; $dbh->prepare($sql)->execute($vals); return $this->json(['ok'=>true]);
            case 'DELETE':
                if(!$id) return $this->json(['error'=>'ID required'],400); $dbh->prepare('DELETE FROM investments WHERE tenant_id=? AND id=?')->execute([$tenantId,(int)$id]); return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    private function accounts(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $st=$dbh->prepare('SELECT * FROM accounts WHERE tenant_id=? AND id=?');
                    $st->execute([$tenantId,(int)$id]);
                    $row=$st->fetch();
                    return $this->json($row ?: ['error'=>'Not found'], $row?200:404);
                } else {
                    $st=$dbh->prepare('SELECT * FROM accounts WHERE tenant_id=? ORDER BY name');
                    $st->execute([$tenantId]);
                    return $this->json($st->fetchAll());
                }
            case 'POST':
                $b=$this->bodyJson();
                $name=mb_substr(trim($b['name'] ?? ''),0,120);
                $type=mb_substr(trim($b['type'] ?? 'cash'),0,40);
                $currency=strtoupper(mb_substr(trim($b['currency'] ?? 'CAD'),0,8));
                $opening=(int)($b['opening_balance_cents'] ?? 0);
                if ($name==='') return $this->json(['error'=>'Name required'],422);
                $st=$dbh->prepare('INSERT INTO accounts (tenant_id,name,type,currency,opening_balance_cents,active) VALUES (?,?,?,?,?,1)');
                $st->execute([$tenantId,$name,$type,$currency,$opening]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson();
                $id=(int)$id;
                $fields=[]; $vals=[];
                foreach(['name','type','currency'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[] = ($k==='currency'? strtoupper($b[$k]) : $b[$k]); }}
                if(isset($b['opening_balance_cents'])){ $fields[]='opening_balance_cents=?'; $vals[]=(int)$b['opening_balance_cents']; }
                if(isset($b['active'])){ $fields[]='active=?'; $vals[]=(int)$b['active']; }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE accounts SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?';
                $vals[]=$tenantId; $vals[]=$id;
                $st=$dbh->prepare($sql); $st->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $st=$dbh->prepare('DELETE FROM accounts WHERE tenant_id=? AND id=?');
                $st->execute([$tenantId,(int)$id]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    private function savingsGoals(PDO $dbh, int $tenantId, string $method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $st=$dbh->prepare('SELECT * FROM savings_goals WHERE tenant_id=? AND id=?');
                    $st->execute([$tenantId,(int)$id]);
                    $row=$st->fetch();
                    return $this->json($row ?: ['error'=>'Not found'], $row?200:404);
                } else {
                    $st=$dbh->prepare('SELECT * FROM savings_goals WHERE tenant_id=? ORDER BY id DESC');
                    $st->execute([$tenantId]);
                    return $this->json($st->fetchAll());
                }
            case 'POST':
                $b=$this->bodyJson();
                $name=mb_substr(trim($b['name'] ?? ''),0,120);
                $target=(int)($b['target_cents'] ?? 0);
                $saved=(int)($b['saved_cents'] ?? 0);
                $deadline = isset($b['deadline']) && preg_match('/^\d{4}-\d{2}-\d{2}$/',$b['deadline']) ? $b['deadline'] : null;
                $currency=strtoupper(mb_substr(trim($b['currency'] ?? 'CAD'),0,8));
                if ($name==='' || $target<=0) return $this->json(['error'=>'Name and positive target required'],422);
                $st=$dbh->prepare('INSERT INTO savings_goals (tenant_id,name,target_cents,saved_cents,deadline,currency) VALUES (?,?,?,?,?,?)');
                $st->execute([$tenantId,$name,$target,$saved,$deadline,$currency]);
                return $this->json(['id'=>(int)$dbh->lastInsertId()],201);
            case 'PUT':
            case 'PATCH':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $b=$this->bodyJson(); $id=(int)$id;
                $fields=[]; $vals=[];
                foreach(['name','currency'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[] = ($k==='currency'? strtoupper($b[$k]) : $b[$k]); }}
                foreach(['target_cents','saved_cents'] as $k){ if(isset($b[$k])){ $fields[]="$k=?"; $vals[]=(int)$b[$k]; }}
                if(isset($b['deadline'])){ $fields[]='deadline=?'; $vals[]=(preg_match('/^\d{4}-\d{2}-\d{2}$/',(string)$b['deadline'])?$b['deadline']:null); }
                if(!$fields) return $this->json(['error'=>'No fields'],400);
                $sql='UPDATE savings_goals SET '.implode(',', $fields).' WHERE tenant_id=? AND id=?';
                $vals[]=$tenantId; $vals[]=$id;
                $st=$dbh->prepare($sql); $st->execute($vals);
                return $this->json(['ok'=>true]);
            case 'DELETE':
                if (!$id) return $this->json(['error'=>'ID required'],400);
                $st=$dbh->prepare('DELETE FROM savings_goals WHERE tenant_id=? AND id=?');
                $st->execute([$tenantId,(int)$id]);
                return $this->json(['ok'=>true]);
        }
        return $this->json(['error'=>'Method not allowed'],405);
    }

    private function fetchRates(string $base): array {
        // Simple passthrough with basic caching via PHP session (lightweight)
        $key = 'rates_'.$base;
        if (isset($_SESSION[$key]) && isset($_SESSION[$key]['t']) && (time()-$_SESSION[$key]['t']<3600)) {
            return $_SESSION[$key]['d'];
        }
        $url = 'https://api.exchangerate-api.com/v4/latest/'.urlencode($base);
        $json = @file_get_contents($url);
        if ($json===false) return [];
        $data = json_decode($json, true);
        $rates = is_array($data) ? ($data['rates'] ?? []) : [];
        $_SESSION[$key] = ['t'=>time(),'d'=>$rates];
        return $rates;
    }

    private function hasColumn(PDO $dbh, string $table, string $column): bool {
        try {
            $st = $dbh->query("SHOW COLUMNS FROM `{$table}` LIKE " . $dbh->quote($column));
            return (bool)$st->fetch();
        } catch (Throwable $e) { return false; }
    }

}