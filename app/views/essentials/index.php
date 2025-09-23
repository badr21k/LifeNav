
<?php require 'app/views/templates/header.php'; ?>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1a1a1a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>LifeNav - Financial Tracker</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/react@18.2.0/umd/react.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-dom@18.2.0/umd/react-dom.production.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@babel/standalone@7.23.2/babel.min.js"></script>
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
            --safe-area-inset-top: env(safe-area-inset-top, 0);
            --safe-area-inset-bottom: env(safe-area-inset-bottom, 0);
            --safe-area-inset-left: env(safe-area-inset-left, 0);
            --safe-area-inset-right: env(safe-area-inset-right, 0);
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

        html {
            height: 100%;
            font-size: 16px;
        }

        body {
            font-family: var(--font-sans);
            background: linear-gradient(to bottom, var(--background), var(--background));
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
            padding: 1.5rem;
            padding-left: calc(1.5rem + var(--safe-area-inset-left));
            padding-right: calc(1.5rem + var(--safe-area-inset-right));
            padding-top: calc(1.5rem + var(--safe-area-inset-top));
            padding-bottom: calc(1.5rem + var(--safe-area-inset-bottom));
            font-size: 0.9375rem;
            font-weight: 400;
            display: flex;
            flex-direction: column;
            overscroll-behavior: none;
            -webkit-tap-highlight-color: transparent;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            width: 100%;
            flex: 1;
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
            touch-action: manipulation;
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

        .mode-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            color: var(--text-light);
            padding: 0.75rem 1rem;
            background-color: var(--primary-light);
            border-radius: var(--radius-md);
            font-weight: 500;
            font-size: 0.875rem;
            box-shadow: var(--shadow-sm);
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

        .progress-bar {
            height: 0.5rem;
            background: var(--primary-light);
            border-radius: var(--radius-md);
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--primary);
            border-radius: var(--radius-md);
            transition: width 0.5s ease;
        }

        .category-totals {
            margin-top: 1rem;
        }

        .category-total-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border);
            font-size: 0.875rem;
        }

        .category-total-item:last-child {
            border-bottom: none;
        }

        .category-total-name {
            font-weight: 600;
        }

        .category-total-amount {
            color: var(--primary);
            font-weight: 600;
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
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
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
            scroll-snap-align: start;
        }

        .tab.active, .tab:hover {
            background: var(--primary);
            color: white;
            box-shadow: 0 2px 8px rgba(44, 107, 95, 0.2);
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

        .currency-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius-sm);
            background: var(--primary-light);
            color: var(--primary);
            font-size: 0.75rem;
            font-weight: 500;
            margin-right: 0.5rem;
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

        .action-btn.delete-btn:hover, .action-btn.delete-btn:focus-visible {
            color: var(--danger);
        }

        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .category-item {
            padding: 1rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
            background-color: var(--card);
            font-weight: 600;
            font-size: 0.875rem;
            box-shadow: var(--shadow-sm);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            touch-action: manipulation;
        }

        .category-item:hover, .category-item:focus-visible {
            border-color: var(--primary);
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(44, 107, 95, 0.15);
            outline: none;
        }

        .category-item.active {
            border-color: var(--primary);
            background-color: var(--primary-light);
            color: var(--primary);
        }

        .chart-container {
            position: relative;
            height: clamp(200px, 40vw, 280px);
            margin-top: 1.5rem;
            padding: 1rem;
            background-color: var(--card);
            border-radius: var(--radius-md);
            box-shadow: var(--shadow-md);
        }

        .category-chart {
            margin-top: 1.5rem;
        }

        .category-chart h3 {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 1rem;
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
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 107, 95, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .checkbox-group input {
            width: 1.25rem;
            height: 1.25rem;
            accent-color: var(--primary);
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

        .modal.modal-enter-active {
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
            -webkit-overflow-scrolling: touch;
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

        .subcat-group {
            margin-bottom: 1.5rem;
        }

        .subcat-group h4 {
            color: var(--text);
            margin-bottom: 0.75rem;
            font-size: 1.125rem;
            font-weight: 700;
        }

        .subcat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .subcat-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .expense-section {
            margin-top: 1.5rem;
        }

        .expense-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .expense-header h3 {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-enter {
            opacity: 0;
            transform: scale(0.95);
        }

        .modal-enter-active {
            opacity: 1;
            transform: scale(1);
            transition: opacity 200ms ease-in-out, transform 200ms ease-in-out;
        }

        .modal-exit {
            opacity: 1;
            transform: scale(1);
        }

        .modal-exit-active {
            opacity: 0;
            transform: scale(0.95);
            transition: opacity 200ms ease-in-out, transform 200ms ease-in-out;
        }

        @media (min-width: 640px) {
            .header {
                flex-direction: row;
                justify-content: space-between;
                padding: 1.5rem;
            }
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
                max-width: 36rem;
            }
            .modal-title {
                font-size: 1.5rem;
            }
            .chart-container {
                height: 300px;
            }
        }

        @media (min-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(3, 1fr);
            }
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
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
            .chart-container {
                height: 220px;
            }
        }

        @media (max-width: 480px) {
            .expense-table {
                display: block;
            }
            .expense-table thead {
                display: none;
            }
            .expense-table tbody {
                display: block;
            }
            .expense-table tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid var(--border);
                border-radius: var(--radius-md);
                background-color: var(--card);
            }
            .expense-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem 0.75rem;
                border: none;
                border-bottom: 1px solid var(--border);
                background-color: transparent;
            }
            .expense-table td:last-child {
                border-bottom: none;
            }
            .expense-table td:before {
                content: attr(data-label);
                font-weight: 600;
                color: var(--text-light);
                flex: 1;
                min-width: 100px;
            }
            .expense-table td[data-label="Actions"] {
                justify-content: flex-end;
            }
            .expense-table td[data-label="Actions"]:before {
                content: none;
            }
        }

        @media (max-width: 360px) {
            .category-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
            .btn {
                padding: 0.5rem 0.75rem;
            }
            .tab {
                padding: 0.5rem;
                font-size: 0.75rem;
            }
        }

        @media (display-mode: standalone) {
            body {
                padding-top: calc(1.5rem + var(--safe-area-inset-top));
            }
        }

        @media (max-height: 500px) and (orientation: landscape) {
            .modal-content {
                max-height: 80vh;
            }
            .chart-container {
                height: 180px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
            .btn:hover, .btn:focus-visible,
            .action-btn:hover, .action-btn:focus-visible,
            .category-item:hover, .category-item:focus-visible {
                transform: none;
            }
        }

        @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
            .btn, .tab, .category-item, .action-btn {
                border-width: 0.5px;
            }
        }


