        // ---------------- Income UI -----------------
        function getIncomeForm(id){
            let r = null; if(id) r = financeData.income.find(x=>x.id===String(id));
            const accOpts = (financeData.accounts||[]).map(a=>`<option value="${a.id}" ${r&&r.account_id===a.id?'selected':''}>${a.name}</option>`).join('');
            return `
                <div class="form-group">
                    <label for="income-date">Date</label>
                    <input id="income-date" type="date" class="form-control" value="${r?r.date:new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="form-group">
                    <label for="income-source">Source</label>
                    <input id="income-source" class="form-control" value="${r?r.source:''}" required>
                </div>
                <div class="form-group">
                    <label for="income-amount">Amount</label>
                    <input id="income-amount" type="number" step="0.01" min="0" class="form-control" value="${r?r.amount:0}" required>
                </div>
                <div class="form-group">
                    <label for="income-account">Account</label>
                    <select id="income-account" class="form-control"><option value="">—</option>${accOpts}</select>
                </div>
                <div class="form-group">
                    <label for="income-notes">Notes</label>
                    <input id="income-notes" class="form-control" value="${r?r.notes:''}">
                </div>
            `;
        }

        async function saveIncome(id){
            const payload = {
                date: document.getElementById('income-date').value,
                source: document.getElementById('income-source').value.trim(),
                amount_cents: toCents(document.getElementById('income-amount').value),
                account_id: document.getElementById('income-account').value,
                notes: document.getElementById('income-notes').value.trim(),
                currency: financeData.currency
            };
            if(id) await apiSend('PUT', `/finance/api/income/${encodeURIComponent(id)}`, payload);
            else await apiSend('POST', '/finance/api/income', payload);
            await loadData(); updateUI();
        }

        async function deleteIncome(id){
            await apiSend('DELETE', `/finance/api/income/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        function updateIncomeList(){
            const tbody = document.getElementById('income-list'); if(!tbody) return;
            tbody.innerHTML='';
            if(!financeData.income || financeData.income.length===0){
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fas fa-coins"></i> <p>No income</p></td></tr>`; return;
            }
            financeData.income.forEach(r=>{
                const acc = (financeData.accounts||[]).find(a=>a.id===r.account_id);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${r.date}</td>
                    <td>${r.source}</td>
                    <td>${formatCurrency(r.amount)}</td>
                    <td>${acc?acc.name:'—'}</td>
                    <td>${r.notes||''}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('income','${r.id}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="deleteIncome('${r.id}')"><i class="fas fa-trash"></i></button>
                    </td>`;
                tbody.appendChild(tr);
            });
        }

        // ---------------- Transfers UI -----------------
        function getTransferForm(id){
            let t = null; if(id) t = financeData.transfers.find(x=>x.id===String(id));
            const accOptsFrom = (financeData.accounts||[]).map(a=>`<option value="${a.id}" ${t&&t.from_account_id===a.id?'selected':''}>${a.name}</option>`).join('');
            const accOptsTo = (financeData.accounts||[]).map(a=>`<option value="${a.id}" ${t&&t.to_account_id===a.id?'selected':''}>${a.name}</option>`).join('');
            return `
                <div class="form-group">
                    <label for="transfer-date">Date</label>
                    <input id="transfer-date" type="date" class="form-control" value="${t?t.date:new Date().toISOString().split('T')[0]}" required>
                </div>
                <div class="form-group">
                    <label for="transfer-from">From</label>
                    <select id="transfer-from" class="form-control">${accOptsFrom}</select>
                </div>
                <div class="form-group">
                    <label for="transfer-to">To</label>
                    <select id="transfer-to" class="form-control">${accOptsTo}</select>
                </div>
                <div class="form-group">
                    <label for="transfer-amount">Amount</label>
                    <input id="transfer-amount" type="number" step="0.01" min="0" class="form-control" value="${t?t.amount:0}" required>
                </div>
                <div class="form-group">
                    <label for="transfer-notes">Notes</label>
                    <input id="transfer-notes" class="form-control" value="${t?t.notes:''}">
                </div>
            `;
        }

        async function saveTransfer(id){
            const payload = {
                date: document.getElementById('transfer-date').value,
                from_account_id: document.getElementById('transfer-from').value,
                to_account_id: document.getElementById('transfer-to').value,
                amount_cents: toCents(document.getElementById('transfer-amount').value),
                notes: document.getElementById('transfer-notes').value.trim(),
                currency: financeData.currency
            };
            if(id) await apiSend('PUT', `/finance/api/transfers/${encodeURIComponent(id)}`, payload);
            else await apiSend('POST', '/finance/api/transfers', payload);
            await loadData(); updateUI();
        }

        async function deleteTransfer(id){
            await apiSend('DELETE', `/finance/api/transfers/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        function updateTransfersList(){
            const tbody = document.getElementById('transfers-list'); if(!tbody) return;
            tbody.innerHTML='';
            if(!financeData.transfers || financeData.transfers.length===0){
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fas fa-right-left"></i> <p>No transfers</p></td></tr>`; return;
            }
            financeData.transfers.forEach(t=>{
                const from = (financeData.accounts||[]).find(a=>a.id===t.from_account_id);
                const to = (financeData.accounts||[]).find(a=>a.id===t.to_account_id);
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${t.date}</td>
                    <td>${from?from.name:'—'}</td>
                    <td>${to?to.name:'—'}</td>
                    <td>${formatCurrency(t.amount)}</td>
                    <td>${t.notes||''}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('transfer','${t.id}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="deleteTransfer('${t.id}')"><i class="fas fa-trash"></i></button>
                    </td>`;
                tbody.appendChild(tr);
            });
        }

<?php require 'app/views/templates/header.php'; ?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Hub - Modern Financial Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
    --primary: #2c6b5f;
    --primary-dark: #1f4b43;
    --primary-light: #e6f0ee;
    --secondary: #5c6b7a;
    --accent: #d97706;
    --background: #f9fafb;
    --card: #ffffff;
    --text: #111827;
    --text-light: #6b7280;
    --border: #d1d5db;
    --success: #059669;
    --warning: #d97706;
    --danger: #dc2626;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.05);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
    --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --radius-sm: 0.5rem;
    --radius-md: 0.75rem;
    --radius-lg: 1rem;
    --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, sans-serif;
}

[data-theme="dark"] {
    --primary: #4ca89b;
    --primary-dark: #3b867b;
    --primary-light: #1a3c34;
    --secondary: #9ca3af;
    --accent: #f59e0b;
    --background: #111827;
    --card: #1f2a44;
    --text: #f3f4f6;
    --text-light: #d1d5db;
    --border: #374151;
    --success: #10b981;
    --warning: #f59e0b;
    --danger: #ef4444;
    --shadow-sm: 0 2px 4px rgba(0, 0, 0, 0.3);
    --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.3);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}

body {
    font-family: var(--font-sans);
    background: linear-gradient(to bottom, var(--background), var(--background));
    color: var(--text);
    line-height: 1.5;
    min-height: 100vh;
    padding: 1.5rem;
    font-size: 0.9375rem;
    font-weight: 400;
    display: flex;
    flex-direction: column;
    overscroll-behavior: none;
    -webkit-tap-highlight-color: transparent;
    transition: background-color 0.3s ease, color 0.3s ease;
    letter-spacing: -0.01em;
}

.container {
    max-width: 1440px;
    margin: 0 auto;
    width: 100%;
    flex: 1;
    padding: 0 1rem;
}

.header {
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1.5rem;
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    border: none;
    background: linear-gradient(145deg, var(--card), var(--background));
}

@media (min-width: 768px) {
    .header {
        flex-direction: row;
        justify-content: space-between;
    }
}

.logo {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.logo-icon {
    width: 2.75rem;
    height: 2.75rem;
    background: var(--primary);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 700;
    font-size: 1.5rem;
    box-shadow: 0 2px 8px rgba(44, 107, 95, 0.2);
}

.logo-text {
    font-size: 2rem;
    font-weight: 900;
    color: var(--text);
    letter-spacing: -0.02em;
}

.controls {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    justify-content: center;
    align-items: center;
}

.theme-toggle {
    background: transparent;
    border: 1px solid var(--border);
    width: 3rem;
    height: 3rem;
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: var(--text);
    transition: var(--transition);
}

.theme-toggle:hover {
    background: var(--primary-light);
    transform: scale(1.05);
}

.currency-selector-wrapper {
    position: relative;
    display: inline-block;
}

.currency-selector {
    padding: 0.875rem 2.75rem 0.875rem 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    background-color: var(--card);
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    color: var(--text);
    font-size: 0.875rem;
    line-height: 1.25;
    min-height: 3rem;
    min-width: 3rem;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%20256%20512%22%3E%3Cpath%20fill%3D%22%235c6b7a%22%20d%3D%22M119.5%20326.9L40.9%20248.3c-9.4-9.4-9.4-24.6%200-33.9l17.7-17.7c9.4-9.4%2024.6-9.4%2033.9%200L128%20285.3l35.5-35.5c9.4-9.4%2024.6-9.4%2033.9%200l17.7%2017.7c9.4%209.4%209.4%2024.6%200%2033.9l-78.6%2078.6c-9.4%209.4-24.6%209.4-33.9%200z%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right 0.75rem center;
    background-size: 0.8rem;
}

.currency-selector:hover, .currency-selector:focus-visible {
    background-color: var(--primary-light);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 107, 95, 0.15);
    outline: none;
}

.btn {
    padding: 0.875rem 1.25rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    background-color: var(--card);
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: var(--transition);
    box-shadow: var(--shadow-sm);
    color: var(--text);
    font-size: 0.9375rem;
    line-height: 1.25;
    min-height: 3rem;
    min-width: 3rem;
    letter-spacing: -0.01em;
}

.btn:hover, .btn:focus-visible {
    background-color: var(--primary-light);
    border-color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 107, 95, 0.15);
    outline: none;
}

.btn-primary {
    background: var(--primary);
    color: white;
    border: none;
    box-shadow: 0 2px 8px rgba(44, 107, 95, 0.2);
}

.btn-primary:hover, .btn-primary:focus-visible {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(44, 107, 95, 0.3);
}

.card {
    background: var(--card);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: var(--shadow-md);
    margin-bottom: 1.5rem;
    border: none;
    transition: background-color 0.3s ease;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
}

.card-title {
    font-size: 1.375rem;
    font-weight: 800;
    color: var(--text);
    letter-spacing: -0.02em;
}

.summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.5rem;
}

.summary-item {
    padding: 1.25rem;
    background: linear-gradient(145deg, var(--card), var(--background));
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-md);
    text-align: center;
    border: none;
    transition: background-color 0.3s ease;
}

.summary-label {
    font-size: 0.875rem;
    color: var(--text-light);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.summary-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--text);
}

.tabs {
    display: flex;
    gap: 0.25rem;
    overflow-x: auto;
    padding: 0.5rem;
    background: transparent;
    border: 1px solid var(--border);
    border-radius: var(--radius-md);
    margin-bottom: 1.5rem;
}

.tabs::-webkit-scrollbar {
    display: none;
}

.tab {
    white-space: nowrap;
    padding: 0.75rem 1rem;
    border-radius: var(--radius-sm);
    font-weight: 500;
    cursor: pointer;
    color: var(--text-light);
    transition: var(--transition);
}

.tab.active, .tab:hover {
    background: var(--primary);
    color: white;
    box-shadow: 0 2px 8px rgba(44, 107, 95, 0.2);
}

.section {
    display: none;
}

.section.active {
    display: block;
}

.expense-table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.expense-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-top: 0.75rem;
}

.expense-table th {
    text-align: left;
    padding: 1rem;
    font-weight: 500;
    color: var(--text-light);
    font-size: 0.875rem;
    border-bottom: 2px solid var(--border);
    background-color: var(--card);
}

.expense-table td {
    padding: 1rem;
    background-color: var(--card);
    border-bottom: 1px solid var(--border);
    font-size: 0.875rem;
}

.action-btn {
    background: none;
    border: none;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}

.action-btn:hover, .action-btn:focus-visible {
    color: var(--primary);
    background: var(--primary-light);
    outline: none;
}

.empty-state {
    text-align: center;
    padding: 2rem;
    color: var(--text-light);
}

.empty-state i {
    font-size: 2rem;
    margin-bottom: 0.5rem;
    color: var(--border);
}

.empty-state p {
    font-size: 0.875rem;
    font-weight: 500;
}

.expense-form {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.form-group label {
    font-size: 0.875rem;
    color: var(--text);
    font-weight: 600;
}

.form-control {
    padding: 0.875rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border);
    background-color: var(--background);
    font-family: inherit;
    font-size: 0.875rem;
    color: var(--text);
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(44, 107, 95, 0.1);
}

.modal {
    position: fixed;
    inset: 0;
    background-color: rgba(0, 0, 0, 0.5);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 0.75rem;
}

.modal.active {
    display: flex;
}

.modal-content {
    background: var(--card);
    padding: 2rem;
    border-radius: var(--radius-lg);
    width: 100%;
    max-width: 36rem;
    max-height: calc(100vh - 1.5rem);
    overflow-y: auto;
    box-shadow: var(--shadow-md);
    border: none;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--text);
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.25rem;
    cursor: pointer;
    color: var(--text-light);
    padding: 0.25rem;
    border-radius: var(--radius-sm);
    transition: var(--transition);
}

.close-modal:hover, .close-modal:focus-visible {
    color: var(--primary);
    background-color: var(--primary-light);
    outline: none;
}

.error-message {
    color: var(--danger);
    font-size: 0.875rem;
    margin-top: 0.5rem;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.stat-card {
    background: var(--card);
    border-radius: var(--radius-md);
    padding: 1rem;
    box-shadow: var(--shadow-md);
    border: none;
    transition: background-color 0.3s ease;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-light);
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--text);
}

.debt-card {
    background: var(--card);
    border-radius: var(--radius-lg);
    padding: 1.75rem;
    box-shadow: var(--shadow-md);
    margin-bottom: 1.5rem;
    border: none;
    position: relative;
    transition: background-color 0.3s ease;
}

.debt-card-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 1rem;
}

.debt-lender {
    font-size: 1.5rem;
    font-weight: 800;
    color: var(--text);
}

.debt-type-badge {
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
    background: var(--primary-light);
    color: var(--primary);
}

.debt-apr {
    font-size: 1.1rem;
    font-weight: 700;
}

.apr-high {
    color: var(--danger);
}

.apr-medium {
    color: var(--warning);
}

.apr-low {
    color: var(--success);
}

.debt-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1rem;
}

.debt-detail-item {
    display: flex;
    flex-direction: column;
}

.debt-detail-label {
    font-size: 0.875rem;
    color: var(--text-light);
    margin-bottom: 0.25rem;
}

.debt-detail-value {
    font-size: 1rem;
    font-weight: 600;
}

.progress-container {
    margin: 1rem 0;
}

.progress-bar {
    height: 0.5rem;
    background: var(--primary-light);
    border-radius: var(--radius-md);
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: var(--primary);
    border-radius: var(--radius-md);
    transition: width 0.5s ease;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    font-size: 0.875rem;
    color: var(--text-light);
}

.utilization-warning {
    color: var(--danger);
    font-weight: 600;
}

.payment-history {
    margin-top: 1rem;
    border-top: 1px solid var(--border);
    padding-top: 1rem;
}

.payment-history-title {
    font-size: 1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
    color: var(--text);
}

.payment-item {
    display: flex;
    justify-content: space-between;
    padding: 0.5rem 0;
    border-bottom: 1px solid var(--border);
}

.payment-item:last-child {
    border-bottom: none;
}

.payment-date {
    font-size: 0.875rem;
    color: var(--text-light);
}

.payment-amount {
    font-weight: 600;
}

.payment-principal {
    font-size: 0.75rem;
    color: var(--success);
}

.debt-actions {
    display: flex;
    gap: 0.5rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

.recommendation {
    background: var(--primary-light);
    padding: 0.75rem;
    border-radius: var(--radius-sm);
    margin-top: 1rem;
    font-size: 0.875rem;
}

.recommendation-warning {
    background: rgba(220, 38, 38, 0.1);
    color: var(--danger);
}

.recommendation-tip {
    background: rgba(16, 185, 129, 0.1);
    color: var(--success);
}

.custom-type-form {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

.custom-type-container {
    display: none;
    margin-top: 0.5rem;
}

.custom-type-container.visible {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.5rem;
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@media (min-width: 640px) {
    .expense-form {
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    .btn {
        padding: 0.875rem 1.5rem;
        font-size: 0.9375rem;
    }
    .btn span {
        display: inline;
    }
    .card {
        padding: 2rem;
    }
    .card-title {
        font-size: 1.5rem;
    }
    .modal-content {
        padding: 2rem;
    }
    .modal-title {
        font-size: 1.5rem;
    }
}

@media (min-width: 768px) {
    .summary-grid {
        grid-template-columns: repeat(5, 1fr);
    }
}

@media (max-width: 640px) {
    .logo-text {
        font-size: 1.75rem;
    }
    .logo-icon {
        width: 2.25rem;
        height: 2.25rem;
        font-size: 1.25rem;
    }
    .btn {
        padding: 0.75rem;
        font-size: 0.875rem;
        min-height: 2.5rem;
    }
    .btn span {
        display: none;
    }
    .card {
        padding: 1.25rem;
        margin-bottom: 1rem;
    }
    .card-title {
        font-size: 1.25rem;
    }
    .summary-item {
        padding: 0.75rem;
    }
    .summary-label {
        font-size: 0.8125rem;
    }
    .summary-value {
        font-size: 1.5rem;
    }
    .tab {
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
    }
    .form-control {
        padding: 0.625rem;
        font-size: 0.875rem;
    }
    .expense-table th,
    .expense-table td {
        font-size: 0.8125rem;
    }
    .modal-content {
        padding: 1.5rem;
        max-width: 95%;
    }
    .modal-title {
        font-size: 1.125rem;
    }
    .close-modal {
        font-size: 1.25rem;
    }
    .debt-details {
        grid-template-columns: 1fr;
    }
    .debt-actions {
        flex-direction: column;
    }
    .debt-actions .btn {
        width: 100%;
        justify-content: center;
    }
}
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
            </div>

        <!-- Income Section -->
        <div class="section" id="income">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Income</div>
                    <button class="btn btn-primary" onclick="openModal('income')">
                        <i class="fas fa-plus"></i>
                        <span>Add Income</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Source</th>
                                <th>Amount</th>
                                <th>Account</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="income-list"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Transfers Section -->
        <div class="section" id="transfers">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Transfers</div>
                    <button class="btn btn-primary" onclick="openModal('transfer')">
                        <i class="fas fa-plus"></i>
                        <span>Add Transfer</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Amount</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transfers-list"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Budgets Section -->
        <div class="section" id="budgets">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Budgets</div>
                    <button class="btn btn-primary" onclick="openModal('budget')">
                        <i class="fas fa-plus"></i>
                        <span>Add Budget</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Period</th>
                                <th>Date Range</th>
                                <th>Currency</th>
                                <th>Lines</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="budgets-list"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Accounts Section -->
        <div class="section" id="accounts">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Accounts</div>
                    <button class="btn btn-primary" onclick="openModal('account')">
                        <i class="fas fa-plus"></i>
                        <span>Add Account</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Currency</th>
                                <th>Opening Balance</th>
                                <th>Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="accounts-list">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
            <div class="controls">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="currency-selector-wrapper">
                    <select id="base-currency-selector" class="currency-selector">
                        <!-- Currencies will be dynamically loaded here -->
                    </select>
                </div>
            </div>
        </header>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Income YTD</div>
                <div class="summary-value" id="income-ytd">$0.00</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Debt</div>
                <div class="summary-value" id="total-debt">$0.00</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Investments</div>
                <div class="summary-value" id="investments-value">$0.00</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Savings Progress</div>
                <div class="summary-value" id="savings-progress">0%</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Month Shifts</div>
                <div class="summary-value" id="month-shifts">0 hrs</div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="payroll">Payroll</div>
            <div class="tab" data-tab="shifts">Shifts</div>
            <div class="tab" data-tab="debts">Debts</div>
            <div class="tab" data-tab="investments">Investments</div>
            <div class="tab" data-tab="budgets">Budgets</div>
            <div class="tab" data-tab="income">Income</div>
            <div class="tab" data-tab="transfers">Transfers</div>
            <div class="tab" data-tab="accounts">Accounts</div>
            <div class="tab" data-tab="savings">Savings</div>
        </div>

        <!-- Payroll Section -->
        <div class="section active" id="payroll">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Employers</div>
                    <button class="btn btn-primary" onclick="openModal('employer')">
                        <i class="fas fa-plus"></i>
                        <span>Add Employer</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Pay Schedule</th>
                                <th>Base Rate</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="employers-list">
                            <!-- Employers will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Pay Runs</div>
                    <button class="btn btn-primary" onclick="openModal('payrun')">
                        <i class="fas fa-plus"></i>
                        <span>Add Pay Run</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Period</th>
                                <th>Employer</th>
                                <th>Gross Pay</th>
                                <th>Net Pay</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="payruns-list">
                            <!-- Pay runs will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Shifts Section -->
        <div class="section" id="shifts">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Shifts</div>
                    <button class="btn btn-primary" onclick="openModal('shift')">
                        <i class="fas fa-plus"></i>
                        <span>Add Shift</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Hours</th>
                                <th>Employer</th>
                                <th>Earnings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="shifts-list">
                            <!-- Shifts will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Debts Section -->
        <div class="section" id="debts">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Debt</div>
                    <div class="stat-value" id="debt-total">$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Min Payments</div>
                    <div class="stat-value" id="debt-min-payments">$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value" id="debt-this-month">$0.00</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Debts</div>
                    <button class="btn btn-primary" onclick="openModal('debt')">
                        <i class="fas fa-plus"></i>
                        <span>Add Debt</span>
                    </button>
                </div>
                <div id="debts-container">
                    <!-- Debt cards will be dynamically inserted here -->
                </div>

                <!-- Empty state -->
                <div id="debts-empty-state" class="empty-state" style="display: none;">
                    <i class="fas fa-credit-card"></i>
                    <p>No debts added yet</p>
                </div>
            </div>
        </div>

        <!-- Investments Section -->
        <div class="section" id="investments">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Portfolio Value</div>
                    <div class="stat-value" id="portfolio-value">$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Total Gain/Loss</div>
                    <div class="stat-value" id="investment-gain">+$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Holdings</div>
                    <div class="stat-value" id="holdings-count">0</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Investment Accounts</div>
                    <button class="btn btn-primary" onclick="openModal('investment-account')">
                        <i class="fas fa-plus"></i>
                        <span>Add Account</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Account Name</th>
                                <th>Type</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="investment-accounts-list">
                            <!-- Investment accounts will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Investments</div>
                    <button class="btn btn-primary" onclick="openModal('investment')">
                        <i class="fas fa-plus"></i>
                        <span>Add Investment</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Symbol</th>
                                <th>Quantity</th>
                                <th>Value</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="investments-list">
                            <!-- Investments will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Savings Section -->
        <div class="section" id="savings">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Saved</div>
                    <div class="stat-value" id="total-saved">$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">This Month</div>
                    <div class="stat-value" id="savings-this-month">$0.00</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed Goals</div>
                    <div class="stat-value" id="completed-goals">0</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">Savings Goals</div>
                    <button class="btn btn-primary" onclick="openModal('savings')">
                        <i class="fas fa-plus"></i>
                        <span>Add Goal</span>
                    </button>
                </div>
                <div class="expense-table-container">
                    <table class="expense-table">
                        <thead>
                            <tr>
                                <th>Goal</th>
                                <th>Target</th>
                                <th>Progress</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="savings-list">
                            <!-- Savings goals will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for adding/editing items -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="modal-title">Add Item</h2>
                <button class="close-modal" onclick="closeModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="modal-form">
                <div class="expense-form" id="modal-form-content">
                    <!-- Form content will be dynamically inserted here -->
                </div>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal" id="payment-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="payment-modal-title">Record Payment</h2>
                <button class="close-modal" onclick="closePaymentModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="payment-form">
                <div class="expense-form" id="payment-form-content">
                    <!-- Payment form content will be dynamically inserted here -->
                </div>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <button type="button" class="btn" onclick="closePaymentModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Global data storage
        const financeData = {
            employers: [],
            payRuns: [],
            shifts: [],
            debts: [],
            investmentAccounts: [],
            investments: [],
            savingsGoals: [],
            accounts: [],
            currency: 'USD',
            theme: 'light'
        };

        // CSRF token provided by server for API calls
        const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';

        // API helpers
        async function apiGet(path) {
            const res = await fetch(path, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('API error: ' + res.status);
            return res.json();
        }
        async function apiSend(method, path, body) {
            const res = await fetch(path, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                credentials: 'same-origin',
                body: body ? JSON.stringify(body) : null
            });
            if (!res.ok) {
                const t = await res.text();
                throw new Error('API ' + method + ' ' + path + ' failed: ' + res.status + ' ' + t);
            }
            return res.status === 204 ? null : res.json();
        }
        const toCents = (n)=> Math.round((Number(n)||0)*100);
        const fromCents = (c)=> (Number(c)||0)/100;

        // DOM Elements
        const themeToggle = document.getElementById('theme-toggle');
        const baseCurrencySelector = document.getElementById('base-currency-selector');
        const modal = document.getElementById('modal');
        const paymentModal = document.getElementById('payment-modal');
        const modalTitle = document.getElementById('modal-title');
        const modalForm = document.getElementById('modal-form');
        const modalFormContent = document.getElementById('modal-form-content');
        const paymentForm = document.getElementById('payment-form');
        const paymentFormContent = document.getElementById('payment-form-content');
        const tabs = document.querySelectorAll('.tab');
        const sections = document.querySelectorAll('.section');
        let lookup = { categories: [], subcategories: [] };

        // Initialize the application
        async function initApp() {
            loadCurrencies();
            loadTheme();
            setupEventListeners();
            await loadData();
            updateUI();
        }

        // Set up event listeners
        function setupEventListeners() {
            // Theme toggle
            themeToggle.addEventListener('click', toggleTheme);

            // Currency selector
            baseCurrencySelector.addEventListener('change', (e) => {
                financeData.currency = e.target.value;
                updateUI();
                saveData();
            });

            // Tab switching
            tabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const tabName = tab.getAttribute('data-tab');
                    switchTab(tabName);
                });
            });

            // Modal form submission
            modalForm.addEventListener('submit', handleFormSubmit);

            // Payment form submission
            paymentForm.addEventListener('submit', handlePaymentSubmit);
        }

        // Load available currencies
        function loadCurrencies() {
            const currencies = [
                'USD', 'EUR', 'GBP', 'JPY', 'CAD', 'AUD', 'CHF', 'CNY', 'INR', 'BRL'
            ];

            baseCurrencySelector.innerHTML = '';
            currencies.forEach(currency => {
                const option = document.createElement('option');
                option.value = currency;
                option.textContent = currency;
                baseCurrencySelector.appendChild(option);
            });

            baseCurrencySelector.value = financeData.currency;
        }

        // Theme management
        function loadTheme() {
            const savedTheme = localStorage.getItem('financeHubTheme') || 'light';
            financeData.theme = savedTheme;
            document.documentElement.setAttribute('data-theme', savedTheme);
            updateThemeIcon();
        }

        function toggleTheme() {
            financeData.theme = financeData.theme === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', financeData.theme);
            localStorage.setItem('financeHubTheme', financeData.theme);
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const icon = themeToggle.querySelector('i');
            if (financeData.theme === 'dark') {
                icon.className = 'fas fa-sun';
            } else {
                icon.className = 'fas fa-moon';
            }
        }

        // Tab management
        function switchTab(tabName) {
            tabs.forEach(tab => {
                if (tab.getAttribute('data-tab') === tabName) {
                    tab.classList.add('active');
                } else {
                    tab.classList.remove('active');
                }
            });

            sections.forEach(section => {
                if (section.id === tabName) {
                    section.classList.add('active');
                } else {
                    section.classList.remove('active');
                }
            });
        }

        // Modal management
         // Enhanced openModal function to set up custom type handlers
        function openModal(type, id = null) {
            modalTitle.textContent = getModalTitle(type, id);
            modalFormContent.innerHTML = getFormContent(type, id);
            modal.classList.add('active');
            modal.setAttribute('data-type', type);
            if (id !== null) modal.setAttribute('data-id', id);

            // Set up employer change listener for shift form
            if (type === 'shift') {
                setTimeout(() => {
                    const employerSelect = document.getElementById('shift-employer');
                    if (employerSelect) {
                        employerSelect.addEventListener('change', updateShiftHourlyRate);
                    }
                }, 100);
            }

            // Set up custom type handlers for debt and investment account forms
            if (type === 'debt' || type === 'investment-account') {
                setTimeout(() => {
                    const typeSelect = document.getElementById(`${type === 'debt' ? 'debt' : 'account'}-type`);
                    const customContainer = document.getElementById(`${type === 'debt' ? 'debt' : 'account'}-custom-type-container`);

                    if (typeSelect && customContainer) {
                        typeSelect.addEventListener('change', function() {
                            if (this.value === 'other') {
                                customContainer.classList.add('visible');
                            } else {
                                customContainer.classList.remove('visible');
                            }
                        });
                    }
                }, 100);
            }
        }

        function closeModal() {
            modal.classList.remove('active');
            modal.removeAttribute('data-type');
            modal.removeAttribute('data-id');
        }

        function openPaymentModal(debtId) {
            paymentFormContent.innerHTML = getPaymentFormContent(debtId);
            paymentModal.classList.add('active');
            paymentModal.setAttribute('data-debt-id', debtId);
        }

        function closePaymentModal() {
            paymentModal.classList.remove('active');
            paymentModal.removeAttribute('data-debt-id');
        }

        function getModalTitle(type, id) {
            const actions = {
                'employer': id ? 'Edit Employer' : 'Add Employer',
                'payrun': id ? 'Edit Pay Run' : 'Add Pay Run',
                'shift': id ? 'Edit Shift' : 'Add Shift',
                'debt': id ? 'Edit Debt' : 'Add Debt',
                'investment-account': id ? 'Edit Investment Account' : 'Add Investment Account',
                'investment': id ? 'Edit Investment' : 'Add Investment',
                'savings': id ? 'Edit Savings Goal' : 'Add Savings Goal',
                'account': id ? 'Edit Account' : 'Add Account'
            };
            return actions[type] || 'Add Item';
        }

        function getFormContent(type, id) {
            const forms = {
                'employer': getEmployerForm(id),
                'payrun': getPayRunForm(id),
                'shift': getShiftForm(id),
                'debt': getDebtForm(id),
                'investment-account': getInvestmentAccountForm(id),
                'investment': getInvestmentForm(id),
                'savings': getSavingsForm(id),
                'account': getAccountForm(id),
                'budget': getBudgetForm(id),
                'income': getIncomeForm(id),
                'transfer': getTransferForm(id)
            };
            return forms[type] || '';
        }

        function getPaymentFormContent(debtId) {
            const debt = financeData.debts.find(d => d.id === debtId);
            if (!debt) return '';

            return `
                <div class="form-group">
                    <label for="payment-amount">Payment Amount</label>
                    <input type="number" id="payment-amount" class="form-control" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="payment-date">Payment Date</label>
                    <input type="date" id="payment-date" class="form-control" required value="${new Date().toISOString().split('T')[0]}">
                </div>
                <div class="form-group">
                    <label for="payment-notes">Notes (Optional)</label>
                    <input type="text" id="payment-notes" class="form-control">
                </div>
            `;
        }

        // Form content generators
        function getEmployerForm(id) {
            let employer = null;
            if (id) {
                employer = financeData.employers.find(e => e.id === id);
            }

            return `
                <div class="form-group">
                    <label for="employer-name">Employer Name</label>
                    <input type="text" id="employer-name" class="form-control" value="${employer ? employer.name : ''}" required>
                </div>
                <div class="form-group">
                    <label for="pay-schedule">Pay Schedule</label>
                    <select id="pay-schedule" class="form-control" required>
                        <option value="weekly" ${employer && employer.paySchedule === 'weekly' ? 'selected' : ''}>Weekly</option>
                        <option value="bi-weekly" ${employer && employer.paySchedule === 'bi-weekly' ? 'selected' : ''}>Bi-Weekly</option>
                        <option value="monthly" ${employer && employer.paySchedule === 'monthly' ? 'selected' : ''}>Monthly</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="base-rate">Base Rate ($/hr)</label>
                    <input type="number" id="base-rate" class="form-control" step="0.01" min="0" value="${employer ? employer.baseRate : ''}" required>
                </div>
            `;
        }

        function getPayRunForm(id) {
            let payrun = null;
            if (id) {
                payrun = financeData.payRuns.find(p => p.id === id);
            }

            let employersOptions = '';
            financeData.employers.forEach(employer => {
                employersOptions += `<option value="${employer.id}" ${payrun && payrun.employerId === employer.id ? 'selected' : ''}>${employer.name}</option>`;
            });

            return `
                <div class="form-group">
                    <label for="payrun-employer">Employer</label>
                    <select id="payrun-employer" class="form-control" required>
                        <option value="">Select Employer</option>
                        ${employersOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label for="payrun-period-start">Period Start</label>
                    <input type="date" id="payrun-period-start" class="form-control" value="${payrun ? payrun.periodStart : ''}" required>
                </div>
                <div class="form-group">
                    <label for="payrun-period-end">Period End</label>
                    <input type="date" id="payrun-period-end" class="form-control" value="${payrun ? payrun.periodEnd : ''}" required>
                </div>
                <div class="form-group">
                    <label for="payrun-gross">Gross Pay</label>
                    <input type="number" id="payrun-gross" class="form-control" step="0.01" min="0" value="${payrun ? payrun.grossPay : ''}" required>
                </div>
                <div class="form-group">
                    <label for="payrun-net">Net Pay</label>
                    <input type="number" id="payrun-net" class="form-control" step="0.01" min="0" value="${payrun ? payrun.netPay : ''}" required>
                </div>
            `;
        }

        function getShiftForm(id) {
            let shift = null;
            if (id) {
                shift = financeData.shifts.find(s => s.id === id);
            }

            let employersOptions = '<option value="">None</option>';
            financeData.employers.forEach(employer => {
                employersOptions += `<option value="${employer.id}" ${shift && shift.employerId === employer.id ? 'selected' : ''}>${employer.name}</option>`;
            });

            // Calculate default rate based on selected employer
            let defaultRate = '';
            if (shift && shift.employerId) {
                const employer = financeData.employers.find(e => e.id === shift.employerId);
                if (employer) defaultRate = employer.baseRate;
            }

            return `
                <div class="form-group">
                    <label for="shift-date">Date</label>
                    <input type="date" id="shift-date" class="form-control" value="${shift ? shift.date : new Date().toISOString().split('T')[0]}" required>
                </div>

                <div class="form-group">
                    <label for="shift-start-time">Start Time</label>
                    <input type="time" id="shift-start-time" class="form-control" value="${shift ? shift.startTime : '09:00'}" required>
                </div>

                <div class="form-group">
                    <label for="shift-end-time">End Time</label>
                    <input type="time" id="shift-end-time" class="form-control" value="${shift ? shift.endTime : '17:00'}" required>
                </div>

                <div class="form-group">
                    <label for="shift-break">Break (minutes)</label>
                    <input type="number" id="shift-break" class="form-control" min="0" value="${shift ? shift.breakMinutes : '30'}" required>
                </div>

                <div class="form-group">
                    <label for="shift-employer">Employer</label>
                    <select id="shift-employer" class="form-control">
                        ${employersOptions}
                    </select>
                </div>

                <div class="form-group">
                    <label for="shift-role">Role (Optional)</label>
                    <input type="text" id="shift-role" class="form-control" placeholder="e.g., Server, Cashier" value="${shift ? shift.role : ''}">
                </div>

                <div class="form-group">
                    <label for="shift-rate">Hourly Rate ($)</label>
                    <input type="number" id="shift-rate" class="form-control" step="0.01" min="0" value="${shift ? shift.rate : defaultRate}" required>
                </div>

                <div class="form-group">
                    <label for="shift-tips">Tips ($)</label>
                    <input type="number" id="shift-tips" class="form-control" step="0.01" min="0" value="${shift ? shift.tips : '0'}">
                </div>

                <div class="form-group">
                    <label for="shift-location">Location (Optional)</label>
                    <input type="text" id="shift-location" class="form-control" placeholder="e.g., Downtown, Mall" value="${shift ? shift.location : ''}">
                </div>

                <div class="form-group">
                    <label for="shift-notes">Notes (Optional)</label>
                    <textarea id="shift-notes" class="form-control" rows="3" placeholder="Any additional notes about this shift...">${shift ? shift.notes : ''}</textarea>
                </div>
            `;
        }

        function updateShiftHourlyRate() {
            const employerSelect = document.getElementById('shift-employer');
            const rateInput = document.getElementById('shift-rate');

            if (employerSelect && rateInput) {
                const employerId = employerSelect.value;
                if (employerId) {
                    const employer = financeData.employers.find(e => e.id === employerId);
                    if (employer) {
                        rateInput.value = employer.baseRate;
                    }
                }
            }
        }

                // Enhanced getDebtForm function with custom type input
        function getDebtForm(id) {
            let debt = null;
            if (id) {
                debt = financeData.debts.find(d => d.id === id);
            }

            // Check if we have a custom type
            const isCustomType = debt && !['credit-card', 'mortgage', 'student-loan', 'car-loan', 'personal-loan'].includes(debt.type);
            const customTypeValue = isCustomType ? debt.type : '';

            return `
                <div class="form-group">
                    <label for="debt-lender">Lender Name</label>
                    <input type="text" id="debt-lender" class="form-control" value="${debt ? debt.lender : ''}" required>
                </div>
                <div class="form-group">
                    <label for="debt-type">Debt Type</label>
                    <select id="debt-type" class="form-control" required>
                        <option value="credit-card" ${debt && debt.type === 'credit-card' ? 'selected' : ''}>Credit Card</option>
                        <option value="mortgage" ${debt && debt.type === 'mortgage' ? 'selected' : ''}>Mortgage</option>
                        <option value="student-loan" ${debt && debt.type === 'student-loan' ? 'selected' : ''}>Student Loan</option>
                        <option value="car-loan" ${debt && debt.type === 'car-loan' ? 'selected' : ''}>Car Loan</option>
                        <option value="personal-loan" ${debt && debt.type === 'personal-loan' ? 'selected' : ''}>Personal Loan</option>
                        <option value="other" ${isCustomType ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div class="custom-type-container ${isCustomType ? 'visible' : ''}" id="debt-custom-type-container">
                    <div class="form-group">
                        <label for="debt-custom-type">Custom Debt Type</label>
                        <input type="text" id="debt-custom-type" class="form-control" value="${customTypeValue}" placeholder="Enter custom debt type">
                    </div>
                </div>
                <div class="form-group">
                    <label for="debt-balance">Current Balance</label>
                    <input type="number" id="debt-balance" class="form-control" step="0.01" min="0" value="${debt ? debt.balance : ''}" required>
                </div>
                <div class="form-group">
                    <label for="debt-limit">Credit Limit/Loan Amount</label>
                    <input type="number" id="debt-limit" class="form-control" step="0.01" min="0" value="${debt ? debt.limit : ''}" required>
                </div>
                <div class="form-group">
                    <label for="debt-apr">APR (%)</label>
                    <input type="number" id="debt-apr" class="form-control" step="0.01" min="0" value="${debt ? debt.apr : ''}" required>
                </div>
                <div class="form-group">
                    <label for="debt-min-payment">Minimum Payment</label>
                    <input type="number" id="debt-min-payment" class="form-control" step="0.01" min="0" value="${debt ? debt.minPayment : ''}" required>
                </div>
                <div class="form-group">
                    <label for="debt-due-date">Payment Due Date (day of month)</label>
                    <input type="number" id="debt-due-date" class="form-control" min="1" max="31" value="${debt ? debt.dueDate : ''}" required>
                </div>
            `;
        }


        // Enhanced getInvestmentAccountForm function with custom type input
        function getInvestmentAccountForm(id) {
            let account = null;
            if (id) {
                account = financeData.investmentAccounts.find(a => a.id === id);
            }

            // Check if we have a custom type
            const isCustomType = account && !['brokerage', 'ira', 'roth-ira', '401k', 'hsa'].includes(account.type);
            const customTypeValue = isCustomType ? account.type : '';

            return `
                <div class="form-group">
                    <label for="account-name">Account Name</label>
                    <input type="text" id="account-name" class="form-control" value="${account ? account.name : ''}" required>
                </div>
                <div class="form-group">
                    <label for="account-type">Account Type</label>
                    <select id="account-type" class="form-control" required>
                        <option value="brokerage" ${account && account.type === 'brokerage' ? 'selected' : ''}>Brokerage</option>
                        <option value="ira" ${account && account.type === 'ira' ? 'selected' : ''}>IRA</option>
                        <option value="roth-ira" ${account && account.type === 'roth-ira' ? 'selected' : ''}>Roth IRA</option>
                        <option value="401k" ${account && account.type === '401k' ? 'selected' : ''}>401(k)</option>
                        <option value="hsa" ${account && account.type === 'hsa' ? 'selected' : ''}>HSA</option>
                        <option value="other" ${isCustomType ? 'selected' : ''}>Other</option>
                    </select>
                </div>
                <div class="custom-type-container ${isCustomType ? 'visible' : ''}" id="account-custom-type-container">
                    <div class="form-group">
                        <label for="account-custom-type">Custom Account Type</label>
                        <input type="text" id="account-custom-type" class="form-control" value="${customTypeValue}" placeholder="Enter custom account type">
                    </div>
                </div>
                <div class="form-group">
                    <label for="account-value">Current Value</label>
                    <input type="number" id="account-value" class="form-control" step="0.01" min="0" value="${account ? account.value : ''}" required>
                </div>
            `;
        }

        function getInvestmentForm(id) {
            let investment = null;
            if (id) {
                investment = financeData.investments.find(i => i.id === id);
            }

            let accountsOptions = '';
            financeData.investmentAccounts.forEach(account => {
                accountsOptions += `<option value="${account.id}" ${investment && investment.accountId === account.id ? 'selected' : ''}>${account.name}</option>`;
            });

            return `
                <div class="form-group">
                    <label for="investment-account">Investment Account</label>
                    <select id="investment-account" class="form-control" required>
                        <option value="">Select Account</option>
                        ${accountsOptions}
                    </select>
                </div>
                <div class="form-group">
                    <label for="investment-name">Investment Name</label>
                    <input type="text" id="investment-name" class="form-control" value="${investment ? investment.name : ''}" required>
                </div>
                <div class="form-group">
                    <label for="investment-symbol">Symbol (Optional)</label>
                    <input type="text" id="investment-symbol" class="form-control" value="${investment ? investment.symbol : ''}">
                </div>
                <div class="form-group">
                    <label for="investment-quantity">Quantity/Shares</label>
                    <input type="number" id="investment-quantity" class="form-control" step="0.001" min="0" value="${investment ? investment.quantity : ''}" required>
                </div>
                <div class="form-group">
                    <label for="investment-price">Price per Share/Unit</label>
                    <input type="number" id="investment-price" class="form-control" step="0.01" min="0" value="${investment ? investment.price : ''}" required>
                </div>
            `;
        }

        function getSavingsForm(id) {
            let goal = null;
            if (id) {
                goal = financeData.savingsGoals.find(g => g.id === id);
            }

            return `
                <div class="form-group">
                    <label for="savings-goal">Goal Name</label>
                    <input type="text" id="savings-goal" class="form-control" value="${goal ? goal.name : ''}" required>
                </div>
                <div class="form-group">
                    <label for="savings-target">Target Amount</label>
                    <input type="number" id="savings-target" class="form-control" step="0.01" min="0" value="${goal ? goal.target : ''}" required>
                </div>
                    <label for="savings-saved">Currently Saved</label>
                    <input type="number" id="savings-saved" class="form-control" step="0.01" min="0" value="${goal ? goal.saved : ''}" required>
                </div>
                <div class="form-group">
                    <label for="savings-deadline">Target Date (Optional)</label>
                    <input type="date" id="savings-deadline" class="form-control" value="${goal ? goal.deadline : ''}">
                </div>
            `;
        }

        // Form submission handlers
        function handleFormSubmit(e) {
            e.preventDefault();
            const type = modal.getAttribute('data-type');
            const id = modal.getAttribute('data-id');

            // Process form based on type
            switch (type) {
                case 'employer':
                    saveEmployer(id);
                    break;
                case 'payrun':
                    savePayRun(id);
                    break;
                case 'shift':
                    saveShift(id);
                    break;
                case 'debt':
                    saveDebt(id);
                    break;
                case 'investment-account':
                    saveInvestmentAccount(id);
                    break;
                case 'investment':
                    saveInvestment(id);
                    break;
                case 'savings':
                    saveSavingsGoal(id);
                    break;
                case 'account':
                    saveAccount(id);
                    break;
                case 'budget':
                    saveBudget(id);
                    break;
                case 'budget-line':
                    saveBudgetLine(id);
                    break;
                case 'income':
                    saveIncome(id);
                    break;
                case 'transfer':
                    saveTransfer(id);
                    break;
            }

            closeModal();
        }

        function handlePaymentSubmit(e) {
            e.preventDefault();
            const debtId = paymentModal.getAttribute('data-debt-id');
            const amount = parseFloat(document.getElementById('payment-amount').value);
            const date = document.getElementById('payment-date').value;
            const notes = document.getElementById('payment-notes').value;

            if (!debtId || !amount || !date) return;

            const debt = financeData.debts.find(d => d.id === debtId);
            if (!debt) return;

            // Create payment record
            const payment = {
                id: Date.now().toString(),
                debtId,
                amount,
                date,
                notes
            };

            // Update debt balance
            debt.balance -= amount;
            if (debt.balance < 0) debt.balance = 0;

            // Add to payment history
            if (!debt.paymentHistory) debt.paymentHistory = [];
            debt.paymentHistory.push(payment);

            saveData();
            updateUI();
            closePaymentModal();
        }

        // Data persistence
        function saveData() {
            // Persist only preferences locally; data is server-backed now
            try {
                localStorage.setItem('financePrefs', JSON.stringify({
                    theme: financeData.theme,
                    currency: financeData.currency
                }));
            } catch (e) {}
        }

        async function loadData() {
            // Load prefs
            try {
                const prefs = JSON.parse(localStorage.getItem('financePrefs')||'{}');
                if (prefs.theme) financeData.theme = prefs.theme;
                if (prefs.currency) financeData.currency = prefs.currency;
            } catch (e) {}

            // Hydrate from backend
            const init = await apiGet('/finance/api/init');
            // Currency default from server
            if (init.defaultCurrency) financeData.currency = init.defaultCurrency;

            // Map savings goals (cents -> number)
            financeData.savingsGoals = (init.goals||[]).map(g=>({
                id: String(g.id),
                name: g.name,
                target: fromCents(g.target_cents),
                saved: fromCents(g.saved_cents),
                deadline: g.deadline || ''
            }));

            // Accounts (cents -> number for opening)
            financeData.accounts = (init.accounts||[]).map(a=>({
                id: String(a.id),
                name: a.name,
                type: a.type,
                currency: a.currency,
                opening_balance: fromCents(a.opening_balance_cents||0),
                active: !!a.active
            }));

            // Lookup lists
            lookup.categories = init.categories||[];
            lookup.subcategories = init.subcategories||[];

            // Budgets & lines
            financeData.budgets = (init.budgets||[]).map(b=>({
                id: String(b.id),
                name: b.name,
                period_type: b.period_type,
                start_date: b.start_date || '',
                end_date: b.end_date || '',
                currency: b.currency,
                lines: (b.lines||[]).map(l=>({
                    id: String(l.id),
                    budget_id: String(l.budget_id),
                    category_id: l.category_id ? String(l.category_id) : '',
                    subcategory_id: l.subcategory_id ? String(l.subcategory_id) : '',
                    name: l.name,
                    planned: fromCents(l.planned_cents||0)
                }))
            }));

            // Income and transfers (cents -> number)
            financeData.income = (init.income||[]).map(r=>({
                id: String(r.id),
                date: r.date,
                amount: fromCents(r.amount_cents||0),
                source: r.source || '',
                notes: r.notes || '',
                currency: r.currency || financeData.currency,
                account_id: r.account_id ? String(r.account_id) : ''
            }));
            financeData.transfers = (init.transfers||[]).map(t=>({
                id: String(t.id),
                date: t.date,
                amount: fromCents(t.amount_cents||0),
                from_account_id: String(t.from_account_id),
                to_account_id: String(t.to_account_id),
                notes: t.notes || '',
                currency: t.currency || financeData.currency
            }));

            // Leave employers/payRuns/shifts/debts/investments in-memory for now
        }

        // Update UI with current data
        function updateUI() {
            updateSummaryCards();
            updateEmployersList();
            updatePayRunsList();
            updateShiftsList();
            updateDebtsList();
            updateInvestmentAccountsList();
            updateInvestmentsList();
            updateSavingsList();
            updateAccountsList();
            updateBudgetsList();
            updateIncomeList();
            updateTransfersList();
        }

        // ---------------- Budgets UI -----------------
        function getBudgetForm(id) {
            let b = null; if (id) b = financeData.budgets.find(x=>x.id===String(id));
            return `
                <div class="form-group">
                    <label for="budget-name">Name</label>
                    <input id="budget-name" class="form-control" value="${b?b.name:''}" required>
                </div>
                <div class="form-group">
                    <label for="budget-period">Period</label>
                    <select id="budget-period" class="form-control">
                        <option value="monthly" ${b&&b.period_type==='monthly'?'selected':''}>Monthly</option>
                        <option value="weekly" ${b&&b.period_type==='weekly'?'selected':''}>Weekly</option>
                        <option value="custom" ${b&&b.period_type==='custom'?'selected':''}>Custom</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="budget-start">Start Date</label>
                    <input type="date" id="budget-start" class="form-control" value="${b?b.start_date:''}">
                </div>
                <div class="form-group">
                    <label for="budget-end">End Date</label>
                    <input type="date" id="budget-end" class="form-control" value="${b?b.end_date:''}">
                </div>
                <div class="form-group">
                    <label for="budget-currency">Currency</label>
                    <input id="budget-currency" class="form-control" value="${b?b.currency:(financeData.currency||'CAD')}" maxlength="8" required>
                </div>
            `;
        }

        function getBudgetLineForm(budgetId, lineId) {
            const b = financeData.budgets.find(x=>x.id===String(budgetId));
            const l = b && lineId ? b.lines.find(x=>x.id===String(lineId)) : null;
            const catOpts = lookup.categories.map(c=>`<option value="${c.id}" ${l&&l.category_id===String(c.id)?'selected':''}>${c.name}</option>`).join('');
            const subOpts = lookup.subcategories.map(s=>`<option value="${s.id}" ${l&&l.subcategory_id===String(s.id)?'selected':''}>${s.name}</option>`).join('');
            return `
                <div class="form-group">
                    <label for="line-name">Line Name</label>
                    <input id="line-name" class="form-control" value="${l?l.name:''}" required>
                </div>
                <div class="form-group">
                    <label for="line-planned">Planned Amount</label>
                    <input id="line-planned" class="form-control" type="number" step="0.01" min="0" value="${l?l.planned:0}" required>
                </div>
                <div class="form-group">
                    <label for="line-category">Category</label>
                    <select id="line-category" class="form-control"><option value="">—</option>${catOpts}</select>
                </div>
                <div class="form-group">
                    <label for="line-subcategory">Subcategory</label>
                    <select id="line-subcategory" class="form-control"><option value="">—</option>${subOpts}</select>
                </div>
                <input type="hidden" id="line-budget-id" value="${budgetId}">
            `;
        }

        async function saveBudget(id) {
            const payload = {
                name: document.getElementById('budget-name').value.trim(),
                period_type: document.getElementById('budget-period').value,
                start_date: document.getElementById('budget-start').value || null,
                end_date: document.getElementById('budget-end').value || null,
                currency: document.getElementById('budget-currency').value.trim().toUpperCase()
            };
            if (id) await apiSend('PUT', `/finance/api/budgets/${encodeURIComponent(id)}`, payload);
            else { await apiSend('POST', '/finance/api/budgets', payload); }
            await loadData(); updateUI();
        }

        async function deleteBudget(id) {
            await apiSend('DELETE', `/finance/api/budgets/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveBudgetLine(lineId) {
            const bid = document.getElementById('line-budget-id').value;
            const payload = {
                budget_id: bid,
                name: document.getElementById('line-name').value.trim(),
                planned_cents: toCents(document.getElementById('line-planned').value),
                category_id: document.getElementById('line-category').value,
                subcategory_id: document.getElementById('line-subcategory').value
            };
            if (lineId) await apiSend('PUT', `/finance/api/budget_lines/${encodeURIComponent(lineId)}`, payload);
            else await apiSend('POST', '/finance/api/budget_lines', payload);
            await loadData(); updateUI();
        }

        async function deleteBudgetLine(lineId) {
            await apiSend('DELETE', `/finance/api/budget_lines/${encodeURIComponent(lineId)}`);
            await loadData(); updateUI();
        }

        function updateBudgetsList() {
            const tbody = document.getElementById('budgets-list'); if(!tbody) return;
            tbody.innerHTML='';
            if (!financeData.budgets || financeData.budgets.length===0) {
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fas fa-list"></i> <p>No budgets yet</p></td></tr>`;
                return;
            }
            financeData.budgets.forEach(b=>{
                const linesTable = (b.lines||[]).map(l=>`
                    <tr>
                        <td colspan="2">${l.name}</td>
                        <td>${formatCurrency(l.planned)}</td>
                        <td>
                           <button class="action-btn" onclick="openBudgetLineModal('${b.id}','${l.id}')"><i class="fas fa-edit"></i></button>
                           <button class="action-btn" onclick="deleteBudgetLine('${l.id}')"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `).join('');
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${b.name}</td>
                    <td>${b.period_type}</td>
                    <td>${(b.start_date||'')}${b.end_date?(' → '+b.end_date):''}</td>
                    <td>${b.currency}</td>
                    <td>
                        <table style="width:100%">
                            <thead><tr><th colspan="2">Line</th><th>Planned</th><th>Actions</th></tr></thead>
                            <tbody>${linesTable || '<tr><td colspan=4 class="text-muted">No lines</td></tr>'}</tbody>
                        </table>
                        <div style="margin-top:0.5rem"><button class="btn" onclick="openBudgetLineModal('${b.id}')"><i class="fas fa-plus"></i> Add Line</button></div>
                    </td>
                    <td>
                        <button class="action-btn" onclick="openModal('budget','${b.id}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="deleteBudget('${b.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Helpers to open modals for budget and line
        function openBudgetLineModal(budgetId, lineId=null){
            modalTitle.textContent = lineId ? 'Edit Budget Line' : 'Add Budget Line';
            modalFormContent.innerHTML = getBudgetLineForm(budgetId, lineId);
            modal.classList.add('active');
            modal.setAttribute('data-type', 'budget-line');
            if(lineId) modal.setAttribute('data-id', lineId);
        }

        // ---------------- Accounts UI -----------------
        function getAccountForm(id) {
            let acc = null;
            if (id) acc = financeData.accounts.find(a => a.id === String(id));
            return `
                <div class="form-group">
                    <label for="account-name">Name</label>
                    <input type="text" id="account-name" class="form-control" value="${acc?acc.name:''}" required>
                </div>
                <div class="form-group">
                    <label for="account-type">Type</label>
                    <select id="account-type" class="form-control" required>
                        <option value="cash" ${acc&&acc.type==='cash'?'selected':''}>Cash</option>
                        <option value="bank" ${acc&&acc.type==='bank'?'selected':''}>Bank</option>
                        <option value="credit" ${acc&&acc.type==='credit'?'selected':''}>Credit</option>
                        <option value="investment" ${acc&&acc.type==='investment'?'selected':''}>Investment</option>
                        <option value="other" ${acc&&acc.type==='other'?'selected':''}>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="account-currency">Currency</label>
                    <input type="text" id="account-currency" class="form-control" value="${acc?acc.currency:(financeData.currency||'CAD')}" maxlength="8" required>
                </div>
                <div class="form-group">
                    <label for="account-opening">Opening Balance</label>
                    <input type="number" id="account-opening" class="form-control" step="0.01" min="0" value="${acc?acc.opening_balance:0}" required>
                </div>
                <div class="form-group">
                    <label class="checkbox-group">
                        <input type="checkbox" id="account-active" ${!acc || acc.active ? 'checked' : ''}/> Active
                    </label>
                </div>
            `;
        }

        async function saveAccount(id) {
            const name = document.getElementById('account-name').value.trim();
            const type = document.getElementById('account-type').value;
            const currency = document.getElementById('account-currency').value.trim().toUpperCase();
            const opening = parseFloat(document.getElementById('account-opening').value)||0;
            const active = document.getElementById('account-active').checked;

            const payload = {
                name, type, currency,
                opening_balance_cents: toCents(opening),
                active: active ? 1 : 0
            };

            if (id) {
                await apiSend('PUT', `/finance/api/accounts/${encodeURIComponent(id)}`, payload);
            } else {
                await apiSend('POST', '/finance/api/accounts', payload);
            }

            await loadData();
            updateUI();
        }

        async function deleteAccount(id) {
            await apiSend('DELETE', `/finance/api/accounts/${encodeURIComponent(id)}`);
            await loadData();
            updateUI();
        }

        function updateAccountsList() {
            const tbody = document.getElementById('accounts-list');
            if (!tbody) return;
            tbody.innerHTML='';
            if ((financeData.accounts||[]).length===0) {
                tbody.innerHTML = `<tr><td colspan="6" class="empty-state"><i class="fas fa-wallet"></i> <p>No accounts yet</p></td></tr>`;
                return;
            }
            financeData.accounts.forEach(a=>{
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${a.name}</td>
                    <td>${a.type}</td>
                    <td>${a.currency}</td>
                    <td>${formatCurrency(a.opening_balance)}</td>
                    <td>${a.active? 'Yes':'No'}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('account','${a.id}')"><i class="fas fa-edit"></i></button>
                        <button class="action-btn" onclick="deleteAccount('${a.id}')"><i class="fas fa-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function updateSummaryCards() {
            // Calculate and update summary values
            document.getElementById('income-ytd').textContent = formatCurrency(calculateYTDIncome());
            document.getElementById('total-debt').textContent = formatCurrency(calculateTotalDebt());
            document.getElementById('investments-value').textContent = formatCurrency(calculateInvestmentsValue());
            document.getElementById('savings-progress').textContent = calculateSavingsProgress() + '%';
            document.getElementById('month-shifts').textContent = calculateMonthShifts() + ' hrs';
        }

        // Calculation functions
        function calculateYTDIncome() {
            const currentYear = new Date().getFullYear();
            return financeData.payRuns
                .filter(payrun => new Date(payrun.periodEnd).getFullYear() === currentYear)
                .reduce((total, payrun) => total + payrun.netPay, 0);
        }

        function calculateTotalDebt() {
            return financeData.debts.reduce((total, debt) => total + debt.balance, 0);
        }

        function calculateInvestmentsValue() {
            const accountsValue = financeData.investmentAccounts.reduce((total, account) => total + account.value, 0);
            const investmentsValue = financeData.investments.reduce((total, investment) => total + (investment.quantity * investment.price), 0);
            return accountsValue + investmentsValue;
        }

        function calculateSavingsProgress() {
            if (financeData.savingsGoals.length === 0) return 0;
            const totalTarget = financeData.savingsGoals.reduce((total, goal) => total + goal.target, 0);
            const totalSaved = financeData.savingsGoals.reduce((total, goal) => total + goal.saved, 0);
            return Math.round((totalSaved / totalTarget) * 100);
        }

        function calculateMonthShifts() {
            const now = new Date();
            const currentMonth = now.getMonth();
            const currentYear = now.getFullYear();

            return financeData.shifts
                .filter(shift => {
                    const shiftDate = new Date(shift.date);
                    return shiftDate.getMonth() === currentMonth && shiftDate.getFullYear() === currentYear;
                })
                .reduce((total, shift) => total + calculateShiftHours(shift), 0);
        }

        function calculateShiftHours(shift) {
            if (!shift.startTime || !shift.endTime) return shift.hours || 0;

            // Parse time strings (format: HH:MM)
            const [startHours, startMinutes] = shift.startTime.split(':').map(Number);
            const [endHours, endMinutes] = shift.endTime.split(':').map(Number);

            // Calculate total minutes
            const startTotalMinutes = startHours * 60 + startMinutes;
            const endTotalMinutes = endHours * 60 + endMinutes;

            // Calculate difference in hours
            let diffMinutes = endTotalMinutes - startTotalMinutes;
            if (diffMinutes < 0) diffMinutes += 24 * 60; // Handle overnight shifts

            // Subtract break time
            diffMinutes -= (shift.breakMinutes || 0);

            return Math.max(0, diffMinutes / 60);
        }

        function calculateShiftEarnings(shift) {
            const hours = calculateShiftHours(shift);
            const rate = shift.rate || 0;
            const tips = shift.tips || 0;
            return (hours * rate) + tips;
        }

        // Format currency based on selected currency
        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: financeData.currency
            }).format(amount);
        }

        // List update functions
        function updateEmployersList() {
            const employersList = document.getElementById('employers-list');
            employersList.innerHTML = '';

            if (financeData.employers.length === 0) {
                employersList.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fas fa-building"></i>
                            <p>No employers added yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            financeData.employers.forEach(employer => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${employer.name}</td>
                    <td>${employer.paySchedule}</td>
                    <td>${formatCurrency(employer.baseRate)}/hr</td>
                    <td>
                        <button class="action-btn" onclick="openModal('employer', '${employer.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteEmployer('${employer.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                employersList.appendChild(row);
            });
        }

        function updatePayRunsList() {
            const payrunsList = document.getElementById('payruns-list');
            payrunsList.innerHTML = '';

            if (financeData.payRuns.length === 0) {
                payrunsList.innerHTML = `
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-money-bill-wave"></i>
                            <p>No pay runs recorded yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            financeData.payRuns.forEach(payrun => {
                const employer = financeData.employers.find(e => e.id === payrun.employerId) || { name: 'Unknown' };
                const period = `${formatDate(payrun.periodStart)} - ${formatDate(payrun.periodEnd)}`;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${period}</td>
                    <td>${employer.name}</td>
                    <td>${formatCurrency(payrun.grossPay)}</td>
                    <td>${formatCurrency(payrun.netPay)}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('payrun', '${payrun.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deletePayRun('${payrun.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                payrunsList.appendChild(row);
            });
        }

        function updateShiftsList() {
            const shiftsList = document.getElementById('shifts-list');
            shiftsList.innerHTML = '';

            if (financeData.shifts.length === 0) {
                shiftsList.innerHTML = `
                    <tr>
                        <td colspan="7" class="empty-state">
                            <i class="fas fa-clock"></i>
                            <p>No shifts recorded yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            // Sort shifts by date (newest first)
            const sortedShifts = [...financeData.shifts].sort((a, b) => new Date(b.date) - new Date(a.date));

            sortedShifts.forEach(shift => {
                const employer = financeData.employers.find(e => e.id === shift.employerId) || { name: 'None' };
                const hours = calculateShiftHours(shift).toFixed(2);
                const earnings = calculateShiftEarnings(shift);

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${formatDate(shift.date)}</td>
                    <td>${shift.startTime || 'N/A'}</td>
                    <td>${shift.endTime || 'N/A'}</td>
                    <td>${hours} hrs</td>
                    <td>${employer.name}</td>
                    <td>${formatCurrency(earnings)}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('shift', '${shift.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteShift('${shift.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                shiftsList.appendChild(row);
            });
        }

        function updateDebtsList() {
            const debtsContainer = document.getElementById('debts-container');
            const emptyState = document.getElementById('debts-empty-state');
            debtsContainer.innerHTML = '';

            if (financeData.debts.length === 0) {
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';

            // Update debt stats
            const totalDebt = calculateTotalDebt();
            const minPayments = financeData.debts.reduce((total, debt) => total + debt.minPayment, 0);
            const thisMonthDebt = financeData.debts.reduce((total, debt) => {
                const dueDate = debt.dueDate;
                const today = new Date();
                const currentDay = today.getDate();

                // If payment is due this month
                if (currentDay <= dueDate) {
                    return total + debt.minPayment;
                }
                return total;
            }, 0);

            document.getElementById('debt-total').textContent = formatCurrency(totalDebt);
            document.getElementById('debt-min-payments').textContent = formatCurrency(minPayments);
            document.getElementById('debt-this-month').textContent = formatCurrency(thisMonthDebt);

            // Create debt cards
            financeData.debts.forEach(debt => {
                const utilization = debt.limit > 0 ? (debt.balance / debt.limit) * 100 : 0;
                const aprClass = debt.apr >= 15 ? 'apr-high' : debt.apr >= 8 ? 'apr-medium' : 'apr-low';

                const debtCard = document.createElement('div');
                debtCard.className = 'debt-card fade-in';
                debtCard.innerHTML = `
                    <div class="debt-card-header">
                        <div class="debt-lender">${debt.lender}</div>
                        <div class="debt-type-badge">${formatDebtType(debt.type)}</div>
                    </div>

                    <div class="debt-details">
                        <div class="debt-detail-item">
                            <div class="debt-detail-label">Balance</div>
                            <div class="debt-detail-value">${formatCurrency(debt.balance)}</div>
                        </div>
                        <div class="debt-detail-item">
                            <div class="debt-detail-label">Limit/Original</div>
                            <div class="debt-detail-value">${formatCurrency(debt.limit)}</div>
                        </div>
                        <div class="debt-detail-item">
                            <div class="debt-detail-label">APR</div>
                            <div class="debt-detail-value"><span class="debt-apr ${aprClass}">${debt.apr}%</span></div>
                        </div>
                        <div class="debt-detail-item">
                            <div class="debt-detail-label">Min Payment</div>
                            <div class="debt-detail-value">${formatCurrency(debt.minPayment)}</div>
                        </div>
                    </div>

                    ${debt.limit > 0 ? `
                    <div class="progress-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${utilization}%;"></div>
                        </div>
                        <div class="progress-info">
                            <span>Credit Utilization</span>
                            <span>${utilization.toFixed(1)}%</span>
                        </div>
                        ${utilization > 30 ? `<div class="utilization-warning">High utilization may affect credit score</div>` : ''}
                    </div>
                    ` : ''}

                    <div class="debt-actions">
                        <button class="btn" onclick="openPaymentModal('${debt.id}')">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Record Payment</span>
                        </button>
                        <button class="btn" onclick="openModal('debt', '${debt.id}')">
                            <i class="fas fa-edit"></i>
                            <span>Edit</span>
                        </button>
                        <button class="btn" onclick="deleteDebt('${debt.id}')">
                            <i class="fas fa-trash"></i>
                            <span>Delete</span>
                        </button>
                    </div>

                    ${debt.paymentHistory && debt.paymentHistory.length > 0 ? `
                    <div class="payment-history">
                        <div class="payment-history-title">Recent Payments</div>
                        ${debt.paymentHistory.slice(-3).reverse().map(payment => `
                            <div class="payment-item">
                                <div class="payment-date">${formatDate(payment.date)}</div>
                                <div>
                                    <div class="payment-amount">${formatCurrency(payment.amount)}</div>
                                    ${payment.notes ? `<div class="payment-principal">${payment.notes}</div>` : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    ` : ''}
                `;

                debtsContainer.appendChild(debtCard);
            });
        }

        function updateInvestmentAccountsList() {
            const accountsList = document.getElementById('investment-accounts-list');
            accountsList.innerHTML = '';

            if (financeData.investmentAccounts.length === 0) {
                accountsList.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fas fa-chart-line"></i>
                            <p>No investment accounts added yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            financeData.investmentAccounts.forEach(account => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${account.name}</td>
                    <td>${formatAccountType(account.type)}</td>
                    <td>${formatCurrency(account.value)}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('investment-account', '${account.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteInvestmentAccount('${account.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                accountsList.appendChild(row);
            });
        }

        function updateInvestmentsList() {
            const investmentsList = document.getElementById('investments-list');
            investmentsList.innerHTML = '';

            // Update investment stats
            const portfolioValue = calculateInvestmentsValue();
            const holdingsCount = financeData.investments.length;

            // Calculate gain/loss (simplified - would need purchase price data for real calculation)
            const investmentGain = portfolioValue - financeData.investmentAccounts.reduce((total, account) => total + account.value, 0);

            document.getElementById('portfolio-value').textContent = formatCurrency(portfolioValue);
            document.getElementById('investment-gain').textContent = `${investmentGain >= 0 ? '+' : ''}${formatCurrency(investmentGain)}`;
            document.getElementById('investment-gain').style.color = investmentGain >= 0 ? 'var(--success)' : 'var(--danger)';
            document.getElementById('holdings-count').textContent = holdingsCount;

            if (financeData.investments.length === 0) {
                investmentsList.innerHTML = `
                    <tr>
                        <td colspan="5" class="empty-state">
                            <i class="fas fa-coins"></i>
                            <p>No investments added yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            financeData.investments.forEach(investment => {
                const account = financeData.investmentAccounts.find(a => a.id === investment.accountId) || { name: 'Unknown' };
                const value = investment.quantity * investment.price;

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${investment.name}</td>
                    <td>${investment.symbol || '-'}</td>
                    <td>${investment.quantity}</td>
                    <td>${formatCurrency(value)}</td>
                    <td>
                        <button class="action-btn" onclick="openModal('investment', '${investment.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteInvestment('${investment.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                investmentsList.appendChild(row);
            });
        }

        function updateSavingsList() {
            const savingsList = document.getElementById('savings-list');
            savingsList.innerHTML = '';

            // Update savings stats
            const totalSaved = financeData.savingsGoals.reduce((total, goal) => total + goal.saved, 0);
            const thisMonthSaved = 0; // This would need to track monthly contributions
            const completedGoals = financeData.savingsGoals.filter(goal => goal.saved >= goal.target).length;

            document.getElementById('total-saved').textContent = formatCurrency(totalSaved);
            document.getElementById('savings-this-month').textContent = formatCurrency(thisMonthSaved);
            document.getElementById('completed-goals').textContent = completedGoals;

            if (financeData.savingsGoals.length === 0) {
                savingsList.innerHTML = `
                    <tr>
                        <td colspan="4" class="empty-state">
                            <i class="fas fa-piggy-bank"></i>
                            <p>No savings goals added yet</p>
                        </td>
                    </tr>
                `;
                return;
            }

            financeData.savingsGoals.forEach(goal => {
                const progress = Math.min(100, (goal.saved / goal.target) * 100);
                const progressClass = progress >= 100 ? 'success' : progress >= 75 ? 'warning' : 'primary';

                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${goal.name}</td>
                    <td>${formatCurrency(goal.target)}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="flex: 1; height: 0.5rem; background: var(--primary-light); border-radius: var(--radius-sm); overflow: hidden;">
                                <div style="height: 100%; width: ${progress}%; background: var(--${progressClass});"></div>
                            </div>
                            <span>${progress.toFixed(0)}%</span>
                        </div>
                    </td>
                    <td>
                        <button class="action-btn" onclick="openModal('savings', '${goal.id}')">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn" onclick="deleteSavingsGoal('${goal.id}')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                `;
                savingsList.appendChild(row);
            });
        }

        // Helper functions
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString();
        }

        function formatTime(timeString) {
            if (!timeString) return 'N/A';
            const [hours, minutes] = timeString.split(':');
            const hour = parseInt(hours);
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minutes} ${period}`;
        }

        // Enhanced formatDebtType function to handle custom types
        function formatDebtType(type) {
            const types = {
                'credit-card': 'Credit Card',
                'mortgage': 'Mortgage',
                'student-loan': 'Student Loan',
                'car-loan': 'Car Loan',
                'personal-loan': 'Personal Loan',
                'other': 'Other'
            };
            return types[type] || type;
        }

        // Enhanced formatAccountType function to handle custom types
        function formatAccountType(type) {
            const types = {
                'brokerage': 'Brokerage',
                'ira': 'IRA',
                'roth-ira': 'Roth IRA',
                '401k': '401(k)',
                'hsa': 'HSA',
                'other': 'Other'
            };
            return types[type] || type;
        }

        // Data management functions
        function saveEmployer(id) {
            const name = document.getElementById('employer-name').value;
            const paySchedule = document.getElementById('pay-schedule').value;
            const baseRate = parseFloat(document.getElementById('base-rate').value);

            if (id) {
                // Update existing employer
                const index = financeData.employers.findIndex(e => e.id === id);
                if (index !== -1) {
                    financeData.employers[index] = { id, name, paySchedule, baseRate };
                }
            } else {
                // Add new employer
                const newEmployer = {
                    id: Date.now().toString(),
                    name,
                    paySchedule,
                    baseRate
                };
                financeData.employers.push(newEmployer);
            }

            saveData();
            updateUI();
        }

        function deleteEmployer(id) {
            financeData.employers = financeData.employers.filter(e => e.id !== id);
            // Also remove associated pay runs and shifts
            financeData.payRuns = financeData.payRuns.filter(p => p.employerId !== id);
            financeData.shifts = financeData.shifts.filter(s => s.employerId !== id);
            saveData();
            updateUI();
        }

        function savePayRun(id) {
            const employerId = document.getElementById('payrun-employer').value;
            const periodStart = document.getElementById('payrun-period-start').value;
            const periodEnd = document.getElementById('payrun-period-end').value;
            const grossPay = parseFloat(document.getElementById('payrun-gross').value);
            const netPay = parseFloat(document.getElementById('payrun-net').value);

            if (id) {
                // Update existing pay run
                const index = financeData.payRuns.findIndex(p => p.id === id);
                if (index !== -1) {
                    financeData.payRuns[index] = { id, employerId, periodStart, periodEnd, grossPay, netPay };
                }
            } else {
                // Add new pay run
                const newPayRun = {
                    id: Date.now().toString(),
                    employerId,
                    periodStart,
                    periodEnd,
                    grossPay,
                    netPay
                };
                financeData.payRuns.push(newPayRun);
            }

            saveData();
            updateUI();
        }

        function deletePayRun(id) {
            financeData.payRuns = financeData.payRuns.filter(p => p.id !== id);
            saveData();
            updateUI();
        }

        function saveShift(id) {
            const date = document.getElementById('shift-date').value;
            const startTime = document.getElementById('shift-start-time').value;
            const endTime = document.getElementById('shift-end-time').value;
            const breakMinutes = parseInt(document.getElementById('shift-break').value) || 0;
            const employerId = document.getElementById('shift-employer').value || null;
            const role = document.getElementById('shift-role').value || '';
            const rate = parseFloat(document.getElementById('shift-rate').value);
            const tips = parseFloat(document.getElementById('shift-tips').value) || 0;
            const location = document.getElementById('shift-location').value || '';
            const notes = document.getElementById('shift-notes').value || '';

            // Calculate hours based on start/end times and break
            let hours = 0;
            if (startTime && endTime) {
                const [startHours, startMins] = startTime.split(':').map(Number);
                const [endHours, endMins] = endTime.split(':').map(Number);

                let startTotalMinutes = startHours * 60 + startMins;
                let endTotalMinutes = endHours * 60 + endMins;

                // Handle overnight shifts
                if (endTotalMinutes < startTotalMinutes) {
                    endTotalMinutes += 24 * 60;
                }

                hours = (endTotalMinutes - startTotalMinutes - breakMinutes) / 60;
                hours = Math.max(0, hours); // Ensure non-negative
            }

            if (id) {
                // Update existing shift
                const index = financeData.shifts.findIndex(s => s.id === id);
                if (index !== -1) {
                    financeData.shifts[index] = { 
                        id, date, startTime, endTime, breakMinutes, 
                        employerId, role, rate, tips, location, notes, hours 
                    };
                }
            } else {
                // Add new shift
                const newShift = {
                    id: Date.now().toString(),
                    date,
                    startTime,
                    endTime,
                    breakMinutes,
                    employerId,
                    role,
                    rate,
                    tips,
                    location,
                    notes,
                    hours
                };
                financeData.shifts.push(newShift);
            }

            saveData();
            updateUI();
        }

        function deleteShift(id) {
            financeData.shifts = financeData.shifts.filter(s => s.id !== id);
            saveData();
            updateUI();
        }

        // Enhanced saveDebt function to handle custom types
        function saveDebt(id) {
            const lender = document.getElementById('debt-lender').value;
            let type = document.getElementById('debt-type').value;
            const balance = parseFloat(document.getElementById('debt-balance').value);
            const limit = parseFloat(document.getElementById('debt-limit').value);
            const apr = parseFloat(document.getElementById('debt-apr').value);
            const minPayment = parseFloat(document.getElementById('debt-min-payment').value);
            const dueDate = parseInt(document.getElementById('debt-due-date').value);

            // Handle custom type
            if (type === 'other') {
                const customType = document.getElementById('debt-custom-type').value.trim();
                type = customType || 'other';
            }

            if (id) {
                // Update existing debt
                const index = financeData.debts.findIndex(d => d.id === id);
                if (index !== -1) {
                    // Preserve payment history
                    const paymentHistory = financeData.debts[index].paymentHistory || [];
                    financeData.debts[index] = { id, lender, type, balance, limit, apr, minPayment, dueDate, paymentHistory };
                }
            } else {
                // Add new debt
                const newDebt = {
                    id: Date.now().toString(),
                    lender,
                    type,
                    balance,
                    limit,
                    apr,
                    minPayment,
                    dueDate,
                    paymentHistory: []
                };
                financeData.debts.push(newDebt);
            }

            saveData();
            updateUI();
        }

        function deleteDebt(id) {
            financeData.debts = financeData.debts.filter(d => d.id !== id);
            saveData();
            updateUI();
        }

        // Enhanced saveInvestmentAccount function to handle custom types
        function saveInvestmentAccount(id) {
            const name = document.getElementById('account-name').value;
            let type = document.getElementById('account-type').value;
            const value = parseFloat(document.getElementById('account-value').value);

            // Handle custom type
            if (type === 'other') {
                const customType = document.getElementById('account-custom-type').value.trim();
                type = customType || 'other';
            }

            if (id) {
                // Update existing account
                const index = financeData.investmentAccounts.findIndex(a => a.id === id);
                if (index !== -1) {
                    financeData.investmentAccounts[index] = { id, name, type, value };
                }
            } else {
                // Add new account
                const newAccount = {
                    id: Date.now().toString(),
                    name,
                    type,
                    value
                };
                financeData.investmentAccounts.push(newAccount);
            }

            saveData();
            updateUI();
        }

        function deleteInvestmentAccount(id) {
            financeData.investmentAccounts = financeData.investmentAccounts.filter(a => a.id !== id);
            // Also remove associated investments
            financeData.investments = financeData.investments.filter(i => i.accountId !== id);
            saveData();
            updateUI();
        }

        function saveInvestment(id) {
            const accountId = document.getElementById('investment-account').value;
            const name = document.getElementById('investment-name').value;
            const symbol = document.getElementById('investment-symbol').value;
            const quantity = parseFloat(document.getElementById('investment-quantity').value);
            const price = parseFloat(document.getElementById('investment-price').value);

            if (id) {
                // Update existing investment
                const index = financeData.investments.findIndex(i => i.id === id);
                if (index !== -1) {
                    financeData.investments[index] = { id, accountId, name, symbol, quantity, price };
                }
            } else {
                // Add new investment
                const newInvestment = {
                    id: Date.now().toString(),
                    accountId,
                    name,
                    symbol,
                    quantity,
                    price
                };
                financeData.investments.push(newInvestment);
            }

            saveData();
            updateUI();
        }

        function deleteInvestment(id) {
            financeData.investments = financeData.investments.filter(i => i.id !== id);
            saveData();
            updateUI();
        }

        async function saveSavingsGoal(id) {
            const name = document.getElementById('savings-goal').value;
            const target = parseFloat(document.getElementById('savings-target').value);
            const saved = parseFloat(document.getElementById('savings-saved').value);
            const deadline = document.getElementById('savings-deadline').value;

            const payload = {
                name,
                target_cents: toCents(target),
                saved_cents: toCents(saved),
                deadline: deadline || null,
                currency: financeData.currency
            };

            if (id) {
                await apiSend('PUT', `/finance/api/savings_goals/${encodeURIComponent(id)}`, payload);
            } else {
                await apiSend('POST', '/finance/api/savings_goals', payload);
            }

            await loadData();
            updateUI();
        }

        async function deleteSavingsGoal(id) {
            await apiSend('DELETE', `/finance/api/savings_goals/${encodeURIComponent(id)}`);
            await loadData();
            updateUI();
        }

// Initialize the app when the DOM is loaded
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
</body>



<?php require 'app/views/templates/footer.php'; ?>
