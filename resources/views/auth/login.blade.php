<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --border: #e2e8f0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
            background: linear-gradient(135deg, #eef4ff 0%, var(--bg) 100%);
            display: grid;
            place-items: center;
            color: var(--text);
        }

        .card {
            width: min(100%, 420px);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.08);
        }

        .brand {
            display: inline-block;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: #dbeafe;
            color: var(--primary-dark);
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 1rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 0.5rem;
            font-size: 1.7rem;
        }

        .subtitle {
            margin: 0 0 1.4rem;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.4rem;
            font-weight: 600;
            font-size: 0.9rem;
            color: #334155;
        }

        input {
            width: 100%;
            min-height: 42px;
            padding: 0.72rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            outline: none;
            font-size: 0.92rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 0;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            width: 100%;
            min-height: 38px;
            padding: 0.58rem 0.85rem;
            border-radius: 10px;
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1;
            cursor: pointer;
            margin-top: 0.2rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .checkbox-row {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .checkbox-row input {
            width: 16px;
            min-height: auto;
            padding: 0;
            margin: 0;
            border-radius: 4px;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);
        }

        .error {
            color: #dc2626;
            margin-top: 0.35rem;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="brand">Acceso seguro</div>
        <h1>Iniciar sesión</h1>
        <p class="subtitle">Ingresa tus credenciales para continuar.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    autocomplete="email">
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
            </div>

            <div class="form-group checkbox-row">
                <input id="remember" type="checkbox" name="remember">
                <label for="remember" style="margin: 0; font-weight: 500;">Recordarme</label>
            </div>

            <button class="btn" type="submit">Entrar</button>
        </form>
    </div>
</body>

</html>