</style>

</head>
<body>
    <div id="root"></div>
    <script type="text/babel">
        const { useState, useEffect, useRef } = React;
        // CSRF token for API calls
        const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
        async function apiGet(path) {
            const res = await fetch(path, { credentials: 'same-origin' });
            if (!res.ok) throw new Error('API GET ' + path + ' failed: ' + res.status);
            return res.json();
        }
        async function apiSend(method, path, body) {
            const res = await fetch(path, {
                method,
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                credentials: 'same-origin',
                body: body ? JSON.stringify(body) : null
            });
            if (!res.ok) { const t = await res.text(); throw new Error(method+' '+path+' failed: '+res.status+' '+t); }
            return res.status===204 ? null : res.json();
        }


// Currency list
const currencies = [
    { code: 'CAD', symbol: 'C$', name: 'Canadian Dollar' },
    { code: 'USD', symbol: '$', name: 'US Dollar' },
    { code: 'EUR', symbol: '€', name: 'Euro' },
    { code: 'GBP', symbol: '£', name: 'British Pound' },
    { code: 'JPY', symbol: '¥', name: 'Japanese Yen' },
    { code: 'AUD', symbol: 'A$', name: 'Australian Dollar' },
    { code: 'CHF', symbol: 'Fr', name: 'Swiss Franc' },
    { code: 'CNY', symbol: '¥', name: 'Chinese Yuan' },
    { code: 'INR', symbol: '₹', name: 'Indian Rupee' },
    { code: 'MXN', symbol: '$', name: 'Mexican Peso' },
    { code: 'NZD', symbol: '$', name: 'New Zealand Dollar' },
    { code: 'SGD', symbol: '$', name: 'Singapore Dollar' },
    { code: 'HKD', symbol: '$', name: 'Hong Kong Dollar' },
    { code: 'SEK', symbol: 'kr', name: 'Swedish Krona' },
    { code: 'KRW', symbol: '₩', name: 'South Korean Won' },
    { code: 'NOK', symbol: 'kr', name: 'Norwegian Krone' },
    { code: 'TRY', symbol: '₺', name: 'Turkish Lira' },
    { code: 'RUB', symbol: '₽', name: 'Russian Ruble' },
    { code: 'BRL', symbol: 'R$', name: 'Brazilian Real' },
    { code: 'ZAR', symbol: 'R', name: 'South African Rand' }
];

// Category icons
const categoryIcons = {
    'Transportation': 'fa-car',
    'Accommodation': 'fa-home',
    'Food & Dining': 'fa-utensils',
    'Health': 'fa-heartbeat',
    'Entertainment': 'fa-ticket-alt',
    'Travel': 'fa-plane',
    'Activities': 'fa-hiking'
};

// Helper functions
const getStartOfWeek = (date) => {
    const day = date.getDay();
    const diff = (day === 0 ? -6 : 1 - day);
    const start = new Date(date);
    start.setDate(start.getDate() + diff);
    start.setHours(0, 0, 0, 0);
    return start;
};

const getEndOfWeek = (date) => {
    const start = getStartOfWeek(date);
    const end = new Date(start);
    end.setDate(end.getDate() + 6);
    end.setHours(23, 59, 59, 999);
    return end;
};

// Error Boundary Component
function ErrorBoundary({ children }) {
    const [hasError, setHasError] = useState(false);

    useEffect(() => {
        const handleError = (error, errorInfo) => {
            console.error('ErrorBoundary caught:', error, errorInfo);
            setHasError(true);
        };
        window.addEventListener('error', handleError);
        return () => window.removeEventListener('error', handleError);
    }, []);

    if (hasError) {
        return (
            <div className="card">
                <div className="empty-state">
                    <i className="fas fa-exclamation-circle"></i>
                    <p>Something went wrong. Please refresh the page.</p>
                </div>
            </div>
        );
    }
    return children;
}

