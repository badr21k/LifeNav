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

        /* Global loading overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.05);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 2000;
        }
        .loading-overlay.active { display: flex; }
        .spinner {
            width: 42px;
            height: 42px;
            border: 4px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        /* Button level spinner state */
        .btn.loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }
        .btn.loading::after {
            content: '';
            position: absolute;
            left: 50%; top: 50%;
            width: 18px; height: 18px;
            border: 2px solid currentColor;
            border-top-color: transparent;
            border-radius: 50%;
            transform: translate(-50%, -50%);
            animation: spin .8s linear infinite;
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

        /* Improved Summary Grid for Mobile */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .summary-item {
            background: var(--card);
            border-radius: var(--radius-md);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border);
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            position: relative;
            overflow: hidden;
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: var(--primary);
        }

        .summary-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text);
        }

        .summary-value#total-debt {
            color: var(--danger);
        }

        .summary-value#investments-value,
        .summary-value#savings-progress {
            color: var(--success);
        }

        .progress-bar {
            height: 0.5rem;
            background: var(--primary-light);
            border-radius: var(--radius-sm);
            overflow: hidden;
            margin-top: 0.5rem;
        }

        .progress-fill {
            height: 100%;
            border-radius: var(--radius-sm);
            transition: width 0.5s ease;
        }

        .progress-fill.income {
            background: var(--primary);
        }

        .progress-fill.debt {
            background: var(--danger);
        }

        .progress-fill.investments,
        .progress-fill.savings {
            background: var(--success);
        }

        .progress-fill.shifts {
            background: var(--primary);
        }

        /* Mobile Responsive Improvements */
        @media (max-width: 768px) {
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            .summary-item {
                padding: 1rem;
                min-height: 110px;
            }
            
            .summary-label {
                font-size: 0.75rem;
            }
            
            .summary-value {
                font-size: 1.25rem;
            }
        }

        @media (max-width: 480px) {
            .summary-grid {
                grid-template-columns: 1fr;
            }
            
            .summary-item {
                min-height: 100px;
                padding: 1rem;
            }
            
            body {
                padding: 1rem;
            }
            
            .header {
                padding: 1rem;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .logo-icon {
                width: 2.25rem;
                height: 2.25rem;
                font-size: 1.25rem;
            }
        }

        @media (max-width: 360px) {
            .summary-label {
                font-size: 0.7rem;
            }
            
            .summary-value {
                font-size: 1.1rem;
            }
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

        @keyframes spin {
            to { transform: rotate(360deg); }
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
    </style>
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="logo">
                <div class="logo-icon">F</div>
                <div class="logo-text">Finance Hub</div>
            </div>
            <div class="controls">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                <div class="currency-selector-wrapper">
                    <select id="base-currency-selector" class="currency-selector">
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                        <option value="JPY">JPY</option>
                        <option value="CAD">CAD</option>
                    </select>
                </div>
            </div>
        </header>
        <div id="finance-source-banner" style="margin: 0 0 1rem 0; display:flex; align-items:center; gap:.5rem; color: var(--text-light);">
            <i class="fas fa-database"></i>
            <span>Data source: <strong id="finance-source-text">Local</strong></span>
        </div>

        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Income YTD</div>
                <div class="summary-value" id="income-ytd">$0.00</div>
                <div class="progress-bar">
                    <div class="progress-fill income" style="width: 0%;"></div>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Debt</div>
                <div class="summary-value" id="total-debt">$0.00</div>
                <div class="progress-bar">
                    <div class="progress-fill debt" style="width: 0%;"></div>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Investments</div>
                <div class="summary-value" id="investments-value">$0.00</div>
                <div class="progress-bar">
                    <div class="progress-fill investments" style="width: 0%;"></div>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Savings Progress</div>
                <div class="summary-value" id="savings-progress">0%</div>
                <div class="progress-bar">
                    <div class="progress-fill savings" style="width: 0%;"></div>
                </div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Month Shifts</div>
                <div class="summary-value" id="month-shifts">0 hrs</div>
                <div class="progress-bar">
                    <div class="progress-fill shifts" style="width: 0%;"></div>
                </div>
            </div>
        </div>

        <div class="tabs">
            <div class="tab active" data-tab="payroll">Payroll</div>
            <div class="tab" data-tab="shifts">Shifts</div>
            <div class="tab" data-tab="debts">Debts</div>
            <div class="tab" data-tab="investments">Investments</div>
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
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-building"></i>
                                    <p>No employers added yet</p>
                                </td>
                            </tr>
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
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <p>No pay runs recorded yet</p>
                                </td>
                            </tr>
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
                            <tr>
                                <td colspan="7" class="empty-state">
                                    <i class="fas fa-clock"></i>
                                    <p>No shifts recorded yet</p>
                                </td>
                            </tr>
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
                <div id="debts-empty-state" class="empty-state">
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
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-chart-line"></i>
                                    <p>No investment accounts added yet</p>
                                </td>
                            </tr>
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
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="fas fa-coins"></i>
                                    <p>No investments added yet</p>
                                </td>
                            </tr>
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
                            <tr>
                                <td colspan="4" class="empty-state">
                                    <i class="fas fa-piggy-bank"></i>
                                    <p>No savings goals added yet</p>
                                </td>
                            </tr>
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
            currency: 'USD',
            theme: 'light'
        };

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

        // CSRF + API helpers
        const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
        const overlayEl = document.createElement('div');
        overlayEl.id = 'global-spinner';
        overlayEl.className = 'loading-overlay';
        overlayEl.innerHTML = '<div class="spinner" aria-label="Loading"></div>';
        document.addEventListener('DOMContentLoaded', () => { document.body.appendChild(overlayEl); });
        let activeRequests = 0;
        function beginLoad(){ activeRequests++; overlayEl.classList.add('active'); }
        function endLoad(){ activeRequests = Math.max(0, activeRequests-1); if(activeRequests===0) overlayEl.classList.remove('active'); }

        async function apiGet(path){ beginLoad(); try{ const r=await fetch(path,{credentials:'same-origin'}); if(!r.ok) throw new Error('GET '+path+' '+r.status); return await r.json(); } finally { endLoad(); } }
        async function apiSend(method, path, body){ beginLoad(); try{ const r=await fetch(path,{method,credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-Token':CSRF_TOKEN},body: body?JSON.stringify(body):null}); if(!r.ok){ const t=await r.text(); throw new Error(method+' '+path+' '+r.status+' '+t);} return r.status===204?null:await r.json(); } finally { endLoad(); } }

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
                'savings': id ? 'Edit Savings Goal' : 'Add Savings Goal'
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
                'savings': getSavingsForm(id)
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
                <div class="form-group">
                    <label for="savings-saved">Currently Saved</label>
                    <input type="number" id="savings-saved" class="form-control" step="0.01" min="0" value="${goal ? goal.saved : ''}" required>
                </div>
                <div class="form-group">
                    <label for="savings-deadline">Target Date (Optional)</label>
                    <input type="date" id="savings-deadline" class="form-control" value="${goal ? goal.deadline : ''}">
                </div>
            `;
        }

        function handleFormSubmit(e) {
            e.preventDefault();
            const type = modal.getAttribute('data-type');
            const id = modal.getAttribute('data-id');
            
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
            
            const payment = {
                id: Date.now().toString(),
                debtId,
                amount,
                date,
                notes
            };
            
            debt.balance -= amount;
            if (debt.balance < 0) debt.balance = 0;
            
            if (!debt.paymentHistory) debt.paymentHistory = [];
            debt.paymentHistory.push(payment);
            
            saveData();
            updateUI();
            closePaymentModal();
        }

        function saveData() {
            // persist only prefs locally; operational data is DB-backed
            try { localStorage.setItem('financePrefs', JSON.stringify({ theme: financeData.theme, currency: financeData.currency })); } catch(e){}
        }

        async function loadData() {
            // prefs
            try { const prefs = JSON.parse(localStorage.getItem('financePrefs')||'{}'); if(prefs.theme) financeData.theme=prefs.theme; if(prefs.currency) financeData.currency=prefs.currency; } catch(e){}
            // hydrate from backend
            const init = await apiGet('/finance/api/init');
            if (init.defaultCurrency) financeData.currency = init.defaultCurrency;
            const sourceEl = document.getElementById('finance-source-text'); if(sourceEl) sourceEl.textContent = 'Database';

            // map employers
            financeData.employers = (init.employers||[]).map(e=>({ id:String(e.id), name:e.name, paySchedule:e.pay_schedule, baseRate: parseFloat(e.base_rate||0) }));
            // map pay runs
            financeData.payRuns = (init.payruns||[]).map(p=>({ id:String(p.id), employerId:String(p.employer_id), periodStart:p.period_start, periodEnd:p.period_end, grossPay:(p.gross_cents||0)/100, netPay:(p.net_cents||0)/100 }));
            // map shifts
            financeData.shifts = (init.shifts||[]).map(s=>({ id:String(s.id), date:s.date, startTime:s.start_time||'', endTime:s.end_time||'', breakMinutes: parseInt(s.break_minutes||0,10), employerId: s.employer_id?String(s.employer_id):null, role:s.role||'', rate: parseFloat(s.rate||0), tips: parseFloat(s.tips||0), location:s.location||'', notes:s.notes||'' }));
            // map debts
            financeData.debts = (init.debts||[]).map(d=>({ id:String(d.id), lender:d.lender, type:d.type, balance: parseFloat(d.balance||0), limit: parseFloat(d.limit||0), apr: parseFloat(d.apr||0), minPayment: parseFloat(d.min_payment||0), dueDate: parseInt(d.due_day||1,10), paymentHistory: [] }));
            // map investment accounts
            financeData.investmentAccounts = (init.investment_accounts||[]).map(a=>({ id:String(a.id), name:a.name, type:a.type, value: parseFloat(a.value||0) }));
            // map investments
            financeData.investments = (init.investments||[]).map(i=>({ id:String(i.id), accountId:String(i.account_id), name:i.name, symbol:i.symbol||'', quantity: parseFloat(i.quantity||0), price: parseFloat(i.price||0) }));
            // map savings goals
            financeData.savingsGoals = (init.goals||[]).map(g=>({ id:String(g.id), name:g.name, target:(g.target_cents||0)/100, saved:(g.saved_cents||0)/100, deadline:g.deadline||'' }));
        }

        function updateUI() {
            updateSummaryCards();
            updateEmployersList();
            updatePayRunsList();
            updateShiftsList();
            updateDebtsList();
            updateInvestmentAccountsList();
            updateInvestmentsList();
            updateSavingsList();
        }

        function updateSummaryCards() {
            document.getElementById('income-ytd').textContent = formatCurrency(calculateYTDIncome());
            document.getElementById('total-debt').textContent = formatCurrency(calculateTotalDebt());
            document.getElementById('investments-value').textContent = formatCurrency(calculateInvestmentsValue());
            document.getElementById('savings-progress').textContent = calculateSavingsProgress() + '%';
            document.getElementById('month-shifts').textContent = calculateMonthShifts() + ' hrs';
            
            // Update progress bars
            document.querySelector('#income-ytd + .progress-bar .progress-fill').style.width = `${Math.min(calculateYTDIncome() / 10000 * 100, 100)}%`;
            document.querySelector('#total-debt + .progress-bar .progress-fill').style.width = `${Math.min(calculateTotalDebt() / 10000 * 100, 100)}%`;
            document.querySelector('#investments-value + .progress-bar .progress-fill').style.width = `${Math.min(calculateInvestmentsValue() / 10000 * 100, 100)}%`;
            document.querySelector('#savings-progress + .progress-bar .progress-fill').style.width = `${calculateSavingsProgress()}%`;
            document.querySelector('#month-shifts + .progress-bar .progress-fill').style.width = `${Math.min(calculateMonthShifts() / 160 * 100, 100)}%`;
        }

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
            
            const [startHours, startMinutes] = shift.startTime.split(':').map(Number);
            const [endHours, endMinutes] = shift.endTime.split(':').map(Number);
            
            const startTotalMinutes = startHours * 60 + startMinutes;
            const endTotalMinutes = endHours * 60 + endMinutes;
            
            let diffMinutes = endTotalMinutes - startTotalMinutes;
            if (diffMinutes < 0) diffMinutes += 24 * 60;
            
            diffMinutes -= (shift.breakMinutes || 0);
            
            return Math.max(0, diffMinutes / 60);
        }

        function calculateShiftEarnings(shift) {
            const hours = calculateShiftHours(shift);
            const rate = shift.rate || 0;
            const tips = shift.tips || 0;
            return (hours * rate) + tips;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: financeData.currency
            }).format(amount);
        }

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
        async function saveEmployer(id) {
            const name = document.getElementById('employer-name').value;
            const paySchedule = document.getElementById('pay-schedule').value;
            const baseRate = parseFloat(document.getElementById('base-rate').value);
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            const payload = { name, pay_schedule: paySchedule, base_rate: baseRate };
            try{
                if (id) await apiSend('PUT', `/finance/api/employers/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/employers', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteEmployer(id) {
            await apiSend('DELETE', `/finance/api/employers/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function savePayRun(id) {
            const employerId = document.getElementById('payrun-employer').value;
            const periodStart = document.getElementById('payrun-period-start').value;
            const periodEnd = document.getElementById('payrun-period-end').value;
            const grossPay = parseFloat(document.getElementById('payrun-gross').value);
            const netPay = parseFloat(document.getElementById('payrun-net').value);
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            const payload = { employer_id: parseInt(employerId,10), period_start: periodStart, period_end: periodEnd, gross_cents: Math.round((grossPay||0)*100), net_cents: Math.round((netPay||0)*100) };
            try{
                if (id) await apiSend('PUT', `/finance/api/payruns/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/payruns', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deletePayRun(id) {
            await apiSend('DELETE', `/finance/api/payruns/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveShift(id) {
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
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }

            // Calculate hours based on start/end times and break
            if (startTime && endTime) {
                const [startHours, startMins] = startTime.split(':').map(Number);
                const [endHours, endMins] = endTime.split(':').map(Number);
                let startTotalMinutes = startHours * 60 + startMins;
                let endTotalMinutes = endHours * 60 + endMins;
                if (endTotalMinutes < startTotalMinutes) endTotalMinutes += 24 * 60;
            }

            const payload = { date, start_time: startTime, end_time: endTime, break_minutes: breakMinutes, employer_id: employerId?parseInt(employerId,10):null, role, rate, tips, location, notes };
            try{
                if (id) await apiSend('PUT', `/finance/api/shifts/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/shifts', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteShift(id) {
            await apiSend('DELETE', `/finance/api/shifts/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveDebt(id) {
            const lender = document.getElementById('debt-lender').value;
            let type = document.getElementById('debt-type').value;
            const balance = parseFloat(document.getElementById('debt-balance').value);
            const limit = parseFloat(document.getElementById('debt-limit').value);
            const apr = parseFloat(document.getElementById('debt-apr').value);
            const minPayment = parseFloat(document.getElementById('debt-min-payment').value);
            const dueDate = parseInt(document.getElementById('debt-due-date').value);
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            if (type === 'other') { const customType = document.getElementById('debt-custom-type').value.trim(); type = customType || 'other'; }
            const payload = { lender, type, balance, limit_amount: limit, apr, min_payment: minPayment, due_day: dueDate };
            try{
                if (id) await apiSend('PUT', `/finance/api/debts/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/debts', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteDebt(id) {
            await apiSend('DELETE', `/finance/api/debts/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveInvestmentAccount(id) {
            const name = document.getElementById('account-name').value;
            let type = document.getElementById('account-type').value;
            const value = parseFloat(document.getElementById('account-value').value);
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            if (type === 'other') { const customType = document.getElementById('account-custom-type').value.trim(); type = customType || 'other'; }
            const payload = { name, type, value };
            try{
                if (id) await apiSend('PUT', `/finance/api/investment_accounts/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/investment_accounts', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteInvestmentAccount(id) {
            await apiSend('DELETE', `/finance/api/investment_accounts/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveInvestment(id) {
            const accountId = document.getElementById('investment-account').value;
            const name = document.getElementById('investment-name').value;
            const symbol = document.getElementById('investment-symbol').value;
            const quantity = parseFloat(document.getElementById('investment-quantity').value);
            const price = parseFloat(document.getElementById('investment-price').value);
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            const payload = { account_id: parseInt(accountId,10), name, symbol, quantity, price };
            try{
                if (id) await apiSend('PUT', `/finance/api/investments/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/investments', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteInvestment(id) {
            await apiSend('DELETE', `/finance/api/investments/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        async function saveSavingsGoal(id) {
            const name = document.getElementById('savings-goal').value;
            const target = parseFloat(document.getElementById('savings-target').value);
            const saved = parseFloat(document.getElementById('savings-saved').value);
            const deadline = document.getElementById('savings-deadline').value;
            const btn = document.querySelector('#modal .btn-primary'); if(btn){ btn.classList.add('loading'); btn.disabled = true; }
            const payload = { name, target_cents: Math.round((target||0)*100), saved_cents: Math.round((saved||0)*100), deadline: deadline||null, currency: financeData.currency };
            try{
                if (id) await apiSend('PUT', `/finance/api/savings_goals/${encodeURIComponent(id)}`, payload);
                else await apiSend('POST', '/finance/api/savings_goals', payload);
                await loadData(); updateUI();
            } finally { if(btn){ btn.classList.remove('loading'); btn.disabled = false; } }
        }

        async function deleteSavingsGoal(id) {
            await apiSend('DELETE', `/finance/api/savings_goals/${encodeURIComponent(id)}`);
            await loadData(); updateUI();
        }

        // Initialize the app when the DOM is loaded
        document.addEventListener('DOMContentLoaded', initApp);
    </script>
</body>

<?php require 'app/views/templates/footer.php'; ?>
