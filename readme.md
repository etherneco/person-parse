# Person Parse

## Overview
Person Parse is a compact PHP/Laravel utility that normalizes human names from free‑text input into structured fields. It tackles real‑world data messiness (titles, initials, compound surnames, suffixes) with deterministic, explainable rules.

## What a recruiter should notice
- Practical data engineering: handles inconsistent, user‑entered names without relying on opaque ML.
- Clear parsing pipeline: predictable steps for splitting, normalizing, and classifying tokens.
- Edge‑case focus: compound surnames, initials, suffixes, and nickname markers.
- Maintainable code: explicit rules, simple helpers, and testable entry points.

## Tech stack
- PHP 7.1+
- Laravel 5.8 (project scaffold)

## Key entry points
- Parser: `app/Parse/Person.php`
- Splitter for "X and Y" cases: `app/Parse/Splite.php`
- Dictionary rules: `app/Parse/Dictionary.php`
- Helpers: `app/Parse/Helper.php`

## Example usage
```php
use App\Parse\Person;

$parsed = Person::parse('Dr. Anna K. van Helsing, PhD');
```

## Notes on design
- Deterministic over probabilistic.
- Transparent rules over hidden heuristics.
- Focused scope: useful, explainable behavior rather than universal coverage.

## Suggested improvements (future work)
- Add unit tests for more locale‑specific formats.
- Expand dictionary coverage for additional cultures.
- Provide a small CLI or API wrapper for batch normalization.