// Main App Component
function App() {
    const [state, setState] = useState({
        mode: 'normal',
        expenses: [],
        dataSource: 'Local',
        maps: { catByName: {}, subByCatName: {}, pmByName: {} },
        categories: {
            normal: [
                { name: 'Transportation', subcategories: ['Car Insurance', 'Fuel', 'Parking', 'Public Transit', 'Other'] },
                { name: 'Accommodation', subcategories: ['Rent', 'Mortgage', 'Utilities', 'Internet', 'Other'] },
                { name: 'Food & Dining', subcategories: ['Groceries', 'Restaurants', 'Coffee', 'Takeout', 'Other'] },
                { name: 'Health', subcategories: ['Doctor Visits', 'Medications', 'Dental', 'Vision', 'Fitness', 'Other'] },
                { name: 'Entertainment', subcategories: ['Movies', 'Games', 'Sports', 'Concerts', 'Other'] },
            ],
            travel: [
                { name: 'Travel', subcategories: ['Flights', 'Hotels', 'Dining', 'Tours', 'Visas', 'Other'] },
                { name: 'Transportation', subcategories: ['Local Transport', 'Car Rental', 'Fuel', 'Parking', 'Other'] },
                { name: 'Accommodation', subcategories: ['Hotels', 'Airbnb', 'Hostels', 'Other'] },
                { name: 'Health', subcategories: ['Travel Insurance', 'Medications', 'Vaccinations', 'Other'] },
                { name: 'Activities', subcategories: ['Tours', 'Museums', 'Entertainment', 'Shopping', 'Other'] },
            ],
        },
        currentCategory: 'Transportation',
        currentSubcategory: null,
        showCharts: false,
        baseCurrency: 'CAD',
        exchangeRates: {},
        charts: {},
        paycheck: 0,
        weeklyBudgetNormal: 0,
        travelBudget: 0,
        weeklyBudgetTravel: 0,
        editingId: null,
        fromRecurringList: false,
        modal: null,
        error: null,
    });

    const chartRef = useRef(null);
    const categoryChartRefs = useRef({});

    // Fetch backend init and expenses
    const loadEssentials = async () => {
        try {
            const init = await apiGet('/essentials/api/init');
            const catByName = {};
            (init.categories||[]).forEach(c=>{ catByName[c.name]=c.id; });
            const subByCatName = {};
            (init.subcategories||[]).forEach(s=>{
                const cat = (init.categories||[]).find(c=>c.id===s.category_id);
                const cname = cat ? cat.name : 'Unknown';
                if(!subByCatName[cname]) subByCatName[cname] = {};
                subByCatName[cname][s.name] = s.id;
            });
            const pmByName = {}; (init.payment_methods||[]).forEach(p=> pmByName[p.name]=p.id);

            const list = await apiGet('/essentials/api/expenses');
            const rows = (list||[]).map(r=>({
                id: String(r.id),
                mode: 'normal',
                category: r.category_name || 'Unknown',
                subcategory: r.subcategory_name || 'Other',
                amount: (Number(r.amount_cents)||0)/100,
                currency: r.currency || 'CAD',
                symbol: currencies.find(c=>c.code===(r.currency||'CAD'))?.symbol || 'C$',
                date: r.date,
                description: r.note || r.merchant || '',
                countWeekly: true,
                recurring: false
            }));
            setState(prev=>({ ...prev, expenses: rows, dataSource: 'Database', maps: { catByName, subByCatName, pmByName } }));
        } catch (e) { console.error(e); }
    };

    // Fetch exchange rates
    const getExchangeRates = async () => {
        if (Object.keys(state.exchangeRates).length > 0) return;
        try {
            const response = await fetch(`https://api.exchangerate-api.com/v4/latest/${state.baseCurrency}`);
            if (!response.ok) throw new Error('Failed to fetch exchange rates');
            const data = await response.json();
            setState(prev => ({ ...prev, exchangeRates: { ...data.rates, [state.baseCurrency]: 1 } }));
        } catch (error) {
            console.error('Failed to fetch exchange rates:', error);
            setState(prev => ({ ...prev, error: 'Failed to fetch exchange rates. Using default rate of 1.' }));
        }
    };

    // Convert amount to base currency
    const convertToBase = (amount, currency) => {
        if (currency === state.baseCurrency) return amount;
        const rate = state.exchangeRates[currency] || 1;
        return amount / rate;
    };

    // Toggle mode
    const toggleMode = () => {
        setState(prev => ({
            ...prev,
            mode: prev.mode === 'normal' ? 'travel' : 'normal',
            currentCategory: prev.categories[prev.mode === 'normal' ? 'travel' : 'normal'][0].name,
            currentSubcategory: null,
            showCharts: false,
        }));
    };

    // Toggle charts
    const toggleCharts = () => {
        setState(prev => ({ ...prev, showCharts: !prev.showCharts }));
    };

    // Open modal
    const openModal = (modalType, editingId = null, fromRecurringList = false) => {
        setState(prev => ({ ...prev, modal: modalType, editingId, fromRecurringList }));
    };

    // Close modal
    const closeModal = () => {
        setState(prev => ({ ...prev, modal: null, editingId: null, fromRecurringList: false, error: null }));
    };

    // Save new category
    const saveCategory = () => {
        const nameInput = document.getElementById('new-category-name');
        if (!nameInput) return;
        const name = nameInput.value.trim();
        if (!name) {
            setState(prev => ({ ...prev, error: 'Please enter a category name' }));
            return;
        }
        setState(prev => ({
            ...prev,
            categories: {
                ...prev.categories,
                [prev.mode]: [...prev.categories[prev.mode], { name, subcategories: ['Other'] }],
            },
            modal: null,
            error: null,
        }));
    };

    // Save new subcategory
    const saveSubcategory = () => {
        const nameInput = document.getElementById('new-subcategory-name');
        if (!nameInput) return;
        const name = nameInput.value.trim();
        if (!name) {
            setState(prev => ({ ...prev, error: 'Please enter a subcategory name' }));
            return;
        }
        setState(prev => ({
            ...prev,
            categories: {
                ...prev.categories,
                [prev.mode]: prev.categories[prev.mode].map(cat =>
                    cat.name === prev.currentCategory
                        ? { ...cat, subcategories: [...cat.subcategories, name] }
                        : cat
                ),
            },
            modal: null,
            error: null,
        }));
    };

    // Save budgets
    const saveBudgets = () => {
        const paycheck = parseFloat(document.getElementById('paycheck')?.value) || 0;
        const weeklyBudgetNormal = parseFloat(document.getElementById('weekly-budget-normal')?.value) || 0;
        const travelBudget = parseFloat(document.getElementById('travel-budget')?.value) || 0;
        const weeklyBudgetTravel = parseFloat(document.getElementById('weekly-budget-travel')?.value) || 0;
        setState(prev => ({
            ...prev,
            paycheck: prev.mode === 'normal' ? paycheck : prev.paycheck,
            weeklyBudgetNormal: prev.mode === 'normal' ? weeklyBudgetNormal : prev.weeklyBudgetNormal,
            travelBudget: prev.mode === 'travel' ? travelBudget : prev.travelBudget,
            weeklyBudgetTravel: prev.mode === 'travel' ? weeklyBudgetTravel : prev.weeklyBudgetTravel,
            modal: null,
            error: null,
        }));
    };

    // Save expense (DB)
    const saveExpense = async () => {
        const amountInput = document.getElementById('amount');
        const dateInput = document.getElementById('date');
        const recurringInput = document.getElementById('recurring');
        const recurringStartInput = document.getElementById('recurring-start');
        if (!amountInput || !dateInput) return;

        const amount = parseFloat(amountInput.value);
        if (!amount || isNaN(amount) || amount <= 0) {
            setState(prev => ({ ...prev, error: 'Please enter a valid amount' }));
            return;
        }
        if (recurringInput?.checked && !recurringStartInput?.value) {
            setState(prev => ({ ...prev, error: 'Please enter a start date for recurring expenses' }));
            return;
        }

        const category = document.getElementById('modal-category')?.value;
        const subcategory = document.getElementById('modal-subcategory')?.value || null;
        const currency = document.getElementById('currency')?.value || 'CAD';
        const note = document.getElementById('description')?.value || subcategory || '';
        const catId = state.maps.catByName[category] || null;
        const subId = subcategory ? ((state.maps.subByCatName[category]||{})[subcategory] || null) : null;
        if (!catId) { setState(prev=>({ ...prev, error: 'Unknown category' })); return; }

        const payload = { date: dateInput.value, amount_cents: Math.round(amount*100), currency, category_id: catId, subcategory_id: subId, payment_method_id: null, merchant: '', note };
        try {
            if (state.editingId) await apiSend('PUT', `/essentials/api/expenses/${encodeURIComponent(state.editingId)}`, payload);
            else await apiSend('POST', '/essentials/api/expenses', payload);
            await loadEssentials();
            setState(prev=>({ ...prev, modal: null, editingId: null, fromRecurringList: false, error: null }));
        } catch (e) { setState(prev=>({ ...prev, error: e.message })); }
    };

    // Edit expense
    const editExpense = (id) => {
        openModal('add-expense', id, state.fromRecurringList);
    };

    // Delete expense (DB)
    const deleteExpense = async (id) => {
        try { await apiSend('DELETE', `/essentials/api/expenses/${encodeURIComponent(id)}`); await loadEssentials(); }
        catch (e) { console.error(e); }
    };

    // Render charts
    useEffect(() => {
        if (!state.showCharts || state.expenses.length === 0) return;

        const modeExpenses = state.expenses.filter(exp => exp.mode === state.mode);
        const categoryTotals = {};
        modeExpenses.forEach(exp => {
            categoryTotals[exp.category] = (categoryTotals[exp.category] || 0) + convertToBase(exp.amount, exp.currency);
        });

        if (chartRef.current) {
            if (state.charts.main) state.charts.main.destroy();
            const ctx = chartRef.current.getContext('2d');
            const newChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(categoryTotals),
                    datasets: [{
                        data: Object.values(categoryTotals),
                        backgroundColor: ['#1a1a1a', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db'],
                        borderWidth: 1,
                        borderColor: '#fff',
                        hoverOffset: 12,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { font: { size: 12, family: 'Inter', weight: '600' }, padding: 16 },
                        },
                        tooltip: { backgroundColor: '#1a1a1a', bodyFont: { family: 'Inter', size: 12 }, titleFont: { family: 'Inter', size: 14 } },
                    },
                    cutout: '65%',
                    animation: { animateScale: true },
                },
            });
            setState(prev => ({ ...prev, charts: { ...prev.charts, main: newChart } }));
        }

        const categorySubtotals = {};
        modeExpenses.forEach(exp => {
            if (!categorySubtotals[exp.category]) categorySubtotals[exp.category] = {};
            categorySubtotals[exp.category][exp.subcategory] = (categorySubtotals[exp.category][exp.subcategory] || 0) + convertToBase(exp.amount, exp.currency);
        });

        Object.keys(categorySubtotals).forEach(cat => {
            if (categoryChartRefs.current[cat] && Object.keys(categorySubtotals[cat]).length > 0) {
                if (state.charts[cat]) state.charts[cat].destroy();
                const ctx = categoryChartRefs.current[cat].getContext('2d');
                const newChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys(categorySubtotals[cat]),
                        datasets: [{
                            data: Object.values(categorySubtotals[cat]),
                            backgroundColor: ['#1a1a1a', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db'],
                            borderWidth: 1,
                            borderColor: '#fff',
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { font: { size: 12, family: 'Inter', weight: '600' }, padding: 16 },
                            },
                            tooltip: { backgroundColor: '#1a1a1a', bodyFont: { family: 'Inter', size: 12 }, titleFont: { family: 'Inter', size: 14 } },
                        },
                    },
                });
                setState(prev => ({ ...prev, charts: { ...prev.charts, [cat]: newChart } }));
            }
        });

        return () => {
            Object.values(state.charts).forEach(chart => chart?.destroy());
        };
    }, [state.showCharts, state.expenses, state.mode, state.exchangeRates]);

    // Initialize app
    useEffect(() => {
        getExchangeRates();
        loadEssentials();
        return () => {
            Object.values(state.charts).forEach(chart => chart?.destroy());
        };
    }, []);

    // Handle recurring checkbox
    useEffect(() => {
        const recurringCheckbox = document.getElementById('recurring');
        const recurringOptions = document.getElementById('recurring-options');
        const recurringForever = document.getElementById('recurring-forever');
        const recurringEnd = document.getElementById('recurring-end');
        if (recurringCheckbox && recurringOptions) {
            const handleRecurringChange = () => {
                recurringOptions.style.display = recurringCheckbox.checked ? 'block' : 'none';
            };
            recurringCheckbox.addEventListener('change', handleRecurringChange);
            if (recurringForever && recurringEnd) {
                const handleForeverChange = () => {
                    recurringEnd.disabled = recurringForever.checked;
                };
                recurringForever.addEventListener('change', handleForeverChange);
                return () => {
                    recurringCheckbox.removeEventListener('change', handleRecurringChange);
                    recurringForever.removeEventListener('change', handleForeverChange);
                };
            }
        }
    }, [state.modal]);

    // Update summary
    const getSummary = () => {
        const modeExpenses = state.expenses.filter(exp => exp.mode === state.mode);
        const total = modeExpenses.reduce((sum, exp) => sum + convertToBase(exp.amount, exp.currency), 0);
        const startOfWeek = getStartOfWeek(new Date()).toISOString().split('T')[0];
        const endOfWeek = getEndOfWeek(new Date()).toISOString().split('T')[0];
        const weeklySpent = modeExpenses
            .filter(exp => exp.date >= startOfWeek && exp.date <= endOfWeek)
            .reduce((sum, exp) => sum + (exp.countWeekly ? convertToBase(exp.amount, exp.currency) : 0), 0);

        return {
            total,
            weeklySpent,
            budget: state.mode === 'normal' ? state.paycheck : state.travelBudget,
            weeklyBudget: state.mode === 'normal' ? state.weeklyBudgetNormal : state.weeklyBudgetTravel,
        };
    };

    // Modal component (kept from Script 1)
    function Modal({ type, onClose }) {
        if (!type) return null;
        const editingExpense = state.editingId ? state.expenses.find(exp => exp.id === state.editingId) : null;

        return (
            <div className="modal modal-enter-active" aria-modal="true" role="dialog">
                <div className="modal-content">
                    {state.error && <div className="error-message">{state.error}</div>}
                    {type === 'add-category' && (
                        <>
                            <div className="modal-header">
                                <h3 className="modal-title">New Category</h3>
                                <button onClick={onClose} className="close-modal" aria-label="Close modal">&times;</button>
                            </div>
                            <div className="form-group">
                                <label htmlFor="new-category-name">Name</label>
                                <input id="new-category-name" type="text" className="form-control" placeholder="Enter category name" />
                            </div>
                            <button onClick={saveCategory} className="btn btn-primary" style={{ marginTop: '1rem', width: '100%' }}>Save</button>
                        </>
                    )}
                    {type === 'add-subcategory' && (
                        <>
                            <div className="modal-header">
                                <h3 className="modal-title">New Subcategory</h3>
                                <button onClick={onClose} className="close-modal" aria-label="Close modal">&times;</button>
                            </div>
                            <div className="form-group">
                                <label htmlFor="new-subcategory-name">Name</label>
                                <input id="new-subcategory-name" type="text" className="form-control" placeholder="Enter subcategory name" />
                            </div>
                            <button onClick={saveSubcategory} className="btn btn-primary" style={{ marginTop: '1rem', width: '100%' }}>Save</button>
                        </>
                    )}
                    {type === 'set-budgets' && (
                        <>
                            <div className="modal-header">
                                <h3 className="modal-title">Set Budgets for {state.mode.charAt(0).toUpperCase() + state.mode.slice(1)}</h3>
                                <button onClick={onClose} className="close-modal" aria-label="Close modal">&times;</button>
                            </div>
                            {state.mode === 'normal' ? (
                                <>
                                    <div className="form-group">
                                        <label htmlFor="paycheck">Paycheck</label>
                                        <input id="paycheck" type="number" step="0.01" className="form-control" defaultValue={state.paycheck} placeholder="0.00" />
                                    </div>
                                    <div className="form-group">
                                        <label htmlFor="weekly-budget-normal">Weekly Budget</label>
                                        <input id="weekly-budget-normal" type="number" step="0.01" className="form-control" defaultValue={state.weeklyBudgetNormal} placeholder="0.00" />
                                    </div>
                                </>
                            ) : (
                                <>
                                    <div className="form-group">
                                        <label htmlFor="travel-budget">Budget</label>
                                        <input id="travel-budget" type="number" step="0.01" className="form-control" defaultValue={state.travelBudget} placeholder="0.00" />
                                    </div>
                                    <div className="form-group">
                                        <label htmlFor="weekly-budget-travel">Weekly Budget</label>
                                        <input id="weekly-budget-travel" type="number" step="0.01" className="form-control" defaultValue={state.weeklyBudgetTravel} placeholder="0.00" />
                                    </div>
                                </>
                            )}
                            <button onClick={saveBudgets} className="btn btn-primary" style={{ marginTop: '1rem', width: '100%' }}>Save</button>
                        </>
                    )}
                    {type === 'add-expense' && (
                        <>
                            <div className="modal-header">
                                <h3 className="modal-title">{state.editingId ? 'Edit Expense' : 'Add Expense'}</h3>
                                <button onClick={onClose} className="close-modal" aria-label="Close modal">&times;</button>
                            </div>
                            <div className="expense-form">
                                <div className="form-group">
                                    <label htmlFor="modal-category">Category</label>
                                    <select id="modal-category" className="form-control" defaultValue={editingExpense?.category || state.currentCategory} onChange={() => {
                                        const catSelect = document.getElementById('modal-category');
                                        const subcatSelect = document.getElementById('modal-subcategory');
                                        if (!catSelect || !subcatSelect) return;
                                        const category = state.categories[state.mode].find(c => c.name === catSelect.value);
                                        subcatSelect.innerHTML = '';
                                        if (category) {
                                            category.subcategories.forEach(sub => {
                                                const opt = document.createElement('option');
                                                opt.value = sub;
                                                opt.textContent = sub;
                                                subcatSelect.appendChild(opt);
                                            });
                                        }
                                    }}>
                                        {state.categories[state.mode].map(cat => (
                                            <option key={cat.name} value={cat.name}>{cat.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label htmlFor="modal-subcategory">Subcategory</label>
                                    <select id="modal-subcategory" className="form-control" defaultValue={editingExpense?.subcategory || state.currentSubcategory}>
                                        {state.categories[state.mode].find(c => c.name === (editingExpense?.category || state.currentCategory))?.subcategories.map(sub => (
                                            <option key={sub} value={sub}>{sub}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label htmlFor="amount">Amount</label>
                                    <input id="amount" type="number" step="0.01" className="form-control" defaultValue={editingExpense?.amount || ''} placeholder="0.00" />
                                </div>
                                <div className="form-group">
                                    <label htmlFor="currency">Currency</label>
                                    <select id="currency" className="form-control" defaultValue={editingExpense?.currency || 'CAD'}>
                                        {currencies.map(c => (
                                            <option key={c.code} value={c.code}>{c.code} ({c.symbol}) - {c.name}</option>
                                        ))}
                                    </select>
                                </div>
                                <div className="form-group">
                                    <label htmlFor="date">Date</label>
                                    <input id="date" type="date" className="form-control" defaultValue={editingExpense?.date || new Date().toISOString().split('T')[0]} />
                                </div>
                                <div className="form-group">
                                    <label htmlFor="description">Description</label>
                                    <input id="description" type="text" className="form-control" defaultValue={editingExpense?.description || ''} placeholder="Optional" />
                                </div>
                                <div className="form-group checkbox-group">
                                    <input id="count-weekly" type="checkbox" defaultChecked={editingExpense ? editingExpense.countWeekly : true} />
                                    <label htmlFor="count-weekly">Count towards weekly spent</label>
                                </div>
                                <div className="form-group checkbox-group">
                                    <input id="recurring" type="checkbox" defaultChecked={editingExpense?.recurring} />
                                    <label htmlFor="recurring">Recurring Monthly</label>
                                </div>
                                <div id="recurring-options" style={{ display: editingExpense?.recurring ? 'block' : 'none' }}>
                                    <label>Recurring Period</label>
                                    <div className="form-group">
                                        <label htmlFor="recurring-start">Start Date</label>
                                        <input id="recurring-start" type="date" className="form-control" defaultValue={editingExpense?.recurring?.start || new Date().toISOString().split('T')[0]} />
                                    </div>
                                    <div className="form-group">
                                        <label htmlFor="recurring-end">End Date (optional)</label>
                                        <input id="recurring-end" type="date" className="form-control" defaultValue={editingExpense?.recurring?.end || ''} disabled={editingExpense?.recurring?.forever} />
                                    </div>
                                    <div className="checkbox-group">
                                        <input id="recurring-forever" type="checkbox" defaultChecked={editingExpense?.recurring?.forever} />
                                        <label htmlFor="recurring-forever">Forever</label>
                                    </div>
                                </div>
                            </div>
                            <button onClick={saveExpense} className="btn btn-primary" style={{ marginTop: '1rem', width: '100%' }}>Save</button>
                        </>
                    )}
                    {type === 'recurring-list' && (
                        <>
                            <div className="modal-header">
                                <h3 className="modal-title">Recurring Expenses</h3>
                                <button onClick={onClose} className="close-modal" aria-label="Close modal">&times;</button>
                            </div>
                            {state.expenses.filter(exp => exp.recurring).length === 0 ? (
                                <div className="empty-state">
                                    <i className="fas fa-sync-alt"></i>
                                    <p>No recurring expenses yet. Add one in the expense form!</p>
                                </div>
                            ) : (
                                <div className="expense-table-container">
                                    <table className="expense-table">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Subcategory</th>
                                                <th>Amount</th>
                                                <th>Period</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {state.expenses.filter(exp => exp.recurring).map(exp => (
                                                <tr key={exp.id}>
                                                    <td data-label="Category">{exp.category}</td>
                                                    <td data-label="Subcategory">{exp.subcategory}</td>
                                                    <td data-label="Amount">
                                                        <span className="currency-badge">{exp.symbol}</span>
                                                        {exp.amount.toFixed(2)}
                                                    </td>
                                                    <td data-label="Period">From {exp.recurring.start} to {exp.recurring.end || 'forever'}</td>
                                                    <td data-label="Actions" style={{ textAlign: 'right' }}>
                                                        <button onClick={() => editExpense(exp.id)} className="action-btn edit-btn" aria-label="Edit expense">
                                                            <i className="fas fa-edit"></i>
                                                        </button>
                                                        <button onClick={() => deleteExpense(exp.id)} className="action-btn delete-btn" aria-label="Delete expense">
                                                            <i className="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </>
                    )}
                </div>
            </div>
        );
    }

    // --------- Derived values for replacement sections ----------
    const summary = getSummary();
    const baseSymbol = currencies.find(c => c.code === state.baseCurrency)?.symbol || 'C$';
    const currentSubs = state.categories[state.mode].find(c => c.name === state.currentCategory)?.subcategories || [];
    const filtered = state.expenses.filter(e =>
        e.mode === state.mode &&
        e.category === state.currentCategory &&
        (state.currentSubcategory ? e.subcategory === state.currentSubcategory : true)
    );

    // Main render
    return (
        <ErrorBoundary>
            <div className="container">
                <div className="header fade-in">

                    <div className="controls">
                        <button onClick={toggleMode} className="btn" title={state.mode === 'normal' ? 'Switch to Travel Mode' : 'Switch to Normal Mode'} aria-label="Toggle mode">
                            <i className={`fas ${state.mode === 'normal' ? 'fa-plane' : 'fa-home'}`}></i>
                            <span>{state.mode === 'normal' ? 'Travel' : 'Normal'}</span>
                        </button>
                        <button onClick={toggleCharts} className="btn" title={state.showCharts ? 'Hide Charts' : 'Show Charts'} aria-label="Toggle charts">
                            <i className="fas fa-chart-pie"></i>
                            <span>{state.showCharts ? 'Hide Charts' : 'Charts'}</span>
                        </button>
                        <button onClick={() => openModal('set-budgets')} className="btn" title="Set Budgets" aria-label="Set budgets">
                            <i className="fas fa-cog"></i>
                            <span>Budgets</span>
                        </button>
                        <button onClick={() => openModal('recurring-list')} className="btn" title="View Recurring Expenses" aria-label="View recurring expenses">
                            <i className="fas fa-sync-alt"></i>
                            <span>Recurring</span>
                        </button>
                    </div>
                </div>

                <div className="mode-indicator fade-in">
                    <i className={`fas ${state.mode === 'normal' ? 'fa-home' : 'fa-plane'}`}></i>
                    <span>{state.mode.charAt(0).toUpperCase() + state.mode.slice(1)} Mode</span>
                </div>
                <div className="mode-indicator" style={{marginTop:'0.5rem'}}>
                    <i className="fas fa-database"></i>
                    <span>Data source: {state.dataSource}</span>
                </div>

                {state.showCharts && (
                    <div className="card fade-in">
                        <div className="card-header">
                            <div className="card-title">Analytics</div>
                        </div>
                        {state.expenses.filter(exp => exp.mode === state.mode).length === 0 ? (
                            <div className="empty-state">
                                <i className="fas fa-chart-pie"></i>
                                <p>No expenses yet. Add some to see insights!</p>
                            </div>
                        ) : (
                            <div>
                                <div className="chart-container">
                                    <h3>Category Distribution</h3>
                                    <canvas ref={chartRef}></canvas>
                                </div>
                                {state.categories[state.mode].map(cat => {
                                    const subTotals = {};
                                    state.expenses
                                        .filter(exp => exp.mode === state.mode && exp.category === cat.name)
                                        .forEach(exp => {
                                            subTotals[exp.subcategory] = (subTotals[exp.subcategory] || 0) + convertToBase(exp.amount, exp.currency);
                                        });
                                    return Object.keys(subTotals).length > 0 ? (
                                        <div key={cat.name} className="category-chart">
                                            <h3>{cat.name} Breakdown</h3>
                                            <div className="chart-container">
                                                <canvas ref={el => (categoryChartRefs.current[cat.name] = el)}></canvas>
                                            </div>
                                        </div>
                                    ) : null;
                                })}
                            </div>
                        )}
                    </div>
                )}

                <div className="card fade-in">
                    <div className="card-header">
                        <div className="card-title">Summary - {state.mode.charAt(0).toUpperCase() + state.mode.slice(1)} Mode</div>
                    </div>
                    <div className="summary-grid">
                        <div className="summary-item">
                            <div className="summary-label">{state.mode === 'normal' ? 'Paycheck' : 'Budget'}</div>
                            <div className="summary-value">{baseSymbol}{summary.budget.toFixed(2)}</div>
                            <div className="progress-bar">
                                <div className="progress-bar-fill" style={{ width: `${Math.min((summary.total / (summary.budget || 1)) * 100, 100)}%` }}></div>
                            </div>
                            <div className="text-xs text-gray-500 mt-1">{((summary.total / (summary.budget || 1)) * 100).toFixed(1)}% Used</div>
                        </div>
                        <div className="summary-item">
                            <div className="summary-label">{state.mode === 'normal' ? 'Total Expenses' : 'Budget Spent'}</div>
                            <div className="summary-value">{baseSymbol}{summary.total.toFixed(2)}</div>
                        </div>
                        <div className="summary-item">
                            <div className="summary-label">Weekly Spent / Budget</div>
                            <div className="summary-value">{baseSymbol}{summary.weeklySpent.toFixed(2)} / {baseSymbol}{summary.weeklyBudget.toFixed(2)}</div>
                            <div className="progress-bar">
                                <div className="progress-bar-fill" style={{ width: `${Math.min((summary.weeklySpent / (summary.weeklyBudget || 1)) * 100, 100)}%` }}></div>
                            </div>
                            <div className="text-xs text-gray-500 mt-1">{((summary.weeklySpent / (summary.weeklyBudget || 1)) * 100).toFixed(1)}% Used</div>
                        </div>
                    </div>
                    <div className="category-totals">
                        <h4 style={{ marginBottom: '0.75rem' }}>Category Totals:</h4>
                        {state.expenses.filter(exp => exp.mode === state.mode).length === 0 ? (
                            <p>No expenses yet.</p>
                        ) : (
                            Object.entries(
                                state.expenses
                                    .filter(exp => exp.mode === state.mode)
                                    .reduce((acc, exp) => {
                                        acc[exp.category] = (acc[exp.category] || 0) + convertToBase(exp.amount, exp.currency);
                                        return acc;
                                    }, {})
                            ).map(([cat, total]) => (
                                <div key={cat} className="category-total-item">
                                    <span className="category-total-name">{cat}</span>
                                    <span className="category-total-amount">{baseSymbol}{total.toFixed(2)}</span>
                                </div>
                            ))
                        )}
                    </div>
                </div>

                {/* CATEGORIES CARD */}
                <div className="card fade-in">
                    <div className="card-header">
                        <div className="card-title">Categories</div>
                    </div>

                    <div className="category-grid">
                        {state.categories[state.mode].map(cat => (
                            <button
                                key={cat.name}
                                className={`category-item ${state.currentCategory === cat.name ? 'active' : ''}`}
                                onClick={() => setState(prev => ({ ...prev, currentCategory: cat.name, currentSubcategory: null }))}
                            >
                                <i className={`fas ${categoryIcons[cat.name] || 'fa-folder'}`}></i>
                                <span>{cat.name}</span>
                            </button>
                        ))}
                    </div>

                    <button
                        className="btn btn-primary"
                        style={{ marginTop: '.75rem', width: '100%' }}
                        onClick={() => openModal('add-category')}
                    >
                        <i className="fas fa-plus"></i>
                        <span>Add Category</span>
                    </button>
                </div>

                {/* ======= Unified Subcategories + Expenses (improved design) ======= */}
                <div className="card fade-in">
                    <div className="card-header">
                        <div className="card-title">Subcategories &amp; Expenses</div>
                        <div style={{ display:'flex', gap:'.5rem' }}>
                            <button className="btn btn-primary" onClick={() => openModal('add-subcategory')} aria-label="Add subcategory">
                                <i className="fas fa-plus"></i><span>Add Subcategory</span>
                            </button>
                            <button
                                className="btn btn-primary"
                                onClick={() =>
                                    state.currentSubcategory
                                        ? openModal('add-expense')
                                        : setState(prev => ({ ...prev, error: 'Please select a subcategory first' }))
                                }
                                aria-label="Add expense"
                            >
                                <i className="fas fa-plus"></i><span>Add Expense</span>
                            </button>
                        </div>
                    </div>

                    {/* Segmented tabs: All + each subcategory */}
                    <div className="tabs">
                        <div
                            className={`tab ${state.currentSubcategory === null ? 'active' : ''}`}
                            onClick={() => setState(prev => ({ ...prev, currentSubcategory: null }))}
                        >
                            All
                        </div>
                        {currentSubs.map(sub => (
                            <div
                                key={sub}
                                className={`tab ${state.currentSubcategory === sub ? 'active' : ''}`}
                                onClick={() => setState(prev => ({ ...prev, currentSubcategory: sub }))}
                            >
                                {sub}
                            </div>
                        ))}
                    </div>

                    {state.error && <div className="error-message">{state.error}</div>}

                    {/* If a specific subcategory is selected, show a focused table */}
                    {state.currentSubcategory ? (
                        <>
                            {filtered.length === 0 ? (
                                <div className="empty-state">
                                    <i className="fas fa-list"></i>
                                    <p>No expenses in this subcategory yet.</p>
                                </div>
                            ) : (
                                <div className="expense-table-container">
                                    <table className="expense-table">
                                        <thead>
                                            <tr>
                                                <th>Amount</th>
                                                <th>Date</th>
                                                <th>Description</th>
                                                <th>Weekly</th>
                                                <th>Recurring</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {filtered.map(exp => (
                                                <tr key={exp.id}>
                                                    <td data-label="Amount">
                                                        <span className="currency-badge">{exp.symbol}</span>
                                                        {Number(exp.amount).toFixed(2)}
                                                    </td>
                                                    <td data-label="Date">{exp.date}</td>
                                                    <td data-label="Description">{exp.description}</td>
                                                    <td data-label="Weekly">{exp.countWeekly ? <i className="fas fa-check" title="Counts towards weekly"></i> : ''}</td>
                                                    <td data-label="Recurring">{exp.recurring ? <i className="fas fa-sync-alt" title={`Recurring from ${exp.recurring.start} to ${exp.recurring.end || 'forever'}`}></i> : ''}</td>
                                                    <td data-label="Actions" style={{ textAlign: 'right' }}>
                                                        <button onClick={() => editExpense(exp.id)} className="action-btn edit-btn" aria-label="Edit expense">
                                                            <i className="fas fa-edit"></i>
                                                        </button>
                                                        <button onClick={() => deleteExpense(exp.id)} className="action-btn delete-btn" aria-label="Delete expense">
                                                            <i className="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </>
                    ) : (
                        /* All view: grouped by subcategory with per-group totals */
                        <>
                            {state.expenses.filter(exp => exp.mode === state.mode && exp.category === state.currentCategory).length === 0 ? (
                                <div className="empty-state">
                                    <i className="fas fa-list"></i>
                                    <p>No expenses in this category yet.</p>
                                </div>
                            ) : (
                                <div>
                                    {Object.entries(
                                        state.expenses
                                            .filter(exp => exp.mode === state.mode && exp.category === state.currentCategory)
                                            .reduce((acc, exp) => {
                                                if (!acc[exp.subcategory]) acc[exp.subcategory] = [];
                                                acc[exp.subcategory].push(exp);
                                                return acc;
                                            }, {})
                                    ).map(([subcat, exps]) => {
                                        const subTotal = exps.reduce((sum, exp) => sum + convertToBase(exp.amount, exp.currency), 0);
                                        return (
                                            <div key={subcat} className="subcat-group">
                                                <h4 style={{ display:'flex', justifyContent:'space-between', alignItems:'center' }}>
                                                    <span>{subcat}</span>
                                                    <span> Total: {baseSymbol}{subTotal.toFixed(2)}</span>
                                                </h4>
                                                <div className="expense-table-container">
                                                    <table className="expense-table">
                                                        <thead>
                                                            <tr>
                                                                <th>Amount</th>
                                                                <th>Date</th>
                                                                <th>Description</th>
                                                                <th>Weekly</th>
                                                                <th>Recurring</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            {exps.map(exp => (
                                                                <tr key={exp.id}>
                                                                    <td data-label="Amount">
                                                                        <span className="currency-badge">{exp.symbol}</span>
                                                                        {Number(exp.amount).toFixed(2)}
                                                                    </td>
                                                                    <td data-label="Date">{exp.date}</td>
                                                                    <td data-label="Description">{exp.description}</td>
                                                                    <td data-label="Weekly">{exp.countWeekly ? <i className="fas fa-check" title="Counts towards weekly"></i> : ''}</td>
                                                                    <td data-label="Recurring">{exp.recurring ? <i className="fas fa-sync-alt" title={`Recurring from ${exp.recurring.start} to ${exp.recurring.end || 'forever'}`}></i> : ''}</td>
                                                                    <td data-label="Actions" style={{ textAlign: 'right' }}>
                                                                        <button onClick={() => editExpense(exp.id)} className="action-btn edit-btn" aria-label="Edit expense">
                                                                            <i className="fas fa-edit"></i>
                                                                        </button>
                                                                        <button onClick={() => deleteExpense(exp.id)} className="action-btn delete-btn" aria-label="Delete expense">
                                                                            <i className="fas fa-trash"></i>
                                                                        </button>
                                                                    </td>
                                                                </tr>
                                                            ))}
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        );
                                    })}
                                </div>
                            )}
                        </>
                    )}
                </div>
                {/* ======= End unified section ======= */}

                <Modal type={state.modal} onClose={closeModal} />
            </div>
        </ErrorBoundary>
    );
}

// Render the app
ReactDOM.render(<App />, document.getElementById('root'));


</script>

</body>


<?php require 'app/views/templates/footer.php'; ?>
