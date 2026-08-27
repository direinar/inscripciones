<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @stack('styles')
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e2e8f0;
            --danger: #dc2626;
            --success: #16a34a;
            --warning: #f59e0b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #eef4ff 0%, var(--bg) 100%);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        .shell {
            display: flex;
            min-height: 100vh;
            width: 100%;
        }

        .sidebar-backdrop {
            display: none;
        }

        .sidebar {
            flex: 0 0 260px;
            width: 260px;
            background: linear-gradient(180deg, #0f172a 0%, #111827 100%);
            color: white;
            padding: 1.4rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            transition: width 0.24s ease, padding 0.24s ease;
            overflow: hidden;
            z-index: 30;
        }

        .shell.sidebar-collapsed .sidebar {
            width: 0;
            padding-left: 0;
            padding-right: 0;
            border-right: 0;
        }

        .shell.sidebar-collapsed .sidebar * {
            opacity: 0;
            pointer-events: none;
        }

        .sidebar .brand {
            color: white;
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }

        .brand-logo {
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.08);
            padding: 0.2rem;
            object-fit: contain;
            display: block;
        }

        .brand-logo--sidebar {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.12);
        }

        .brand-logo--topbar {
            border: 1px solid var(--border);
            background: #fff;
        }

        .sidebar a {
            color: #dbeafe;
            text-decoration: none;
            padding: 0.72rem 0.8rem;
            border-radius: 12px;
            display: block;
            transition: background 0.2s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.12);
        }

        .sidebar-link__icon {
            display: inline-block;
            min-width: 1.35rem;
            margin-right: 0.35rem;
            font-size: 1rem;
            line-height: 1;
            text-align: center;
        }

        .sidebar-section {
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            padding-top: 0.7rem;
            margin-top: 0.1rem;
        }

        .sidebar-section__title {
            font-size: 0.74rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #93c5fd;
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        .sidebar-section a {
            font-size: 0.92rem;
            padding: 0.58rem 0.75rem;
            margin-bottom: 0.25rem;
        }

        .sidebar-section a span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 1.25rem;
            height: 1.25rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            margin-right: 0.45rem;
            font-size: 0.75rem;
        }

        .main {
            flex: 1;
            min-width: 0;
            padding: 1.5rem 1.25rem 2.5rem;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(15, 23, 42, 0.06);
            padding: 1.5rem;
            min-width: 0;
            max-width: 100%;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            gap: 1rem;
        }

        .topbar-left {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
        }

        .icon-btn {
            min-width: 36px;
            min-height: 36px;
            padding: 0.45rem;
            border-radius: 9px;
            border: 1px solid var(--border);
            background: #fff;
            color: var(--primary-dark);
            font-size: 1rem;
            line-height: 1;
            cursor: pointer;
        }

        .icon-btn:hover {
            background: #f8fafc;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-weight: 700;
            color: var(--primary-dark);
        }

        .brand-text {
            min-width: 0;
        }

        .badge {
            display: inline-block;
            padding: 0.3rem 0.65rem;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--primary-dark);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        h1,
        h2,
        h3 {
            margin-top: 0;
        }

        .muted {
            color: var(--muted);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 10px;
            padding: 0.68rem 0.95rem;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1;
            cursor: pointer;
            color: white;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-sm {
            min-height: 36px;
            padding: 0.58rem 0.85rem;
            border-radius: 9px;
            font-size: 0.84rem;
        }

        .btn-sm-nav {
            min-width: 122px;
        }

        .btn-sm-action {
            min-width: 84px;
        }

        .btn-block {
            width: 100%;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.18);
        }

        .btn-secondary {
            background: #6b7280;
        }

        .btn-danger {
            background: var(--danger);
        }

        .btn-ghost {
            background: transparent;
            color: var(--primary-dark);
            border: 1px solid var(--border);
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        .grid-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .actions-row {
            display: flex;
            gap: 0.6rem;
            flex-wrap: wrap;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .field {
            margin-bottom: 1rem;
        }

        .field-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
        }

        input,
        select {
            width: 100%;
            min-height: 42px;
            padding: 0.72rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 0.92rem;
            outline: none;
            background: white;
            color: var(--text);
        }

        input:focus,
        select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0.6rem;
        }

        .table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
        }

        .table-wrap table {
            min-width: 920px;
        }

        th,
        td {
            padding: 0.9rem 0.8rem;
            border-bottom: 1px solid var(--border);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .table-modern tbody tr:hover {
            background: #f8fbff;
        }

        .pagination-wrap {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .pagination li a,
        .pagination li span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2.25rem;
            min-height: 2.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            border: 1px solid var(--border);
            background: white;
            color: var(--primary-dark);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .pagination li.active span {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }

        .pagination li.disabled span {
            color: var(--muted);
            background: #f8fafc;
        }

        .person-block,
        .program-block,
        .movement-stack {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .person-name {
            font-weight: 700;
            color: var(--text);
        }

        .person-meta {
            font-size: 0.84rem;
            color: var(--muted);
        }

        .movement-item {
            display: grid;
            gap: 0.16rem;
            padding: 0.45rem 0.55rem;
            border-radius: 10px;
            background: #f8fafc;
        }

        .movement-item span {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
        }

        .movement-item small {
            color: var(--muted);
            font-size: 0.8rem;
        }

        .action-group {
            display: flex;
            flex-wrap: wrap;
            gap: 0.45rem;
        }

        .action-pill {
            border: 1px solid transparent;
            border-radius: 999px;
            padding: 0.47rem 0.8rem;
            font-size: 0.8rem;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            background: #eef4ff;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .action-pill:hover {
            transform: translateY(-1px);
        }

        .action-pill--inscription {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .action-pill--tuition {
            background: #f0fdf4;
            color: #15803d;
        }

        .action-pill--refund {
            background: #fef2f2;
            color: #b91c1c;
        }

        .payment-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 70;
            padding: 1rem;
        }

        .payment-modal.is-open {
            display: flex;
        }

        .payment-modal__backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
        }

        .payment-modal__card {
            position: relative;
            width: min(100%, 480px);
            background: white;
            border-radius: 22px;
            padding: 1.3rem;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.18);
            z-index: 1;
        }

        .payment-modal__close {
            position: absolute;
            top: 0.8rem;
            right: 0.8rem;
            border: none;
            background: transparent;
            font-size: 1.4rem;
            color: var(--muted);
            cursor: pointer;
        }

        .payment-modal__eyebrow {
            display: inline-block;
            padding: 0.32rem 0.6rem;
            border-radius: 999px;
            background: #eff6ff;
            color: var(--primary-dark);
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 0.55rem;
        }

        .payment-modal__error {
            min-height: 1.1rem;
            margin-top: 0.2rem;
            color: var(--danger);
            font-size: 0.88rem;
            font-weight: 600;
        }

        .actions-row--end {
            justify-content: flex-end;
            margin-top: 0.8rem;
        }

        body.modal-open {
            overflow: hidden;
        }

        .flash {
            padding: 0.8rem 0.95rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .flash.success {
            background: #dcfce7;
            color: var(--success);
        }

        .flash.error {
            background: #fee2e2;
            color: var(--danger);
        }

        .error {
            color: var(--danger);
            margin-top: 0.35rem;
            font-size: 0.88rem;
            font-weight: 500;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.32rem 0.65rem;
            border-radius: 999px;
            background: #eff6ff;
            color: var(--primary-dark);
            font-size: 0.85rem;
            font-weight: 600;
        }

        .col-name {
            width: 28%;
        }

        .col-email {
            width: 34%;
        }

        .col-role {
            width: 16%;
        }

        .col-actions {
            width: 22%;
        }

        .hide-sm {
            display: table-cell;
        }

        .hide-md {
            display: table-cell;
        }

        .hide-lg {
            display: table-cell;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .btn-sm-nav {
                min-width: 108px;
            }
        }

        @media (max-width: 900px) {
            .shell {
                display: block;
                position: relative;
            }

            .main {
                width: 100%;
                padding: 1rem 0.75rem 1.5rem;
            }

            .card {
                padding: 1rem;
                border-radius: 16px;
            }

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 0.85rem;
            }

            .topbar-left {
                width: 100%;
                justify-content: space-between;
            }

            .grid-2 {
                grid-template-columns: 1fr;
            }

            .field-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .actions-row {
                width: 100%;
            }

            .actions-row .btn,
            .actions-row form,
            .actions-row form .btn {
                width: 100%;
            }

            .btn-sm-nav,
            .btn-sm-action {
                min-width: 0;
            }

            .action-group {
                flex-direction: column;
                align-items: stretch;
            }

            .action-pill {
                width: 100%;
                text-align: center;
            }

            .hide-sm {
                display: none;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                flex: 0 0 min(84vw, 300px);
                width: min(84vw, 300px);
                transform: translateX(0);
                transition: transform 0.24s ease;
            }

            .shell.sidebar-collapsed .sidebar {
                width: min(84vw, 300px);
                padding: 1.4rem 1rem;
                transform: translateX(-100%);
            }

            .shell.sidebar-collapsed .sidebar * {
                opacity: 1;
                pointer-events: auto;
            }

            .sidebar-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.35);
                z-index: 20;
                opacity: 1;
                transition: opacity 0.24s ease;
            }

            .shell.sidebar-collapsed .sidebar-backdrop {
                pointer-events: none;
                opacity: 0;
            }
        }

        @media (max-width: 540px) {
            .badge {
                font-size: 0.72rem;
            }

            .hide-md {
                display: none;
            }
        }

        @media (max-width: 420px) {
            .hide-lg {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <button id="sidebarBackdrop" class="sidebar-backdrop" type="button" aria-label="Cerrar menú"></button>
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-logo brand-logo--sidebar" src="{{ asset('images/logotipo.jpeg') }}"
                    alt="Logotipo del sistema" loading="eager" decoding="async">
                <span>Sistema de gestión</span>
            </div>

            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span
                    class="sidebar-link__icon" aria-hidden="true">📊</span>Dashboard</a>
            @if (Auth::check())
                @include('layouts.partials.sidebar-link', [
                    'active' => request()->routeIs('prospects.index'),
                    'icon' => '👥',
                    'label' => 'Prospectos',
                    'route' => 'prospects.index',
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request()->routeIs('marketing.index'),
                    'icon' => '☎',
                    'label' => 'Mercadeo',
                    'route' => 'marketing.index',
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request('module') === 'pagos',
                    'icon' => '💵',
                    'label' => 'Pagos',
                    'route' => 'enrollments.financial',
                    'routeParams' => ['module' => 'pagos'],
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request('module') === 'creditos',
                    'icon' => '🏢',
                    'label' => 'Créditos académicos',
                    'route' => 'enrollments.financial',
                    'routeParams' => ['module' => 'creditos'],
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request('module') === 'retirados',
                    'icon' => '⛔',
                    'label' => 'Retirados',
                    'route' => 'enrollments.index',
                    'routeParams' => ['module' => 'retirados', 'status' => 'retirado'],
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request('module') === 'devoluciones',
                    'icon' => '↩',
                    'label' => 'Devoluciones',
                    'route' => 'enrollments.financial',
                    'routeParams' => ['module' => 'devoluciones'],
                ])
                @include('layouts.partials.sidebar-link', [
                    'active' => request('module') === 'reportes',
                    'icon' => '📈',
                    'label' => 'Reportes',
                    'route' => 'enrollments.index',
                    'routeParams' => ['module' => 'reportes'],
                ])
            @endif
            <form method="POST" action="{{ route('logout') }}" style="margin-top:auto;">
                @csrf
                <button class="btn btn-sm btn-secondary btn-block" type="submit">Cerrar sesión</button>
            </form>
        </aside>

        <main class="main">
            <div class="topbar">
                <div class="topbar-left">
                    <button id="sidebarToggle" class="icon-btn" type="button" aria-label="Mostrar u ocultar menú"
                        aria-expanded="true">☰</button>
                    <div class="brand" style="color: var(--primary-dark);">
                        <img class="brand-logo brand-logo--topbar" src="{{ asset('images/logotipo.jpeg') }}"
                            alt="Logotipo del sistema" loading="eager" decoding="async">
                        <span class="brand-text">@yield('badge', 'Panel')</span>
                    </div>
                </div>
                <div class="badge">{{ Auth::check() ? Auth::user()->role : 'Invitado' }}</div>
            </div>

            <div class="card">
                @yield('content')
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function() {
            var shell = document.querySelector('.shell');
            var toggle = document.getElementById('sidebarToggle');
            var backdrop = document.getElementById('sidebarBackdrop');
            if (!shell || !toggle) return;

            var storageKey = 'inscripcionesu.sidebarCollapsed';
            var collapsed = localStorage.getItem(storageKey) === '1';
            var mobileQuery = window.matchMedia('(max-width: 900px)');

            if (localStorage.getItem(storageKey) === null && mobileQuery.matches) {
                collapsed = true;
            }

            function applyState(isCollapsed) {
                shell.classList.toggle('sidebar-collapsed', isCollapsed);
                toggle.setAttribute('aria-expanded', String(!isCollapsed));
                toggle.setAttribute('title', isCollapsed ? 'Mostrar menú' : 'Ocultar menú');
            }

            applyState(collapsed);

            toggle.addEventListener('click', function() {
                collapsed = !collapsed;
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
                applyState(collapsed);
            });

            if (backdrop) {
                backdrop.addEventListener('click', function() {
                    collapsed = true;
                    localStorage.setItem(storageKey, '1');
                    applyState(collapsed);
                });
            }

            mobileQuery.addEventListener('change', function(event) {
                if (event.matches) {
                    collapsed = true;
                    applyState(true);
                }
            });
        })();

        (function() {
            if (typeof Swal === 'undefined') return;

            @if (session('success'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: @json(session('success')),
                    showConfirmButton: false,
                    timer: 2600,
                    timerProgressBar: true
                });
            @endif

            @if (session('error'))
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: @json(session('error')),
                    confirmButtonText: 'Cerrar'
                });
            @endif
        })();
    </script>
    @stack('scripts')
</body>

</html>
