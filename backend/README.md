# Restaurant POS Backend

FastAPI backend for restaurant POS with roles (waiter/receptionist/owner), table assignment, order management, kitchen printing, live updates, and billing.

## Quickstart

- Install Poetry (if not installed): https://python-poetry.org/docs/#installation
- Install deps: `poetry install` (add `-E printing` to enable ESC/POS printer support)
- Run dev server: `poetry run uvicorn app.main:app --reload --host 0.0.0.0 --port 8000`

## Default flow

- Waiter logs in, sees tables, assigns table to customer count, takes orders, prints kitchen ticket
- Receptionist sees live operations via WebSocket, can bill and vacate tables
- Owner can manage menu, tables, and users

## Notes

- By default, print jobs are written to `print_jobs/*.txt`. Enable ESC/POS via extra and env vars.
- Uses SQLite for local dev (`app.db`).