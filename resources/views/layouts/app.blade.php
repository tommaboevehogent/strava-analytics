<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', config('app.name', 'Strava Analytics API'))</title>

        <style>
            :root {
                color-scheme: light dark;
                --bg: #fdfdfc;
                --card: #ffffff;
                --border: #e3e3e0;
                --text: #1b1b18;
                --muted: #706f6c;
                --accent: #fc4c02; /* Strava orange */
                --danger-bg: #fff2f2;
                --danger-text: #b3261e;
                --success-bg: #f0faf1;
                --success-text: #1e6b2e;
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0a0a0a;
                    --card: #161615;
                    --border: #3e3e3a;
                    --text: #ededec;
                    --muted: #a1a09a;
                    --danger-bg: #2a1010;
                    --danger-text: #ff8a80;
                    --success-bg: #0f2414;
                    --success-text: #7fd694;
                }
            }
            * { box-sizing: border-box; }
            body {
                margin: 0;
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1.5rem;
                background: var(--bg);
                color: var(--text);
                font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            }
            main {
                max-width: 640px;
                width: 100%;
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: 0.75rem;
                padding: 2rem;
            }
            .topnav {
                display: flex;
                justify-content: flex-end;
                gap: 1rem;
                font-size: 0.85rem;
                margin-bottom: 1rem;
            }
            .topnav a { color: var(--muted); text-decoration: none; }
            .topnav a:hover { color: var(--accent); }
            .eyebrow {
                display: inline-block;
                font-size: 0.75rem;
                font-weight: 600;
                letter-spacing: 0.05em;
                text-transform: uppercase;
                color: var(--accent);
                margin-bottom: 0.5rem;
            }
            h1 {
                font-size: 1.5rem;
                margin: 0 0 0.5rem;
            }
            p.lead {
                color: var(--muted);
                line-height: 1.6;
                margin: 0 0 1.5rem;
            }
            .status {
                display: inline-flex;
                align-items: center;
                gap: 0.4rem;
                font-size: 0.85rem;
                color: var(--muted);
                margin-bottom: 1.5rem;
            }
            .dot {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #2fb344;
                display: inline-block;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 0.85rem;
                margin-bottom: 1.5rem;
            }
            th, td {
                text-align: left;
                padding: 0.5rem 0.6rem;
                border-bottom: 1px solid var(--border);
            }
            th { color: var(--muted); font-weight: 600; }
            code {
                font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
                font-size: 0.85em;
                background: var(--bg);
                padding: 0.1em 0.35em;
                border-radius: 0.25em;
                word-break: break-all;
            }
            footer {
                font-size: 0.8rem;
                color: var(--muted);
            }
            footer a { color: var(--accent); text-decoration: none; }
            footer a:hover { text-decoration: underline; }

            /* Forms */
            form.stacked label {
                display: block;
                font-size: 0.85rem;
                color: var(--muted);
                margin-bottom: 1rem;
            }
            form.stacked input[type="email"],
            form.stacked input[type="password"],
            form.stacked input[type="text"] {
                display: block;
                width: 100%;
                margin-top: 0.35rem;
                padding: 0.55rem 0.7rem;
                border: 1px solid var(--border);
                border-radius: 0.4rem;
                background: var(--bg);
                color: var(--text);
                font-size: 0.9rem;
            }
            label.checkbox {
                display: flex;
                align-items: center;
                gap: 0.4rem;
            }
            label.checkbox input { width: auto; }
            .btn {
                display: inline-block;
                background: var(--accent);
                color: #fff;
                border: none;
                padding: 0.55rem 1.1rem;
                border-radius: 0.4rem;
                font-size: 0.9rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
            }
            .btn:hover { opacity: 0.9; }
            .btn-link {
                background: none;
                border: none;
                color: var(--muted);
                text-decoration: underline;
                cursor: pointer;
                font-size: 0.85rem;
                padding: 0;
            }
            .btn-link:hover { color: var(--danger-text); }
            .alert {
                padding: 0.75rem 1rem;
                border-radius: 0.4rem;
                font-size: 0.85rem;
                margin-bottom: 1.5rem;
                line-height: 1.5;
            }
            .alert-error { background: var(--danger-bg); color: var(--danger-text); }
            .alert-success { background: var(--success-bg); color: var(--success-text); }
            .inline-form {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1.5rem;
            }
            .inline-form input {
                flex: 1;
                padding: 0.55rem 0.7rem;
                border: 1px solid var(--border);
                border-radius: 0.4rem;
                background: var(--bg);
                color: var(--text);
                font-size: 0.9rem;
            }
        </style>
    </head>
    <body>
        <main>
            @yield('content')
        </main>
    </body>
</html>
