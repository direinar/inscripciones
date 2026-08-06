<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de inscripción</title>
    <style>
        :root {
            --bg: #f5f7fb;
            --card: #ffffff;
            --text: #0f172a;
            --muted: #64748b;
            --primary: #7f1d1d;
            --primary-dark: #991b1b;
            --accent: #f8fafc;
            --border: #dbe4f0;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Inter, "Segoe UI", Roboto, Arial, sans-serif;
            background:
                radial-gradient(circle at top right, rgba(127, 29, 29, 0.08), transparent 25%),
                linear-gradient(180deg, #fbfdff 0%, var(--bg) 100%);
            color: var(--text);
        }

        .shell {
            max-width: 1240px;
            margin: 0 auto;
            padding: 2rem 1.25rem 3rem;
        }

        .hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .brand-panel {
            display: flex;
            align-items: center;
            gap: 0.95rem;
            max-width: 760px;
        }

        .brand-mark {
            flex: 0 0 auto;
            width: clamp(72px, 10vw, 118px);
            aspect-ratio: 1 / 1;
            padding: 0.65rem;
            border-radius: 22px;
            background: white;
            border: 1px solid rgba(219, 228, 240, 0.95);
            box-shadow: 0 14px 34px rgba(15, 23, 42, 0.08);
            display: grid;
            place-items: center;
        }

        .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        .brand-copy {
            min-width: 0;
        }

        .eyebrow {
            display: inline-block;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: rgba(127, 29, 29, 0.08);
            color: var(--primary-dark);
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.9rem;
        }

        h1 {
            margin: 0 0 0.45rem;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.05;
        }

        .subtitle {
            margin: 0;
            max-width: 720px;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.65;
        }

        .hero-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
            align-self: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 0.68rem 1rem;
            border-radius: 10px;
            text-decoration: none;
            font-size: 0.86rem;
            font-weight: 700;
            line-height: 1;
            border: 1px solid transparent;
            cursor: pointer;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .btn-ghost {
            color: var(--primary-dark);
            background: white;
            border-color: var(--border);
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 1.5rem;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06);
        }

        .section+.section {
            margin-top: 1.4rem;
            padding-top: 1.4rem;
            border-top: 1px solid var(--border);
        }

        .section-title {
            margin: 0 0 1rem;
            font-size: 1.25rem;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 0.45rem;
        }

        label {
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
        }

        input,
        select {
            width: 100%;
            min-height: 42px;
            padding: 0.72rem 0.9rem;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: white;
            color: var(--text);
            font-size: 0.92rem;
            outline: none;
        }

        input:focus,
        select:focus {
            border-color: #c2410c;
            box-shadow: 0 0 0 4px rgba(194, 65, 12, 0.12);
        }

        .helper,
        .error {
            font-size: 0.84rem;
        }

        .helper {
            color: var(--muted);
        }

        .error {
            color: var(--danger-text);
        }

        .flash {
            padding: 0.9rem 1rem;
            border-radius: 12px;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .flash-success {
            background: var(--success-bg);
            color: var(--success-text);
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-top: 1.5rem;
            flex-wrap: wrap;
        }

        .meta {
            color: var(--muted);
            font-size: 0.9rem;
        }

        @media (max-width: 960px) {
            .grid-3 {
                grid-template-columns: 1fr 1fr;
            }

            .hero {
                flex-direction: column;
                align-items: flex-start;
            }

            .brand-panel {
                max-width: none;
            }

            .hero-actions {
                align-self: flex-start;
            }
        }

        @media (max-width: 640px) {
            .shell {
                padding: 1.25rem 0.9rem 2rem;
            }

            .card {
                padding: 1rem;
                border-radius: 16px;
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .actions .btn {
                width: 100%;
            }

            .hero {
                margin-bottom: 1rem;
            }

            .brand-panel {
                align-items: flex-start;
            }

            .brand-mark {
                width: 84px;
                padding: 0.55rem;
                border-radius: 18px;
            }

            .subtitle {
                font-size: 0.95rem;
                line-height: 1.55;
            }
        }
    </style>
</head>

<body>
    <div class="shell">
        <div class="hero">
            <div class="brand-panel">
                <div class="brand-mark" aria-hidden="true">
                    <img src="{{ asset('images/logotipo.jpeg') }}" alt="" loading="eager" decoding="async">
                </div>
                <div class="brand-copy">
                    <span class="eyebrow">Inscripción pública</span>
                    <h1>Registra tu solicitud de inscripción</h1>
                    <p class="subtitle">Completa el formulario con la información del programa y tus datos personales.
                        El equipo administrativo y de mercadeo podrá hacer seguimiento interno a tu registro.</p>
                </div>
            </div>
            <div class="hero-actions">
                <a class="btn btn-ghost" href="{{ route('login') }}">Acceso interno</a>
            </div>
        </div>

        <div class="card">
            @if (session('success'))
                <div class="flash flash-success">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('enrollments.store') }}">
                @csrf

                <div class="section">
                    <h2 class="section-title">Información programas</h2>
                    <div class="grid-3">
                        <div class="field">
                            <label for="period">Periodo *</label>
                            <select id="period" name="period" required>
                                <option value="">Seleccione</option>
                                @foreach ($periods as $period)
                                    <option value="{{ $period }}"
                                        {{ old('period') === $period ? 'selected' : '' }}>{{ $period }}</option>
                                @endforeach
                            </select>
                            @error('period')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="campus">Sede *</label>
                            <select id="campus" name="campus" required>
                                <option value="">Seleccione</option>
                                @foreach ($campuses as $campus)
                                    <option value="{{ $campus }}"
                                        {{ old('campus') === $campus ? 'selected' : '' }}>
                                        {{ $campus }}</option>
                                @endforeach
                            </select>
                            @error('campus')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="jornada">Jornada *</label>
                            <select id="jornada" name="jornada" required>
                                <option value="">Seleccione</option>
                                @foreach ($jornadas as $jornada)
                                    <option value="{{ $jornada }}"
                                        {{ old('jornada') === $jornada ? 'selected' : '' }}>
                                        {{ $jornada }}</option>
                                @endforeach
                            </select>
                            @error('jornada')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="program">Programa *</label>
                            <select id="program" name="program" required>
                                <option value="">Seleccione</option>
                                @foreach ($programs as $program)
                                    <option value="{{ $program }}"
                                        {{ old('program') === $program ? 'selected' : '' }}>{{ $program }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title">Información personal</h2>
                    <div class="grid-3">
                        <div class="field">
                            <label for="first_name">Primer nombre *</label>
                            <input id="first_name" name="first_name" type="text" value="{{ old('first_name') }}"
                                required>
                            @error('first_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="middle_name">Segundo nombre</label>
                            <input id="middle_name" name="middle_name" type="text" value="{{ old('middle_name') }}">
                            @error('middle_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="last_name">Primer apellido *</label>
                            <input id="last_name" name="last_name" type="text" value="{{ old('last_name') }}"
                                required>
                            @error('last_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="second_last_name">Segundo apellido</label>
                            <input id="second_last_name" name="second_last_name" type="text"
                                value="{{ old('second_last_name') }}">
                            @error('second_last_name')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="document_type">Tipo de identificación *</label>
                            <select id="document_type" name="document_type" required>
                                <option value="">Seleccione</option>
                                @foreach ($documentTypes as $documentType)
                                    <option value="{{ $documentType }}"
                                        {{ old('document_type') === $documentType ? 'selected' : '' }}>
                                        {{ $documentType }}</option>
                                @endforeach
                            </select>
                            @error('document_type')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="document_number">Número de identificación *</label>
                            <input id="document_number" name="document_number" type="text"
                                value="{{ old('document_number') }}" required>
                            @error('document_number')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="sex">Sexo *</label>
                            <select id="sex" name="sex" required>
                                <option value="">Seleccione</option>
                                @foreach ($sexOptions as $sexOption)
                                    <option value="{{ $sexOption }}"
                                        {{ old('sex') === $sexOption ? 'selected' : '' }}>{{ $sexOption }}</option>
                                @endforeach
                            </select>
                            @error('sex')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="email">Correo electrónico *</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}"
                                required>
                            @error('email')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="mobile">Celular *</label>
                            <input id="mobile" name="mobile" type="text" value="{{ old('mobile') }}"
                                required>
                            @error('mobile')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="birth_date">Fecha de nacimiento *</label>
                            <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date') }}"
                                required>
                            @error('birth_date')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="address">Dirección *</label>
                            <input id="address" name="address" type="text" value="{{ old('address') }}"
                                required>
                            @error('address')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="residence_department_id">Departamento de residencia *</label>
                            <select id="residence_department_id" name="residence_department_id" required>
                                <option value="">Seleccione departamento</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}"
                                        {{ (string) old('residence_department_id') === (string) $department->id ? 'selected' : '' }}>
                                        {{ $department->name }}</option>
                                @endforeach
                            </select>
                            @error('residence_department_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="field">
                            <label for="residence_municipality_id">Municipio de residencia *</label>
                            <select id="residence_municipality_id" name="residence_municipality_id" required
                                data-old-value="{{ old('residence_municipality_id') }}" disabled>
                                <option value="">Seleccione municipio</option>
                            </select>
                            @error('residence_municipality_id')
                                <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="actions">
                    <div class="meta">Los campos marcados con * son obligatorios.</div>
                    <button class="btn btn-primary" type="submit">Enviar inscripción</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            var departmentSelect = document.getElementById('residence_department_id');
            var municipalitySelect = document.getElementById('residence_municipality_id');
            if (!departmentSelect || !municipalitySelect) return;

            var endpoint = @json(route('enrollments.municipalities.by-department'));
            endpoint = endpoint.startsWith('http') ? endpoint.replace(/^https?:\/\/[^\/]+/, '') : endpoint;
            var selectedMunicipality = municipalitySelect.getAttribute('data-old-value') || '';

            function resetMunicipalities(placeholder) {
                municipalitySelect.innerHTML = '';
                var option = document.createElement('option');
                option.value = '';
                option.textContent = placeholder;
                municipalitySelect.appendChild(option);
                municipalitySelect.disabled = true;
            }

            async function loadMunicipalities(departmentId) {
                resetMunicipalities('Cargando municipios...');

                try {
                    var response = await fetch(endpoint + '?department_id=' + encodeURIComponent(departmentId), {
                        headers: {
                            'Accept': 'application/json'
                        }
                    });

                    if (!response.ok) {
                        throw new Error('No se pudieron cargar los municipios.');
                    }

                    var data = await response.json();
                    var municipalities = Array.isArray(data.municipalities) ? data.municipalities : [];

                    resetMunicipalities('Seleccione municipio');
                    municipalitySelect.disabled = municipalities.length === 0;

                    municipalities.forEach(function(item) {
                        var option = document.createElement('option');
                        option.value = String(item.id);
                        option.textContent = item.name;
                        if (selectedMunicipality && String(item.id) === String(selectedMunicipality)) {
                            option.selected = true;
                        }
                        municipalitySelect.appendChild(option);
                    });
                } catch (error) {
                    resetMunicipalities('No se pudieron cargar municipios');
                }
            }

            departmentSelect.addEventListener('change', function() {
                selectedMunicipality = '';
                if (!departmentSelect.value) {
                    resetMunicipalities('Seleccione municipio');
                    return;
                }

                loadMunicipalities(departmentSelect.value);
            });

            if (departmentSelect.value) {
                loadMunicipalities(departmentSelect.value);
            }
        })();
    </script>
</body>

</html>
