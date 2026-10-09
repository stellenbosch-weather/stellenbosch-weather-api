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

Includes `rain_today`, the sum of available `SB_TMin.Rain_1_Tot` readings in
millimetres from midnight through the current time in `Africa/Johannesburg`.
The value is a JSON number (including `0` on a measured dry day), or `null` if
there are no rain measurements for today. Missing readings are excluded from
the sum, so gaps in station data can result in an incomplete total.

### `/history`

Return historical data.

### `/forecast`

Return forecast data.
