# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Security

**NEVER use the user's credentials to log into a remote server**, under any circumstances or justification.

## Project Overview

Shared domain library for the CMS monorepo (`digitanollc/cms-core`, namespace `CMSCore\`). Both `cms-admin` and `cms-api` depend on it; there is no dependency or shared code between `cms-admin` and `cms-api` directly — their only connection is this package and the shared PostgreSQL database (schema owned by `cms-admin`).
