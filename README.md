# Stellenbosch Weather API

This project implements a REST-like API to act as backend for the Stellenbosch Weather website.

## Configuration

The API looks for `settings.conf` in this order:

1. The API directory (`./settings.conf`).
2. The parent workspace directory, two levels above the API directory.
3. The current user's home directory (`~/settings.conf`).

## Endpoints

### `/current`

Return the latest entry with all available fields.

### `/history`

Return historical data.

### `/forecast`

Return forecast data.
