<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Strava Analytics API') }}</title>

        <style>
            :root {
                color-scheme: light dark;
                --bg: #fdfdfc;
                --card: #ffffff;
                --border: #e3e3e0;
                --text: #1b1b18;
                --muted: #706f6c;
                --accent: #fc4c02; /* Strava orange */
            }
            @media (prefers-color-scheme: dark) {
                :root {
                    --bg: #0a0a0a;
                    --card: #161615;
                    --border: #3e3e3a;
                    --text: #ededec;
                    --muted: #a1a09a;
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
            }
            footer {
                font-size: 0.8rem;
                color: var(--muted);
            }
            footer a { color: var(--accent); text-decoration: none; }
            footer a:hover { text-decoration: underline; }
        </style>
    </head>
    <body>
        <main>
            <span class="eyebrow">Personal project</span>
            <h1>Strava Analytics API</h1>
            <p class="lead">
                Een Laravel REST API die Strava-trainingsdata ontsluit via OAuth,
                real-time webhooks en een token-geauthenticeerde API met
                wekelijkse/trend-statistieken.
            </p>

            <div class="status">
                <span class="dot"></span> API online — geen publieke UI, alleen endpoints
            </div>

            <table>
                <thead>
                    <tr><th>Methode</th><th>Route</th></tr>
                </thead>
                <tbody>
                    <tr><td>GET</td><td><code>/api/activities</code></td></tr>
                    <tr><td>GET</td><td><code>/api/activities/{id}</code></td></tr>
                    <tr><td>GET</td><td><code>/api/stats/weekly</code></td></tr>
                    <tr><td>GET</td><td><code>/api/stats/trends</code></td></tr>
                </tbody>
            </table>

            <footer>
                Alle <code>/api/*</code>-routes vereisen een Sanctum-token.
                Broncode en volledige documentatie:
                <a href="https://github.com/tommaboevehogent/strava-analytics" target="_blank" rel="noopener">GitHub</a>.
            </footer>
        </main>
    </body>
</html>