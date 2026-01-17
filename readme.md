# Person Parse

## Overview
This repository explores the problem of parsing and normalising personal name data from loosely structured input.

It was created to address a common integration challenge encountered in real-world systems:  
**human names do not follow strict or predictable formats.**

---

## Problem Context
Many systems (HR platforms, CRM tools, legacy databases, imports) rely on personal data that:
- originates from free-text input
- uses inconsistent ordering
- mixes titles, middle names and initials
- varies across cultures and formats

Despite appearing simple, name parsing quickly becomes non-trivial at scale.

---

## Approach
This project focuses on:
- separating structured intent from raw input
- applying deterministic parsing rules
- handling common edge cases without overfitting
- keeping logic transparent and explainable

The goal is not perfect accuracy, but **predictable and debuggable behaviour**.

---

## Use Cases
- Data migration and imports
- HR and recruitment systems
- CRM integrations
- Normalisation of legacy datasets

---

## Design Principles
- Deterministic over probabilistic
- Explicit rules over hidden heuristics
- Simplicity over completeness
- Explainability over magic

---

## Status
- Experimental utility
- Proof of concept
- Used to explore parsing strategies rather than deliver a final product

---

## Why This Project Exists
This repository demonstrates:
- awareness of real-world data quality problems
- experience with edge cases in human-entered data
- preference for transparent logic over black-box solutions

It reflects practical engineering trade-offs rather than theoretical completeness.
